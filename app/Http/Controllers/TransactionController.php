<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BorrowRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // ==========================================
    // ADMIN: VIEW PENDING REQUESTS
    // ==========================================
    public function manageRequests(Request $request)
    {
        // 1. Base query using the correct BorrowRequest model
        $query = BorrowRequest::with(['user', 'item.category']);

        // 2. Handle Search (Transaction ID, Borrower Name, Item Tag, or QR Hash)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('qr_code_hash', $searchTerm) // <--- ADDED QR SEARCH HERE
                  ->orWhereHas('user', function($u) use ($searchTerm) {
                      $u->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('item', function($i) use ($searchTerm) {
                      $i->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('property_tag', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // 3. Handle Status Filtering
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // 4. Execute Query (Pending items pinned to the top, paginated for performance)
        $requests = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                          ->orderBy('created_at', 'desc')
                          ->paginate(6);

        return view('admin.borrow-requests', compact('requests'));
    }

    // ==========================================
    // ADMIN: APPROVE REQUEST
    // ==========================================
    public function approve(Request $request, $id)
    {
        try {
            $borrowRequest = BorrowRequest::findOrFail($id);
            
            // Security: Prevent double-approvals
            if ($borrowRequest->status !== 'pending') {
                return redirect()->back()->with('error', 'This request has already been processed.');
            }

            // Update Request Status
            $borrowRequest->status = 'approved';
            $borrowRequest->admin_remarks = $request->admin_remarks ?? 'Approved by ' . Auth::user()->name;
            
            // saveQuietly() bypasses background events to prevent infinite loading spinners
            $borrowRequest->saveQuietly(); 
            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'approval',
                'title' => 'Request Approved',
                'message' => 'Your request for the asset has been approved. Please see the Admin to claim it.'
            ]);
            return redirect()->back()->with('success', 'Request #REQ-' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . ' has been officially approved!');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // ADMIN: REJECT REQUEST
    // ==========================================
    public function reject(Request $request, $id)
    {
        // Force the admin to provide a reason for rejecting
        $request->validate([
            'admin_remarks' => 'required|string|max:500'
        ], [
            'admin_remarks.required' => 'You must provide a reason to the borrower explaining why this was rejected.'
        ]);

        try {
            $borrowRequest = BorrowRequest::findOrFail($id);
            
            if ($borrowRequest->status !== 'pending') {
                return redirect()->back()->with('error', 'This request has already been processed.');
            }

            // Reject the request and save the admin's reason
            $borrowRequest->status = 'rejected';
            $borrowRequest->admin_remarks = $request->admin_remarks;
            $borrowRequest->saveQuietly();

            // FREE UP THE INVENTORY: The item must become available again!
            $item = Item::find($borrowRequest->item_id);
            if ($item) {
                $item->status = 'available';
                $item->saveQuietly();
            }

            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'alert',
                'title' => 'Request Declined',
                'message' => 'Your request was declined. Reason: ' . $request->admin_remarks
            ]);
            return redirect()->back()->with('success', 'Request rejected. The item has been returned to available inventory.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }
    // ==========================================
    // ADMIN: VIEW RETURNS DASHBOARD
    // ==========================================
    public function manageReturns()
    {
        // Fetch items waiting for return verification
        $pendingReturns = BorrowRequest::with(['user', 'item.category'])
            ->where('status', 'return_pending')
            ->orderBy('updated_at', 'desc')
            ->paginate(8);

        return view('admin.returns', compact('pendingReturns'));
    }

    // ==========================================
    // ADMIN: CONFIRM ASSET RETURN
    // ==========================================
    public function markAsReturned(Request $request, $id)
    {
        // Allow the admin to override the borrower's condition report if needed
        $request->validate([
            'final_condition' => 'required|string'
        ]);

        try {
            $borrowRequest = BorrowRequest::findOrFail($id);

            if ($borrowRequest->status !== 'return_pending') {
                return redirect()->back()->with('error', 'This request is not pending a return.');
            }

            // 1. Mark the transaction as officially returned
            $borrowRequest->status = 'returned';
            $borrowRequest->admin_remarks = 'Return verified by ' . Auth::user()->name;
            $borrowRequest->return_condition = $request->final_condition;
            $borrowRequest->saveQuietly();

            // 2. Free up the physical inventory item & update its final condition
            $item = Item::find($borrowRequest->item_id);
            if ($item) {
                if ($request->final_condition === 'Good') {
                    $item->status = 'available';
                } elseif ($request->final_condition === 'Damaged') {
                    $item->status = 'damaged';
                } elseif ($request->final_condition === 'Needs Repair') {
                    $item->status = 'maintenance';
                }
                $item->saveQuietly();
            }
            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'return',
                'title' => 'Return Verified',
                'message' => 'The admin has successfully verified the return of your asset. Thank you!'
            ]);

            return redirect()->back()->with('success', 'Asset successfully returned and added back to the available inventory pool.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }
}
