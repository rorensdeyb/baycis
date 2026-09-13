<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BorrowRequest;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                $q->whereLike('id', "%{$searchTerm}%")
                  ->orWhere('qr_code_hash', $searchTerm) // <--- ADDED QR SEARCH HERE
                  ->orWhereHas('user', function($u) use ($searchTerm) {
                      $u->whereLike('name', "%{$searchTerm}%");
                  })
                  ->orWhereHas('item', function($i) use ($searchTerm) {
                      $i->whereLike('name', "%{$searchTerm}%")
                        ->orWhereLike('property_tag', "%{$searchTerm}%");
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

        if ($request->ajax() || $request->has('ajax_table')) {
            return view('admin.partials.borrow-requests-table', compact('requests'))->render();
        }

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
            // Notify the borrower
            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'approval',
                'title' => 'Request Approved',
                'message' => 'Your request for the asset has been approved. Please see the Admin to claim it.'
            ]);

            // Also notify ALL admin users (notification bug fix)
            $admins = User::inventoryStaff();
            foreach ($admins as $adminUser) {
                \App\Models\Notification::create([
                    'user_id' => $adminUser->id,
                    'type' => 'approval',
                    'title' => 'Request Approved',
                    'message' => 'Request #REQ-' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . ' has been approved by ' . Auth::user()->name . '.'
                ]);
            }

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

            // Notify the borrower
            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'alert',
                'title' => 'Request Declined',
                'message' => 'Your request was declined. Reason: ' . $request->admin_remarks
            ]);

            // Also notify ALL admin users (notification bug fix)
            $admins = User::inventoryStaff();
            foreach ($admins as $adminUser) {
                \App\Models\Notification::create([
                    'user_id' => $adminUser->id,
                    'type' => 'alert',
                    'title' => 'Request Declined',
                    'message' => 'Request #REQ-' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . ' was declined by ' . Auth::user()->name . '. Reason: ' . $request->admin_remarks
                ]);
            }

            return redirect()->back()->with('success', 'Request rejected. The item has been returned to available inventory.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }
    // ==========================================
    // ADMIN: INITIATE WALK-IN BORROW
    // ==========================================
    public function adminInitiateRequest(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'item_id'       => 'required|exists:items,id',
            'purpose'       => 'required|string|max:500',
            'admin_remarks' => 'nullable|string|max:500',
            'pin'           => 'required|string|digits:4',
        ]);

        try {
            // 0. 🔒 PIN verification — prevent unauthorized borrow initiations
            $adminUser = Auth::user();
            if (!$adminUser->pin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must set up your PIN in Account Settings before initiating walk-in borrows.'
                ], 422);
            }
            if (!Hash::check($request->pin, $adminUser->pin)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect PIN. Walk-in borrow cancelled.'
                ], 422);
            }

            // Use a DB transaction to prevent race conditions
            return DB::transaction(function () use ($request, $adminUser) {

            // 1. Verify the item is available (locked inside transaction)
            $item = Item::where('id', $request->item_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();
            if (!$item) {
                // Item may have been taken by another concurrent request
                $existingItem = Item::find($request->item_id);
                $status = $existingItem ? $existingItem->status : 'deleted';
                throw new \Exception('This item is not currently available. Current status: ' . ucfirst($status) . '.');
            }

            // 2. Verify the user is a valid borrower
            $borrower = User::findOrFail($request->user_id);
            if (!$borrower->is_active) {
                throw new \Exception('This account is inactive. Cannot create a borrow request for an inactive user.');
            }

            // 3. Generate QR code hash (same pattern as borrower portal)
            $qrHash = \Illuminate\Support\Str::uuid()->toString();

            // 4. Create the BorrowRequest — set properties manually, use saveQuietly for consistency
            $borrowRequest = new BorrowRequest();
            $borrowRequest->user_id           = $request->user_id;
            $borrowRequest->item_id           = $request->item_id;
            // NOTE: requested_date was dropped by migration 2026_07_02_183755;
            // request time is recorded in created_at.
            $borrowRequest->purpose           = $request->purpose;
            $borrowRequest->status            = 'approved';
            $borrowRequest->admin_remarks     = $request->admin_remarks ?? 'Walk-in request initiated by ' . $adminUser->name;
            $borrowRequest->qr_code_hash      = $qrHash;
            $borrowRequest->saveQuietly();

            // 5. Lock the item as borrowed (status already verified in the WHERE clause)
            $item->status = 'borrowed';
            $item->saveQuietly();

            // 6. Notify the borrower
            \App\Models\Notification::create([
                'user_id' => $borrower->id,
                'type'    => 'approval',
                'title'   => 'Walk-in Borrow Approved',
                'message' => 'Your walk-in request for "' . $item->name . '" has been approved by ' . $adminUser->name . '. Please see the Admin to claim it.'
            ]);

            // 7. Notify all admins
            $admins = User::inventoryStaff();
            foreach ($admins as $notifAdmin) {
                \App\Models\Notification::create([
                    'user_id' => $notifAdmin->id,
                    'type'    => 'approval',
                    'title'   => 'Walk-in Borrow Initiated',
                    'message' => $adminUser->name . ' initiated a walk-in borrow for ' . $borrower->name . ' — "' . $item->name . '"'
                ]);
            }

            // 8. Audit log
            \App\Models\AuditLog::create([
                'user_id'     => $adminUser->id,
                'action'      => 'Walk-in Borrow Initiated',
                'table_name'  => 'borrow_requests',
                'record_id'   => $borrowRequest->id,
                'description' => $adminUser->name . ' initiated a walk-in borrow for ' . $borrower->name . ' — ' . $item->name . ' (#' . $item->property_tag . ')'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Walk-in borrow created! ' . $borrower->name . ' can now claim the item.',
                'borrow'  => [
                    'id'         => $borrowRequest->id,
                    'item_name'  => $item->name,
                    'property_tag' => $item->property_tag,
                    'user_name'  => $borrower->name,
                ]
            ], 201);

            }); // End DB::transaction

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // ==========================================
    // ADMIN: CANCEL WALK-IN BORROW
    // ==========================================
    public function cancelInitiatedBorrow(Request $request, $id)
    {
        try {
            $borrowRequest = BorrowRequest::findOrFail($id);

            // Only allow cancellation for 'approved' status
            if ($borrowRequest->status !== 'approved') {
                return redirect()->back()->with('error', 'This request cannot be cancelled because it is not in an approved/active state.');
            }

            // 🔒 Restrict to walk-in borrows only (admin-initiated)
            if (!$borrowRequest->isWalkin()) {
                return redirect()->back()->with('error', 'Only walk-in (admin-initiated) borrows can be cancelled through this action.');
            }

            $cancelReason = $request->input('cancel_reason', 'Borrower did not claim the item.');

            DB::transaction(function () use ($borrowRequest, $cancelReason) {
                // Free the item
                $item = Item::find($borrowRequest->item_id);
                if ($item) {
                    $item->status = 'available';
                    $item->saveQuietly();
                }

                // Mark the request as cancelled with admin's reason
                $borrowRequest->status = 'cancelled';
                $borrowRequest->admin_remarks = 'Cancelled by ' . Auth::user()->name . ' — ' . $cancelReason;
                $borrowRequest->saveQuietly();

                // Notify the borrower
                \App\Models\Notification::create([
                    'user_id' => $borrowRequest->user_id,
                    'type'    => 'alert',
                    'title'   => 'Walk-in Borrow Cancelled',
                    'message' => 'Your walk-in borrow request for "' . ($borrowRequest->item->name ?? 'asset') . '" has been cancelled by ' . Auth::user()->name . '. Please contact the admin for details.'
                ]);

                // Notify all admins
                $admins = User::inventoryStaff();
                foreach ($admins as $adminUser) {
                    \App\Models\Notification::create([
                        'user_id' => $adminUser->id,
                        'type'    => 'alert',
                        'title'   => 'Borrow Cancelled',
                        'message' => Auth::user()->name . ' cancelled borrow request #REQ-' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . ' for ' . ($borrowRequest->user->name ?? 'Unknown') . '.'
                    ]);
                }

                // Audit log
                \App\Models\AuditLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Borrow Cancelled',
                    'table_name'  => 'borrow_requests',
                    'record_id'   => $borrowRequest->id,
                    'description' => Auth::user()->name . ' cancelled borrow request #REQ-' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . ' for ' . ($borrowRequest->user->name ?? 'Unknown') . ' — Item freed back to available.'
                ]);
            });

            return redirect()->back()->with('success', 'Borrow request cancelled. The item has been returned to available inventory.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // ADMIN: VIEW RETURNS DASHBOARD
    // ==========================================
    public function manageReturns(\Illuminate\Http\Request $request)
    {
        // Fetch items waiting for return verification
        $query = BorrowRequest::with(['user', 'item.category'])
            ->where('status', 'return_pending');

        // Handle search
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($u) use ($searchTerm) {
                    $u->whereLike('name', "%{$searchTerm}%");
                })
                ->orWhereHas('item', function($i) use ($searchTerm) {
                    $i->whereLike('name', "%{$searchTerm}%")
                      ->orWhereLike('property_tag', "%{$searchTerm}%");
                });
            });
        }

        // Handle condition filter
        if ($request->has('condition') && $request->condition != 'all') {
            $query->where('return_condition', $request->condition);
        }

        $pendingReturns = $query->orderBy('updated_at', 'desc')
            ->paginate(8);

        if ($request->ajax() || $request->has('ajax_table')) {
            return view('admin.partials.returns-table', compact('pendingReturns'))->render();
        }

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
            // Notify the borrower
            \App\Models\Notification::create([
                'user_id' => $borrowRequest->user_id,
                'type' => 'return',
                'title' => 'Return Verified',
                'message' => 'The admin has successfully verified the return of your asset. Thank you!'
            ]);

            // Also notify ALL admin users (notification bug fix)
            $admins = User::inventoryStaff();
            foreach ($admins as $adminUser) {
                \App\Models\Notification::create([
                    'user_id' => $adminUser->id,
                    'type' => 'return',
                    'title' => 'Return Verified',
                    'message' => Auth::user()->name . ' verified the return of asset #' . str_pad($borrowRequest->id, 4, '0', STR_PAD_LEFT) . '.'
                ]);
            }

            return redirect()->back()->with('success', 'Asset successfully returned and added back to the available inventory pool.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }
}
