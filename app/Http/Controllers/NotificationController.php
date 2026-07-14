<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read and redirect the user
     * to the page most relevant to that notification type.
     */
    public function markRead($id)
    {
        $notif = Notification::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->firstOrFail();

        // Mark as read
        $notif->is_read = 1;
        $notif->save();

        // Redirect based on notification type & user role
        $role = Auth::user()->role ?? 'borrower';

        if ($role === 'admin') {
            return match ($notif->type) {
                'request'  => redirect()->route('admin.requests'),
                'return'   => redirect()->route('admin.returns'),
                'alert'    => redirect()->route('admin.dashboard'),
                default    => redirect()->route('admin.dashboard'),
            };
        }

        // Borrower side
        return match ($notif->type) {
            'approval' => redirect()->route('borrower.returns'),
            'return'   => redirect()->route('borrower.history'),
            'alert'    => redirect()->route('borrower.dashboard'),
            default    => redirect()->route('borrower.dashboard'),
        };
    }

    /**
     * Mark ALL notifications for the authenticated user as read.
     */
    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);

        return redirect()->back();
    }
}
