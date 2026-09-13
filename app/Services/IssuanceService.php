<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ConsumableIssuance;
use App\Models\ConsumableStock;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for every consumable-issuance mutation.
 * Used by: walk-in issue, request fulfillment, bulk kit issuance,
 * borrower request, receipt confirmation, cancellations.
 */
class IssuanceService
{
    /* ────────────────────────────────────────────────
       Core mutations (all row-locked, audited, notified)
    ──────────────────────────────────────────────── */

    /**
     * Walk-in / direct issue: creates an `issued` record and deducts stock.
     *
     * @throws \DomainException when stock is insufficient (message = user-safe reason)
     */
    public static function issueDirect(
        int $stockId,
        int $recipientId,
        int $quantity,
        string $purpose,
        int $actorId,
        ?string $notes = null,
        ?string $groupId = null
    ): ConsumableIssuance {
        [$stock, $issuance] = DB::transaction(function () use ($stockId, $recipientId, $quantity, $purpose, $actorId, $notes, $groupId) {
            $stock = ConsumableStock::where('id', $stockId)->lockForUpdate()->firstOrFail();

            if ($stock->stock_quantity < $quantity) {
                throw new \DomainException(
                    "Insufficient stock for \"{$stock->name}\" — {$stock->stock_quantity} {$stock->unit}(s) left."
                );
            }

            $stock->decrement('stock_quantity', $quantity);

            $issuance = ConsumableIssuance::create([
                'user_id'       => $recipientId,
                'item_id'       => $stock->id,
                'quantity'      => $quantity,
                'purpose'       => $purpose,
                'status'        => 'issued',
                'initiated_by'  => 'admin',
                'issued_by'     => $actorId,
                'issued_at'     => now(),
                'admin_notes'   => $notes,
                'group_id'      => $groupId,
            ]);

            return [$stock, $issuance];
        });

        self::audit('Consumable Issued (Admin)', $issuance, $actorId,
            "Issued {$quantity} × {$stock->name} to " . (User::find($recipientId)->name ?? "user #{$recipientId}")
            . ($groupId ? ' [kit ' . $groupId . ']' : ''));

        self::notifyRecipient($issuance, 'Consumable Item Issued',
            "{$quantity} × {$stock->name} has been issued to you." . ($notes ? ' Note: ' . $notes : ''));
        self::notifyStaffExcept($issuance, 'Consumable Issued',
            "{$quantity} × {$stock->name} issued by staff.", $actorId);
        self::checkLowStock($stock);

        return $issuance;
    }

    /**
     * Fulfill a borrower-initiated request: pending_issue → issued (+deduct).
     *
     * @throws \DomainException insufficient stock
     */
    public static function fulfill(ConsumableIssuance $issuance, int $actorId): ConsumableIssuance
    {
        [$stock] = DB::transaction(function () use ($issuance, $actorId) {
            $stock = ConsumableStock::where('id', $issuance->item_id)->lockForUpdate()->firstOrFail();

            if ($stock->stock_quantity < $issuance->quantity) {
                throw new \DomainException(
                    "Insufficient stock for \"{$stock->name}\" — {$stock->stock_quantity} {$stock->unit}(s) left."
                );
            }

            $stock->decrement('stock_quantity', $issuance->quantity);

            $issuance->forceFill([
                'status'    => 'issued',
                'issued_by' => $actorId,
                'issued_at' => now(),
            ])->saveQuietly(); // avoid double observer noise; we audit manually below

            return [$stock, $issuance];
        });

        self::audit('Borrower Request Fulfilled', $issuance, $actorId,
            "Fulfilled request: {$issuance->quantity} × {$stock->name}");

        self::notifyRecipient($issuance, 'Your Request has been Issued',
            "{$issuance->quantity} × {$stock->name} is ready — please confirm receipt.");
        self::notifyStaffExcept($issuance, 'Request Fulfilled',
            "{$issuance->quantity} × {$stock->name} fulfilled by staff.", $actorId);
        self::checkLowStock($stock);

        return $issuance;
    }

    /** Borrower confirms receipt: issued → confirmed. */
    public static function confirmReceipt(ConsumableIssuance $issuance): void
    {
        $issuance->forceFill(['status' => 'confirmed', 'confirmed_at' => now()])->save();
        $stock = $issuance->item;

        self::audit('Consumable Receipt Confirmed', $issuance, $issuance->user_id,
            "Borrower confirmed receipt of {$issuance->quantity} × {$stock->name}");

        // Notify the issuing staff member specifically (falls back to inventory staff)
        $recipients = $issuance->issued_by
            ? User::where('id', $issuance->issued_by)->get()
            : User::inventoryStaff();
        foreach ($recipients as $u) {
            Notification::create([
                'user_id' => $u->id, 'type' => 'consumable',
                'title'   => 'Receipt Confirmed',
                'message' => "{$issuance->user->name} confirmed {$issuance->quantity} × {$stock->name}.",
            ]);
        }
    }

    /**
     * Cancel from pending_issue (no restore) or issued (restores stock).
     *
     * @throws \DomainException invalid transition
     */
    public static function cancel(ConsumableIssuance $issuance, int $actorId, string $reason = ''): ConsumableStock
    {
        [$stock, $restored] = DB::transaction(function () use ($issuance, $actorId) {
            $stock = ConsumableStock::where('id', $issuance->item_id)->lockForUpdate()->firstOrFail();

            $restored = false;
            if ($issuance->status === 'issued') {
                $stock->increment('stock_quantity', $issuance->quantity);
                $restored = true;
            } elseif ($issuance->status !== 'pending_issue') {
                throw new \DomainException('Only pending or issued issuances can be cancelled.');
            }

            $issuance->forceFill(['status' => 'cancelled'])->save();

            return [$stock, $restored];
        });

        $reasonSuffix = $reason !== '' ? ' Reason: ' . $reason : '';
        self::audit('Issuance Cancelled', $issuance, $actorId,
            "Cancelled {$issuance->quantity} × {$stock->name}"
            . ($restored ? ' (stock restored)' : '') . $reasonSuffix);

        if ($restored) {
            self::checkLowStockClear($stock);
        }

        return $stock;
    }

    /* ────────────────────────────────────────────────
       Notifications
    ──────────────────────────────────────────────── */

    public static function notifyRecipient(ConsumableIssuance $issuance, string $title, string $message): void
    {
        try {
            Notification::create([
                'user_id' => $issuance->user_id, 'type' => 'consumable',
                'title' => $title, 'message' => $message,
            ]);
        } catch (\Throwable $e) {}
    }

    /** Staff fan-out minus the acting user (kills the old "Admin alert:" self-copy noise). */
    public static function notifyStaffExcept(ConsumableIssuance $issuance, string $title, string $message, ?int $exceptId = null): void
    {
        try {
            foreach (User::inventoryStaff() as $u) {
                if ($exceptId && $u->id === $exceptId) continue;
                Notification::create([
                    'user_id' => $u->id, 'type' => 'consumable',
                    'title' => $title, 'message' => $message,
                ]);
            }
        } catch (\Throwable $e) {}
    }

    /* ────────────────────────────────────────────────
       Low-stock alerting (deduplicated per stock/day)
    ──────────────────────────────────────────────── */

    public static function checkLowStock(ConsumableStock $stock): void
    {
        if (!($stock->isLowStock() || $stock->isOutOfStock())) {
            self::checkLowStockClear($stock);
            return;
        }

        $key = 'lowstock.alert.' . $stock->id . '.' . now()->format('Ymd');
        if (Cache::has($key)) return;

        Cache::put($key, true, now()->endOfDay());

        $state = $stock->isOutOfStock() ? 'OUT OF STOCK' : 'below minimum';
        foreach (User::inventoryStaff() as $u) {
            try {
                Notification::create([
                    'user_id' => $u->id, 'type' => 'alert',
                    'title'   => '⚠ Low Stock Alert',
                    'message' => "\"{$stock->name}\" is {$state} — {$stock->stock_quantity} {$stock->unit}(s) remaining "
                               . "(minimum: {$stock->getMinStockThreshold()}).",
                ]);
            } catch (\Throwable $e) {}
        }
    }

    private static function checkLowStockClear(ConsumableStock $stock): void
    {
        Cache::forget('lowstock.alert.' . $stock->id . '.' . now()->format('Ymd'));
    }

    /* ────────────────────────────────────────────────
       Audit helper
    ──────────────────────────────────────────────── */

    public static function audit(string $action, ConsumableIssuance $issuance, ?int $userId, string $description): void
    {
        try {
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => $action,
                'table_name'  => 'consumable_issuances',
                'record_id'   => $issuance->id,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {}
    }
}
