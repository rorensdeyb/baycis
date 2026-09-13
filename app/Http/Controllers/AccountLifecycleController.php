<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Notification;

/**
 * Account Lifecycle — user-initiated account deactivation / deletion requests.
 *
 * Flow: user requests (with reason + PIN) → administrators are notified →
 * an admin approves or rejects from User Management. Approved deletions enter
 * a 60-day buffer before the scheduled purge command anonymizes the account.
 */
class AccountLifecycleController extends Controller
{
    /**
     * Request account deactivation (temporary).
     */
    public function requestDeactivation(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ], [
            'reason.min' => 'Please provide a reason of at least 10 characters.',
        ]);

        if ($user->hasPendingDeletionRequest() || $user->isScheduledForDeletion()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a deletion request in progress. Cancel it first if you want to request a deactivation instead.'
            ], 422);
        }

        if ($user->hasPendingDeactivationRequest()) {
            return response()->json([
                'status' => 'error',
                'message' => 'A deactivation request is already awaiting administrator review.'
            ], 422);
        }

        $user->update([
            'deactivation_requested_at' => now(),
            'deactivation_reason'       => $request->reason,
        ]);

        $this->audit($user, 'Deactivation Requested', "Requested account deactivation. Reason: {$request->reason}");
        $this->notifyAdmins('Account Deactivation Request', "{$user->name} ({$user->email}) requested account deactivation. Review it in User Management.");

        return response()->json([
            'status' => 'success',
            'message' => 'Deactivation request submitted. An administrator will review it shortly.'
        ]);
    }

    /**
     * Request permanent account deletion. Requires PIN confirmation.
     */
    public function requestDeletion(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'reason' => 'required|string|min:10|max:500',
            'pin'    => 'required|string|digits:4',
        ], [
            'reason.min' => 'Please provide a reason of at least 10 characters.',
        ]);

        // PIN verification (sensitive action)
        if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
            return response()->json([
                'status' => 'error',
                'message' => !$user->pin
                    ? 'You must set up your PIN in Account Settings before requesting deletion.'
                    : 'Incorrect PIN. Deletion request cancelled.',
                'require_pin' => true,
            ], 422);
        }

        if ($user->isScheduledForDeletion()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your deletion request was already approved and is inside the grace period.'
            ], 422);
        }

        if ($user->hasPendingDeletionRequest() || $user->hasPendingDeactivationRequest()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a pending account request awaiting administrator review.'
            ], 422);
        }

        $user->update([
            'deletion_requested_at' => now(),
            'deletion_reason'       => $request->reason,
        ]);

        $this->audit($user, 'Deletion Requested', "Requested permanent account deletion. Reason: {$request->reason}");
        $this->notifyAdmins('Account Deletion Request', "{$user->name} ({$user->email}) requested PERMANENT account deletion. Review it in User Management.");

        return response()->json([
            'status' => 'success',
            'message' => 'Deletion request submitted. Once approved, your account enters a '
                . User::DELETION_BUFFER_DAYS . '-day grace period before permanent removal.'
        ]);
    }

    /**
     * Cancel the signed-in user's own pending lifecycle request — including
     * an already-approved deletion while it is still inside the buffer window.
     */
    public function cancelRequest(Request $request)
    {
        $user = Auth::user();

        $hadPendingRequest = $user->hasPendingDeletionRequest() || $user->hasPendingDeactivationRequest();
        $wasScheduled = $user->isScheduledForDeletion();

        if (!$hadPendingRequest && !$wasScheduled) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have no pending account requests to cancel.'
            ], 422);
        }

        DB::transaction(function () use ($user, $wasScheduled) {
            $user->clearLifecycleRequests();
            $user->deletion_effective_at = null;

            // Cancelling an approved deletion restores access unless an admin
            // separately deactivated the account.
            if ($wasScheduled) {
                $user->is_active = true;
            }
            $user->save();
        });

        $this->audit($user, $wasScheduled ? 'Scheduled Deletion Cancelled' : 'Account Request Cancelled', 'User cancelled their pending account deletion/deactivation request.');
        $this->notifyAdmins(
            $wasScheduled ? 'Scheduled Deletion Cancelled' : 'Account Request Cancelled',
            "{$user->name} ({$user->email}) cancelled their account request."
        );

        return response()->json([
            'status' => 'success',
            'message' => $wasScheduled
                ? 'Scheduled deletion cancelled. Your account has been reactivated.'
                : 'Your pending account request has been cancelled.'
        ]);
    }

    private function audit(User $user, string $action, string $description): void
    {
        AuditLog::create([
            'user_id'     => $user->id,
            'action'      => $action,
            'table_name'  => 'users',
            'record_id'   => $user->id,
            'description' => $description,
        ]);
    }

    private function notifyAdmins(string $title, string $message): void
    {
        foreach (User::where('role', 'admin')->get() as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type'    => 'alert',
                'title'   => $title,
                'message' => $message,
            ]);
        }
    }
}
