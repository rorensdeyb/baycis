<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController; 

/*
|--------------------------------------------------------------------------
| Public Routes (Guests Only)
|--------------------------------------------------------------------------
*/

// 1. The Entry Point
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin' 
            ? redirect('/admin/dashboard') 
            : redirect('/borrower/dashboard');
    }
    return view('welcome');
})->name('login'); 

// 2. Offline PWA Route
Route::get('/offline', function () {
    return view('offline');
});

/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::post('/login-process', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| Protected Application Routes (Requires Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // ADMIN: COMMAND CENTER
    // ==========================================
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); // <--- FIXED
    Route::get('/admin/issuance', [ItemController::class, 'issuance'])->name('items.issuance');

    // BATCH & ARCHIVE ROUTES (Must be above {id} routes)
    Route::get('/admin/inventory/print-batch', [ItemController::class, 'printBatch'])->name('items.print-batch');
    Route::get('/admin/archive', [ItemController::class, 'archive'])->name('items.archive');
    Route::post('/admin/inventory/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::delete('/admin/inventory/{id}/force', [ItemController::class, 'forceDelete'])->name('items.force-delete');

    // ==========================================
    // ADMIN: INVENTORY
    // ==========================================
    Route::get('/admin/inventory', [ItemController::class, 'index'])->name('items.index');
    Route::get('/admin/inventory/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/admin/inventory', [ItemController::class, 'store'])->name('items.store');

    Route::get('/admin/inventory/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/admin/inventory/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/admin/inventory/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/admin/inventory/{id}/print-tag', [ItemController::class, 'printTag'])->name('items.print-tag');


    // ==========================================
    // ADMIN: TRANSACTIONS & SYSTEM
    // ==========================================
    Route::get('/admin/history', [ItemController::class, 'transactionHistory'])->name('items.history');
    
    // ==========================================
    // ADMIN: BORROW REQUESTS & RETURNS
    // ==========================================
    Route::get('/admin/requests', [\App\Http\Controllers\TransactionController::class, 'manageRequests'])->name('admin.requests');
    Route::post('/admin/requests/{id}/approve', [\App\Http\Controllers\TransactionController::class, 'approve'])->name('admin.requests.approve');
    Route::post('/admin/requests/{id}/reject', [\App\Http\Controllers\TransactionController::class, 'reject'])->name('admin.requests.reject');
    Route::post('/admin/requests/{id}/return', [\App\Http\Controllers\TransactionController::class, 'markAsReturned'])->name('admin.requests.return');

    Route::get('/admin/returns', [\App\Http\Controllers\TransactionController::class, 'manageReturns'])->name('admin.returns');

    Route::get('/admin/reports', [ItemController::class, 'reports'])->name('admin.reports'); // <--- FIXED

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users'); // <--- FIXED
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store'); // <--- FIXED


    // ==========================================
    // ADMIN: SYSTEM & SETTINGS (Corrected)
    // ==========================================
    Route::get('/admin/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/admin/settings/general', [\App\Http\Controllers\SettingsController::class, 'updateGeneral'])->name('admin.settings.general');
    Route::post('/admin/settings/inventory', [\App\Http\Controllers\SettingsController::class, 'updateInventory'])->name('admin.settings.inventory');
    Route::post('/admin/settings/appearance', [\App\Http\Controllers\SettingsController::class, 'updateAppearance'])->name('admin.settings.appearance');
    
    // Backup & Restore remain in AdminController
    Route::get('/admin/settings/backup/download', [AdminController::class, 'downloadBackup'])->name('admin.backup.download');
    Route::post('/admin/settings/backup/restore', [AdminController::class, 'restoreBackup'])->name('admin.backup.restore');

    // ==========================================
    // BORROWER ROUTES
    // ==========================================
    Route::get('/borrower/dashboard', [\App\Http\Controllers\BorrowerController::class, 'dashboard'])->name('borrower.dashboard');
    Route::get('/borrower/requests', [\App\Http\Controllers\BorrowerController::class, 'requests'])->name('borrower.requests');
    Route::get('/borrower/history', [\App\Http\Controllers\BorrowerController::class, 'history'])->name('borrower.history');
    Route::get('/borrower/returns', [\App\Http\Controllers\BorrowerController::class, 'returns'])->name('borrower.returns');
    Route::get('/borrower/account', [\App\Http\Controllers\BorrowerController::class, 'account'])->name('borrower.account');

    Route::post('/borrower/returns/{id}', [\App\Http\Controllers\BorrowerController::class, 'submitReturn'])->name('borrower.returns.submit');
    
    Route::post('/borrower/request', [\App\Http\Controllers\BorrowerController::class, 'submitRequest'])->name('borrower.request.submit');
    Route::post('/borrower/request/{id}/cancel', [\App\Http\Controllers\BorrowerController::class, 'cancelRequest'])->name('borrower.request.cancel');

    // ==========================================
    // NOTIFICATIONS
    // ==========================================
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',  [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ==========================================
    // PIN MANAGEMENT
    // ==========================================
    Route::post('/auth/set-pin',    [\App\Http\Controllers\AuthController::class, 'setPin'])->name('auth.set-pin');
    Route::post('/auth/verify-pin', [\App\Http\Controllers\AuthController::class, 'verifyPin'])->name('auth.verify-pin');
});


/*
|--------------------------------------------------------------------------
| OTP & Security Routes (Outside Auth Middleware)
|--------------------------------------------------------------------------
*/
Route::get('/verify-otp', function () {
    if (!session()->has('pending_verification_email')) {
        return redirect('/');
    }
    return view('auth.verify-otp');
});

Route::post('/verify-otp-process', [AuthController::class, 'verifyOtp']);

Route::get('/force-change-password', function () {
    if (!session()->has('pending_verification_email')) {
        return redirect('/');
    }
    return view('auth.force-change-password');
});

Route::post('/force-change-password-process', [AuthController::class, 'processForcePasswordChange']);


Route::get('/fix-db', function () {
    // 1. Drop the table just in case it's stuck
    \Illuminate\Support\Facades\Schema::dropIfExists('notifications');
    
    // 2. Erase it from Laravel's memory ledger
    \Illuminate\Support\Facades\DB::table('migrations')
        ->where('migration', 'like', '%create_notifications_table%')
        ->delete();
        
    return "Memory cleared! Now go to your terminal and run: php artisan migrate";
});