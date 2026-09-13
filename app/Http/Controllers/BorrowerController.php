<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\BorrowRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ConsumableIssuance;
use App\Models\ConsumableStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BorrowerController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        // 1. Active equipment loans & return verifications
        $activeBorrowsCount = BorrowRequest::where('user_id', $userId)
            ->whereIn('status', ['approved', 'active'])
            ->count();

        $returnPendingCount = BorrowRequest::where('user_id', $userId)
            ->where('status', 'return_pending')
            ->count();

        // 2. Pending approvals (equipment requests + consumable supply requests)
        $pendingBorrowsCount = BorrowRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $pendingIssuancesCount = ConsumableIssuance::where('user_id', $userId)
            ->where('status', 'pending_issue')
            ->count();

        $totalPendingCount = $pendingBorrowsCount + $pendingIssuancesCount;

        // 3. Consumables issued by admin, waiting for borrower confirmation of receipt
        $pendingConfirmationsCount = ConsumableIssuance::where('user_id', $userId)
            ->where('status', 'issued')
            ->count();

        // 4. Completed transactions (returned borrows + confirmed issuances)
        $returnedBorrowsCount = BorrowRequest::where('user_id', $userId)
            ->where('status', 'returned')
            ->count();

        $confirmedIssuancesCount = ConsumableIssuance::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->count();

        $totalCompletedCount = $returnedBorrowsCount + $confirmedIssuancesCount;

        // 5. Active items currently held by user (for quick return access)
        $currentLoans = BorrowRequest::with(['item.category'])
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'active', 'return_pending'])
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();

        // 6. Unified recent activity (both Borrows and Consumable Issuances)
        $recentBorrows = BorrowRequest::with('item')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'type' => 'borrow',
                    'id' => $item->id,
                    'title' => $item->item->name ?? 'Unknown Item',
                    'meta' => 'Asset Loan • Tag: ' . ($item->item->property_tag ?? 'N/A'),
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'link' => route('borrower.history', ['tab' => 'borrows', 'search' => $item->id]),
                ];
            });

        $recentIssuances = ConsumableIssuance::with('item')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'type' => 'issuance',
                    'id' => $item->id,
                    'title' => $item->item->name ?? 'Unknown Supply',
                    'meta' => 'Consumable • Qty: ' . $item->quantity . ' ' . ($item->item->unit ?? 'unit(s)'),
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'link' => route('borrower.history', ['tab' => 'issuances', 'search' => $item->id]),
                ];
            });

        $recentActivity = $recentBorrows->concat($recentIssuances)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        // Backward compatibility variables for any views
        $activeBorrows = $activeBorrowsCount;
        $activeCount = $activeBorrowsCount;
        $pendingCount = $totalPendingCount;
        $pendingConfirmations = $pendingConfirmationsCount;

        return view('borrower.dashboard', compact(
            'activeBorrowsCount',
            'returnPendingCount',
            'totalPendingCount',
            'pendingBorrowsCount',
            'pendingIssuancesCount',
            'pendingConfirmationsCount',
            'totalCompletedCount',
            'currentLoans',
            'recentActivity',
            'activeBorrows',
            'activeCount',
            'pendingCount',
            'pendingConfirmations'
        ));
    }

    public function requests(Request $request)
    {
        $query = Item::with(['category', 'tag']);

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('property_tag', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $items = $query->orderBy('name')->get();
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
            
            // 👇 BUG FIX: Adding the required date field 👇
            $borrow->requested_date = now(); 
            
            $borrow->save(); 

            // 4. Update the item status
            $item->status = 'borrowed'; 
            $item->save(); 

            // 5. Trigger the Notification BEFORE the redirect!
            $admins = \App\Models\User::inventoryStaff();
            foreach ($admins as $adminUser) {
                \App\Models\Notification::create([
                    'user_id' => $adminUser->id,
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
        $tab = $request->get('tab', 'borrows');
        if (!in_array($tab, ['borrows', 'issuances'])) {
            $tab = 'borrows';
        }
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', 'all');

        $borrowsCount = BorrowRequest::where('user_id', $userId)->count();
        $issuancesCount = ConsumableIssuance::where('user_id', $userId)->count();

        // 1. Fixed Asset Borrows Query
        $borrowsQuery = BorrowRequest::with(['item.category'])->where('user_id', $userId);
        if ($search !== '') {
            $borrowsQuery->where(function ($q) use ($search) {
                $q->whereHas('item', function ($iq) use ($search) {
                    $iq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('property_tag', 'LIKE', "%{$search}%");
                })->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('purpose', 'LIKE', "%{$search}%");
            });
        }
        if ($status !== 'all' && $status !== '') {
            $borrowsQuery->where('status', $status);
        }
        $requests = $borrowsQuery->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'borrows_page')
            ->withQueryString();

        // 2. Consumable Issuances Query
        $issuancesQuery = ConsumableIssuance::with(['item', 'issuer'])->where('user_id', $userId);
        if ($search !== '') {
            $issuancesQuery->where(function ($q) use ($search) {
                $q->whereHas('item', function ($iq) use ($search) {
                    $iq->where('name', 'LIKE', "%{$search}%");
                })->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('purpose', 'LIKE', "%{$search}%");
            });
        }
        if ($status !== 'all' && $status !== '') {
            $issuancesQuery->where('status', $status);
        }
        $issuances = $issuancesQuery->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'issuances_page')
            ->withQueryString();

        return view('borrower.history', compact(
            'requests',
            'issuances',
            'tab',
            'borrowsCount',
            'issuancesCount',
            'search',
            'status'
        ));
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
            ->whereIn('status', ['approved', 'active', 'return_pending', 'borrowed'])
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
                                ->whereIn('status', ['approved', 'active', 'borrowed'])
                                ->firstOrFail();

            // 2. Mark it as "Return Pending" for the Admin to verify
            $borrowRequest->status = 'return_pending';
            $borrowRequest->return_condition = $request->return_condition;
            $borrowRequest->return_remarks = $request->return_remarks;
            $borrowRequest->saveQuietly();

            $admins = \App\Models\User::inventoryStaff();
            foreach ($admins as $adminUser) {
                \App\Models\Notification::create([
                    'user_id' => $adminUser->id,
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

    // ==========================================
    // ACTION: UPDATE PROFILE INFO
    // ==========================================
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validateWithBag('profileUpdate', [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->back()->with('success', 'Profile information updated successfully.');
    }

    // ==========================================
    // ACTION: UPDATE PASSWORD
    // ==========================================
    public function updatePassword(Request $request)
    {
        $request->validateWithBag('passwordUpdate', [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.'], 'passwordUpdate');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password successfully changed.');
    }

    // ==========================================
    // ACTION: UPDATE PIN
    // ==========================================
    public function updatePin(Request $request)
    {
        $request->validateWithBag('pinUpdate', [
            'current_pin' => 'required|digits:4',
            'new_pin' => 'required|digits:4|confirmed',
        ]);

        $user = Auth::user();

        // Check if the provided current PIN matches the hash in the database
        if (!Hash::check($request->current_pin, $user->pin)) {
            return back()->withErrors(['current_pin' => 'The provided PIN does not match your current PIN.'], 'pinUpdate');
        }

        $user->pin = Hash::make($request->new_pin);
        // Ensure the setup flag is true just in case
        $user->pin_setup_completed = true; 
        $user->save();

        return redirect()->back()->with('success', 'Security PIN successfully updated.');
    }
    
    public function account()
    {
        return view('borrower.account');
    }

    // ==========================================
    // VIEW: MY CONSUMABLE REQUESTS & ISSUANCES
    // ==========================================
    public function issuance(Request $request)
    {
        $userId = Auth::id();

        // Items waiting for borrower confirmation (admin already issued)
        $pendingConfirmations = ConsumableIssuance::with(['item'])
            ->where('user_id', $userId)
            ->where('status', 'issued')
            ->orderBy('issued_at', 'desc')
            ->get();

        // Borrower's own pending requests
        $myRequests = ConsumableIssuance::with(['item'])
            ->where('user_id', $userId)
            ->where('status', 'pending_issue')
            ->where('initiated_by', 'borrower')
            ->orderBy('created_at', 'desc')
            ->get();

        // Available consumable stocks for requesting
        $availableItems = ConsumableStock::where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        // ISS-8 favorites: most-confirmed items by this borrower (request-again shortcuts)
        $favorites = ConsumableIssuance::selectRaw('item_id, COUNT(*) as times, SUM(quantity) as total')
            ->where('user_id', $userId)
            ->where('status', 'confirmed')
            ->groupBy('item_id')
            ->orderByDesc('times')
            ->limit(3)
            ->with('item:id,name,unit')
            ->get()
            ->filter(fn ($f) => $f->item);

        // History (confirmed + cancelled)
        $history = ConsumableIssuance::with(['item', 'issuer'])
            ->where('user_id', $userId)
            ->whereIn('status', ['confirmed', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('borrower.issuance', compact(
            'pendingConfirmations',
            'myRequests',
            'history',
            'availableItems',
            'favorites'
        ));
    }

    // ==========================================
    // ACTION: REQUEST CONSUMABLE (Borrower-initiated)
    // ==========================================
    public function requestConsumable(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:consumable_stocks,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
        ]);

        try {
            $item = ConsumableStock::findOrFail($request->item_id);

            // ISS-8: server-side stock ceiling (was client-only before)
            if ($item->stock_quantity < $request->quantity) {
                $msg = "Insufficient stock. Only {$item->stock_quantity} {$item->unit}(s) available.";
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                return redirect()->back()->with('error', $msg);
            }

            // Create the request — stock NOT deducted yet (admin will do it)
            $issuance = ConsumableIssuance::create([
                'user_id' => Auth::id(),
                'item_id' => $item->id,
                'quantity' => $request->quantity,
                'purpose' => $request->purpose,
                'status' => 'pending_issue',
                'initiated_by' => 'borrower',
            ]);

            // Notify all inventory staff
            foreach (User::inventoryStaff() as $staffUser) {
                \App\Models\Notification::create([
                    'user_id' => $staffUser->id,
                    'type' => 'consumable',
                    'title' => 'New Consumable Request',
                    'message' => Auth::user()->name . ' requested ' . $request->quantity . ' ' . $item->unit . '(s) of ' . $item->name,
                ]);
            }
            // Confirmation copy to the requester (new — closes the loop)
            \App\Models\Notification::create([
                'user_id' => Auth::id(),
                'type' => 'consumable',
                'title' => 'Request Submitted',
                'message' => "Your request for {$request->quantity} {$item->unit}(s) of {$item->name} was submitted.",
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'id' => $issuance->id,
                    'message' => "Request submitted for {$request->quantity} {$item->unit}(s) of '{$item->name}'."]);
            }

            return redirect()->route('borrower.issuance')->with('success',
                "Request submitted for {$request->quantity} {$item->unit}(s) of '{$item->name}'. Awaiting admin approval.");

        } catch (\Throwable $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // ACTION: CONFIRM RECEIPT (Borrower confirms)
    // ==========================================
    public function confirmReceipt($id)
    {
        try {
            $issuance = ConsumableIssuance::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'issued')
                ->firstOrFail();

            \App\Services\IssuanceService::confirmReceipt($issuance);

            return redirect()->route('borrower.issuance')->with('success',
                'Receipt confirmed! Thank you for acknowledging the issuance.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // ACTION: CANCEL OWN REQUEST
    public function cancelConsumableRequest($id)
    {
        try {
            $issuance = ConsumableIssuance::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending_issue')
                ->firstOrFail();

            \App\Services\IssuanceService::cancel($issuance, Auth::id());

            return redirect()->route('borrower.issuance')->with('success', 'Your request has been cancelled.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}