<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\BorrowRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BorrowerController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        $activeCount = BorrowRequest::where('user_id', $userId)
            ->whereIn('status', ['approved', 'active'])
            ->count();
            
        $pendingCount = BorrowRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $recentActivity = BorrowRequest::with('item')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('borrower.dashboard', compact('activeCount', 'pendingCount', 'recentActivity'));
    }

    public function requests(Request $request)
    {
        $query = Item::with('category');

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('property_tag', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $items = $query->get();
        $qrCodeHash = (string) Str::uuid();

        return view('borrower.requests', compact('items', 'qrCodeHash'));
    }

    public function submitRequest(Request $request)
    {
        // 1. Validate the Request
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'purpose' => 'required|string|max:500',
            'qr_code_hash' => 'required|string'
        ], [
            'item_id.required' => 'Please select an item to request.',
            'purpose.required' => 'Please provide a Reason for your Request.'
        ]);

        try {
            // 2. Check item availability
            $item = Item::findOrFail($request->item_id);
            if ($item->status !== 'available') {
                return redirect()->back()->with('error', 'Sorry, this item was just taken by someone else.')->withInput();
            }

            // 3. HARD SAVE
            $borrow = new BorrowRequest();
            $borrow->user_id = Auth::id();
            $borrow->item_id = $request->item_id;
            $borrow->purpose = $request->purpose;
            $borrow->status = 'pending';
            $borrow->qr_code_hash = $request->qr_code_hash;
            $borrow->save(); 

            // 4. Update the item status
            $item->status = 'borrowed'; 
            $item->save(); 

            // 5. Trigger the Notification BEFORE the redirect!
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'request',
                    'title' => 'New Borrow Request',
                    'message' => auth()->user()->name . ' has submitted a new borrow request.'
                ]);
            }

            // 6. Success Redirect
            return redirect()->route('borrower.requests')->with([
                'success' => 'Request Submitted!',
                'qr_code_success' => $request->qr_code_hash,
                'completed_item' => $item->name
            ]);
            
        } catch (\Throwable $e) {
            dd("SYSTEM ERROR DETECTED: " . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $userId = Auth::id();

        $query = BorrowRequest::with(['item.category'])->where('user_id', $userId);

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('item', function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('property_tag', 'LIKE', "%{$searchTerm}%");
            })->orWhere('id', 'LIKE', "%{$searchTerm}%"); 
        }

        if ($request->has('status') && $request->status != 'all' && $request->status != '') {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('borrower.history', compact('requests'));
    }

    public function cancelRequest($id)
    {
        try {
            $borrowRequest = BorrowRequest::where('id', $id)
                                ->where('user_id', Auth::id())
                                ->firstOrFail();

            if ($borrowRequest->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending requests can be cancelled.');
            }

            $borrowRequest->status = 'cancelled';
            $borrowRequest->save();

            $item = Item::find($borrowRequest->item_id);
            if ($item) {
                $item->status = 'available';
                $item->save();
            }

            return redirect()->back()->with('success', 'Your request has been successfully cancelled.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // VIEW: RETURNS DASHBOARD
    // ==========================================
    public function returns()
    {
        $userId = Auth::id();

        // Fetch items the user is currently borrowing, AND items waiting for admin return approval
        $activeBorrows = BorrowRequest::with('item.category')
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'active', 'return_pending'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('borrower.returns', compact('activeBorrows'));
    }

    // ==========================================
    // ACTION: SUBMIT RETURN
    // ==========================================
    public function submitReturn(Request $request, $id)
    {
        $request->validate([
            'return_condition' => 'required|in:Good,Damaged,Needs Repair',
            'return_remarks' => 'nullable|string|max:500'
        ], [
            'return_condition.required' => 'Please declare the current condition of the item.'
        ]);

        try {
            // 1. Find the active request
            $borrowRequest = BorrowRequest::where('id', $id)
                                ->where('user_id', Auth::id())
                                ->whereIn('status', ['approved', 'active'])
                                ->firstOrFail();

            // 2. Mark it as "Return Pending" for the Admin to verify
            $borrowRequest->status = 'return_pending';
            $borrowRequest->return_condition = $request->return_condition;
            $borrowRequest->return_remarks = $request->return_remarks;
            $borrowRequest->saveQuietly();

            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'return',
                    'title' => 'Asset Return Pending',
                    'message' => auth()->user()->name . ' has initiated an asset return. Please verify the condition.'
                ]);
            }
            return redirect()->back()->with('success', 'Return initiated! Please hand the item to the Admin for final system verification.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }


    public function account()
    {
        return view('borrower.account');
    }
}