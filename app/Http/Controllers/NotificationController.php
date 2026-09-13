<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read and redirect the user
     * to the page most relevant to that notification type, or return JSON for AJAX.
     */
    public function markRead(Request $request, $id)
    {
        $notif = Notification::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        // Mark as read
        $notif->is_read = 1;
        $notif->save();

        $role = Auth::user()->role ?? 'borrower';
        $targetUrl = $this->getTargetUrl($notif, $role);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'is_read'    => true,
                'target_url' => $targetUrl,
                'unread'     => Notification::where('user_id', Auth::id())->where('is_read', 0)->count(),
            ]);
        }

        return redirect($targetUrl);
    }

    /**
     * Toggle read/unread status of a single notification.
     */
    public function toggleRead(Request $request, $id)
    {
        $notif = Notification::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        $notif->is_read = $notif->is_read ? 0 : 1;
        $notif->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_read' => (bool) $notif->is_read,
                'unread'  => Notification::where('user_id', Auth::id())->where('is_read', 0)->count(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Delete an individual notification.
     */
    public function destroy(Request $request, $id)
    {
        $notif = Notification::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        $notif->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread'  => Notification::where('user_id', Auth::id())->where('is_read', 0)->count(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Mark ALL notifications for the authenticated user as read.
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', Auth::id())
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread'  => 0,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Clear notifications for authenticated user (optionally only read ones, or all).
     */
    public function clearAll(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());

        if ($request->input('filter') === 'read') {
            $query->where('is_read', 1);
        }

        $query->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread'  => Notification::where('user_id', Auth::id())->where('is_read', 0)->count(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Determine destination target URL for a notification based on type and user role.
     */
    public function getTargetUrl($notif, string $role): string
    {
        $title = strtolower($notif->title ?? '');
        $msg = strtolower($notif->message ?? '');

        if ($role === 'admin' || $role === 'custodian') {
            // Account lifecycle management (Admin only)
            if ($role === 'admin') {
                if (str_contains($title, 'deactivation') || str_contains($msg, 'deactivation')) {
                    return route('admin.users', ['status' => 'deactivation_requested']);
                }
                if (str_contains($title, 'deletion') || str_contains($msg, 'deletion')) {
                    return route('admin.users', ['status' => 'deletion_requested']);
                }
                if (str_contains($title, 'registration') || str_contains($msg, 'registration') 
                    || str_contains($title, 'pending approval') || str_contains($title, 'activation') 
                    || str_contains($msg, 'activation') || str_contains($title, 'account')) {
                    return route('admin.users', ['status' => 'pending_approval']);
                }
            }

            // Specific borrow request notifications
            if (str_contains($title, 'request') || str_contains($msg, 'request #req-') || str_contains($title, 'borrow')) {
                return route('admin.requests');
            }

            return match ($notif->type) {
                'request'     => route('admin.requests'),
                'return'      => route('admin.returns'),
                'approval'    => route('items.issuance'),
                'consumable'  => route('items.issuance'),
                'alert'       => route('admin.dashboard'),
                default       => route('admin.dashboard'),
            };
        }

        // Borrower side
        if (str_contains($title, 'decline') || str_contains($msg, 'decline') 
            || str_contains($title, 'reject') || str_contains($msg, 'reject')) {
            return route('borrower.history');
        }

        if (str_contains($title, 'issuance') || str_contains($msg, 'issuance') 
            || str_contains($title, 'consumable') || str_contains($msg, 'consumable') || str_contains($title, 'supply')) {
            return route('borrower.issuance');
        }

        return match ($notif->type) {
            'approval'    => route('borrower.returns'),
            'return'      => route('borrower.history'),
            'consumable'  => route('borrower.issuance'),
            'alert'       => route('borrower.dashboard'),
            default       => route('borrower.dashboard'),
        };
    }

    /**
     * Get icon and color class meta for notification types.
     */
    public function getTypeMeta(?string $type): array
    {
        return match ($type) {
            'request'    => ['icon' => 'bi-arrow-left-right', 'class' => 'text-primary', 'bg' => 'rgba(13,110,253,0.12)'],
            'return'     => ['icon' => 'bi-arrow-return-left', 'class' => 'text-success', 'bg' => 'rgba(25,135,84,0.12)'],
            'approval'   => ['icon' => 'bi-check-circle-fill', 'class' => 'text-success', 'bg' => 'rgba(25,135,84,0.12)'],
            'consumable' => ['icon' => 'bi-box-seam-fill', 'class' => 'text-info', 'bg' => 'rgba(13,202,240,0.12)'],
            'alert'      => ['icon' => 'bi-exclamation-triangle-fill', 'class' => 'text-danger', 'bg' => 'rgba(220,53,69,0.12)'],
            default      => ['icon' => 'bi-bell-fill', 'class' => 'text-secondary', 'bg' => 'rgba(108,117,125,0.12)'],
        };
    }

    /**
     * AJAX endpoint: return unread count, recent notifications, new notifications,
     * and real-time pending counters as JSON.
     * Used by the auto-polling system so the bell badge, dropdown, sidebar badges,
     * and active views update without a full page refresh.
     */
    public function poll(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'unread'            => 0,
                'max_id'            => 0,
                'notifications'     => [],
                'new_notifications' => [],
                'counts'            => [],
            ]);
        }

        $userId = $user->id;
        $role = $user->role ?? 'borrower';

        $unread = Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        $maxId = (int) Notification::where('user_id', $userId)->max('id');

        $notifications = Notification::where('user_id', $userId)
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($n) use ($role) {
                $meta = $this->getTypeMeta($n->type);
                return [
                    'id'             => $n->id,
                    'title'          => $n->title,
                    'message'        => $n->message,
                    'type'           => $n->type,
                    'is_read'        => (bool) $n->is_read,
                    'time_ago'       => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                    'formatted_time' => $n->created_at ? $n->created_at->format('M d, g:i A') : '',
                    'url'            => route('notifications.read', $n->id),
                    'target_url'     => $this->getTargetUrl($n, $role),
                    'type_icon'      => $meta['icon'],
                    'type_class'     => $meta['class'],
                    'type_bg'        => $meta['bg'],
                ];
            });

        // Detect newly created unread notifications since client's last seen ID
        $newNotifications = [];
        if ($request->filled('since_id')) {
            $sinceId = (int) $request->since_id;
            if ($sinceId > 0) {
                $newNotifications = Notification::where('user_id', $userId)
                    ->where('id', '>', $sinceId)
                    ->where('is_read', 0)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($n) use ($role) {
                        $meta = $this->getTypeMeta($n->type);
                        return [
                            'id'             => $n->id,
                            'title'          => $n->title,
                            'message'        => $n->message,
                            'type'           => $n->type,
                            'is_read'        => (bool) $n->is_read,
                            'time_ago'       => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                            'formatted_time' => $n->created_at ? $n->created_at->format('M d, g:i A') : '',
                            'url'            => route('notifications.read', $n->id),
                            'target_url'     => $this->getTargetUrl($n, $role),
                            'type_icon'      => $meta['icon'],
                            'type_class'     => $meta['class'],
                            'type_bg'        => $meta['bg'],
                        ];
                    });
            }
        }

        $counts = [];

        if ($role === 'admin' || $role === 'custodian') {
            $counts['pending_borrow_requests'] = class_exists(\App\Models\BorrowRequest::class)
                ? \App\Models\BorrowRequest::where('status', 'pending')->count() : 0;
            $counts['pending_returns'] = class_exists(\App\Models\BorrowRequest::class)
                ? \App\Models\BorrowRequest::where('status', 'return_pending')->count() : 0;

            $pendingIssue = class_exists(\App\Models\ConsumableIssuance::class)
                ? \App\Models\ConsumableIssuance::where('status', 'pending_issue')->count() : 0;
            $pendingConfirm = class_exists(\App\Models\ConsumableIssuance::class)
                ? \App\Models\ConsumableIssuance::where('status', 'issued')->count() : 0;

            $counts['pending_issue_requests'] = $pendingIssue;
            $counts['pending_confirmations'] = $pendingConfirm;
            $counts['pending_consumable_issuances'] = $pendingIssue + $pendingConfirm;
            $counts['active_borrows'] = class_exists(\App\Models\BorrowRequest::class)
                ? \App\Models\BorrowRequest::whereIn('status', ['approved', 'active'])->count() : 0;

            if ($role === 'admin' && class_exists(\App\Models\User::class)) {
                $counts['pending_account_requests'] = \App\Models\User::where(function ($q) {
                    $q->whereNotNull('deactivation_requested_at')
                      ->orWhere(function ($qq) {
                          $qq->whereNotNull('deletion_requested_at')->whereNull('deletion_effective_at');
                      });
                })->orWhere(function ($q) {
                    $q->where('registration_source', 'self_registered')
                      ->where('is_active', false)
                      ->whereNotNull('email_verified_at');
                })->count();
            } else {
                $counts['pending_account_requests'] = 0;
            }

            $counts['total_nav_pending'] = $counts['pending_borrow_requests'] +
                                           $counts['pending_returns'] +
                                           $counts['pending_consumable_issuances'] +
                                           $counts['pending_account_requests'];

            $counts['latest_request_id'] = class_exists(\App\Models\BorrowRequest::class)
                ? (int) \App\Models\BorrowRequest::max('id') : 0;
            $counts['latest_request_updated_at'] = class_exists(\App\Models\BorrowRequest::class)
                ? (string) \App\Models\BorrowRequest::max('updated_at') : '';
            $counts['latest_return_id'] = class_exists(\App\Models\BorrowRequest::class)
                ? (int) \App\Models\BorrowRequest::where('status', 'return_pending')->max('id') : 0;
            $counts['latest_return_updated_at'] = class_exists(\App\Models\BorrowRequest::class)
                ? (string) \App\Models\BorrowRequest::whereIn('status', ['return_pending', 'returned'])->max('updated_at') : '';
            $counts['latest_issuance_id'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? (int) \App\Models\ConsumableIssuance::max('id') : 0;
            $counts['latest_issuance_updated_at'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? (string) \App\Models\ConsumableIssuance::max('updated_at') : '';
        } else {
            // Borrower counters
            $counts['pending_confirmations'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? \App\Models\ConsumableIssuance::where('user_id', $userId)->where('status', 'issued')->count() : 0;
            $counts['active_loans'] = class_exists(\App\Models\BorrowRequest::class)
                ? \App\Models\BorrowRequest::where('user_id', $userId)->whereIn('status', ['approved', 'active'])->count() : 0;
            $counts['pending_borrow_requests'] = class_exists(\App\Models\BorrowRequest::class)
                ? \App\Models\BorrowRequest::where('user_id', $userId)->where('status', 'pending')->count() : 0;
            $counts['pending_consumable_issuances'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? \App\Models\ConsumableIssuance::where('user_id', $userId)->where('status', 'pending_issue')->count() : 0;

            $counts['total_nav_pending'] = $counts['pending_confirmations'] +
                                           $counts['active_loans'] +
                                           $counts['pending_borrow_requests'] +
                                           $counts['pending_consumable_issuances'];

            $counts['latest_request_id'] = class_exists(\App\Models\BorrowRequest::class)
                ? (int) \App\Models\BorrowRequest::where('user_id', $userId)->max('id') : 0;
            $counts['latest_request_updated_at'] = class_exists(\App\Models\BorrowRequest::class)
                ? (string) \App\Models\BorrowRequest::where('user_id', $userId)->max('updated_at') : '';
            $counts['latest_return_updated_at'] = class_exists(\App\Models\BorrowRequest::class)
                ? (string) \App\Models\BorrowRequest::where('user_id', $userId)->whereIn('status', ['return_pending', 'returned'])->max('updated_at') : '';
            $counts['latest_issuance_id'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? (int) \App\Models\ConsumableIssuance::where('user_id', $userId)->max('id') : 0;
            $counts['latest_issuance_updated_at'] = class_exists(\App\Models\ConsumableIssuance::class)
                ? (string) \App\Models\ConsumableIssuance::where('user_id', $userId)->max('updated_at') : '';
        }

        return response()->json([
            'unread'            => $unread,
            'max_id'            => $maxId,
            'notifications'     => $notifications,
            'new_notifications' => $newNotifications,
            'counts'            => $counts,
        ]);
    }
}
