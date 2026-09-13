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
        return match (Auth::user()->role) {
            'admin', 'custodian' => redirect('/admin/dashboard'),
            default              => redirect('/borrower/dashboard'),
        };
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
Route::post('/login-process', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:3,5')->name('auth.register');
Route::post('/auth/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| Protected Application Routes (Requires Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // STAFF: COMMAND CENTER (Admin + Custodian)
    // ==========================================
    Route::middleware(['role:admin,custodian'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); // <--- FIXED
        Route::get('/admin/dashboard/chart-data', [AdminController::class, 'chartData'])->name('admin.dashboard.chart');
    Route::get('/admin/issuance', [\App\Http\Controllers\IssuanceController::class, 'index'])->name('items.issuance');
    Route::post('/admin/issuance/add-stock', [\App\Http\Controllers\IssuanceController::class, 'addStock'])->name('issuance.add-stock');
    Route::put('/admin/issuance/stocks/{id}', [\App\Http\Controllers\IssuanceController::class, 'updateStock'])->name('issuance.stocks.update');
Route::delete('/admin/issuance/stocks/{id}', [\App\Http\Controllers\IssuanceController::class, 'deleteStock'])->name('issuance.stocks.destroy');
    Route::post('/admin/issuance/stocks/{id}/replenish', [\App\Http\Controllers\IssuanceController::class, 'replenish'])->name('issuance.replenish');
    Route::post('/admin/issuance/bulk-issue', [\App\Http\Controllers\IssuanceController::class, 'bulkIssue'])->name('issuance.bulk-issue');
    Route::get('/admin/issuance/search', [\App\Http\Controllers\IssuanceController::class, 'searchStocks'])->name('issuance.search');
    Route::get('/admin/issuance/history/export', [\App\Http\Controllers\IssuanceController::class, 'exportHistory'])->name('issuance.history-export');
    Route::post('/admin/issuance/stocks/import', [\App\Http\Controllers\IssuanceController::class, 'importStocks'])->name('issuance.stocks.import');
    Route::post('/admin/issuance/initiate', [\App\Http\Controllers\IssuanceController::class, 'adminInitiateIssue'])->name('issuance.initiate');
    Route::post('/admin/issuance/fulfill/{id}', [\App\Http\Controllers\IssuanceController::class, 'adminFulfillRequest'])->name('issuance.fulfill');
    Route::post('/admin/issuance/cancel/{id}', [\App\Http\Controllers\IssuanceController::class, 'adminCancelIssuance'])->name('issuance.cancel');
    Route::get('/admin/issuance/stock-history/{id}', [\App\Http\Controllers\IssuanceController::class, 'stockHistory'])->name('issuance.stock-history');

    // BATCH & ARCHIVE ROUTES (Must be above {id} routes)
    // Legacy batch print removed — superseded by Print Studio (/admin/print-studio).
    Route::get('/admin/print-studio/search', [\App\Http\Controllers\PrintStudioController::class, 'search'])->name('items.print-studio.search');
    Route::post('/admin/print-studio/resolve', [\App\Http\Controllers\PrintStudioController::class, 'resolveTags'])->name('items.print-studio.resolve');
    Route::get('/admin/print-studio', [\App\Http\Controllers\PrintStudioController::class, 'index'])->name('items.print-studio');
    Route::get('/admin/archive', [ItemController::class, 'archive'])->name('items.archive');
    Route::get('/admin/archive/stats', [ItemController::class, 'archiveStats'])->name('items.archive-stats');
        Route::post('/admin/inventory/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
        Route::post('/admin/inventory/bulk-restore', [ItemController::class, 'bulkRestore'])->name('items.bulk-restore');
        Route::post('/admin/inventory/bulk-force', [ItemController::class, 'bulkForceDelete'])->name('items.bulk-force');

        // ==========================================
        // STAFF: INVENTORY (Admin + Custodian)
        // ==========================================
        Route::get('/admin/inventory', [ItemController::class, 'index'])->name('items.index');
        Route::get('/admin/inventory/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/admin/inventory', [ItemController::class, 'store'])->name('items.store');
        Route::get('/admin/inventory/export', [ItemController::class, 'export'])->name('items.export');
        Route::get('/admin/inventory/stats', [ItemController::class, 'stats'])->name('items.stats');
        Route::post('/admin/inventory/bulk-status', [ItemController::class, 'bulkStatus'])->name('items.bulk-status');
        // JSON payload for the Inventory row drawer
        Route::get('/admin/inventory/{id}/details', [ItemController::class, 'details'])->name('items.details');

        Route::get('/admin/inventory/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/admin/inventory/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/admin/inventory/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::get('/admin/inventory/{id}/print-tag', [ItemController::class, 'printTag'])->name('items.print-tag');
        Route::get('/admin/inventory/{id}/duplicate', [ItemController::class, 'duplicate'])->name('items.duplicate');

        // ==========================================
        // STAFF: TRANSACTIONS & SYSTEM
        // ==========================================
        Route::get('/admin/history', [ItemController::class, 'transactionHistory'])->name('items.history');

        // ==========================================
        // STAFF: BORROW REQUESTS & RETURNS
        // ==========================================
        Route::get('/admin/requests', [\App\Http\Controllers\TransactionController::class, 'manageRequests'])->name('admin.requests');
        Route::post('/admin/requests/{id}/approve', [\App\Http\Controllers\TransactionController::class, 'approve'])->name('admin.requests.approve');
        Route::post('/admin/requests/{id}/reject', [\App\Http\Controllers\TransactionController::class, 'reject'])->name('admin.requests.reject');
        Route::post('/admin/requests/{id}/return', [\App\Http\Controllers\TransactionController::class, 'markAsReturned'])->name('admin.requests.return');
        Route::post('/admin/requests/initiate', [\App\Http\Controllers\TransactionController::class, 'adminInitiateRequest'])->name('admin.requests.initiate');
        Route::post('/admin/requests/{id}/cancel', [\App\Http\Controllers\TransactionController::class, 'cancelInitiatedBorrow'])->name('admin.requests.cancel');
        Route::get('/api/admin/borrowers', [\App\Http\Controllers\AdminUserController::class, 'borrowers'])->name('api.admin.borrowers');
        Route::get('/api/admin/available-items', [\App\Http\Controllers\ItemController::class, 'availableItems'])->name('api.admin.available-items');

        Route::get('/admin/returns', [\App\Http\Controllers\TransactionController::class, 'manageReturns'])->name('admin.returns');

        Route::get('/admin/reports', [ItemController::class, 'reports'])->name('admin.reports'); // <--- FIXED

        // Account Settings — self-service profile/password/PIN for all staff roles
        Route::get('/admin/account-settings', [App\Http\Controllers\SettingsController::class, 'accountPage'])->name('admin.account-settings');
        Route::put('/admin/settings/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('admin.settings.profile');
        Route::put('/admin/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('admin.settings.password');
        Route::put('/admin/settings/pin', [App\Http\Controllers\SettingsController::class, 'updatePin'])->name('admin.settings.pin');
    });

    // ==========================================
    // ADMIN ONLY: FORCE DELETE (permanent)
    // ==========================================
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('/admin/inventory/{id}/force', [ItemController::class, 'forceDelete'])->name('items.force-delete');
    });

    // ==========================================
    // ADMIN ONLY: USER MANAGEMENT & SYSTEM CONFIG
    // ==========================================
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
        // 🔑 Clear a user's security PIN (forgotten PIN) — requires admin's own PIN
        Route::post('/admin/users/{id}/reset-pin', [AdminUserController::class, 'resetPin'])->name('admin.users.reset-pin');
        Route::post('/admin/users/bulk-update', [AdminUserController::class, 'bulkUpdate'])->name('admin.users.bulk-update');

        // Account Lifecycle: approve/reject user-initiated activation/deactivation/deletion requests
        // NOTE: Direct account deletion was removed — deletion requires a user request + approval.
        Route::post('/admin/users/{id}/lifecycle-action', [AdminUserController::class, 'handleLifecycleAction'])->name('admin.users.lifecycle-action');


        // ==========================================
        // ADMIN: SYSTEM & SETTINGS (Corrected)
        // ==========================================
        Route::get('/admin/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings/general', [\App\Http\Controllers\SettingsController::class, 'updateGeneral'])->name('admin.settings.general');
        Route::post('/admin/settings/inventory', [\App\Http\Controllers\SettingsController::class, 'updateInventory'])->name('admin.settings.inventory');
        Route::post('/admin/settings/appearance', [\App\Http\Controllers\SettingsController::class, 'updateAppearance'])->name('admin.settings.appearance');

        // Audit Logs
        Route::get('/admin/logs', [\App\Http\Controllers\SettingsController::class, 'auditLogs'])->name('admin.audit-logs');

        // Backup & Restore remain in AdminController
        Route::get('/admin/settings/backup/download', [AdminController::class, 'downloadBackup'])->name('admin.backup.download');
        Route::post('/admin/settings/backup/restore', [AdminController::class, 'restoreBackup'])->name('admin.backup.restore');

        // ==========================================
        // ADMIN: INVENTORY CONFIGURATION CRUD
        // ==========================================
        Route::get('/admin/settings/categories', [\App\Http\Controllers\SettingsInventoryController::class, 'categories'])->name('admin.settings.categories');
        Route::post('/admin/settings/categories', [\App\Http\Controllers\SettingsInventoryController::class, 'storeCategory'])->name('admin.settings.categories.store');
        Route::put('/admin/settings/categories/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'updateCategory'])->name('admin.settings.categories.update');
        Route::delete('/admin/settings/categories/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'destroyCategory'])->name('admin.settings.categories.destroy');

        // Asset Tags (Sub-Categories)
        Route::get('/admin/settings/inventory-reference', [\App\Http\Controllers\SettingsController::class, 'inventoryReference'])->name('admin.settings.inventory-reference');
        Route::post('/admin/settings/tags', [\App\Http\Controllers\SettingsInventoryController::class, 'storeTag'])->name('admin.settings.tags.store');
        Route::put('/admin/settings/tags/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'updateTag'])->name('admin.settings.tags.update');
        Route::delete('/admin/settings/tags/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'destroyTag'])->name('admin.settings.tags.destroy');

        Route::get('/admin/settings/suppliers', [\App\Http\Controllers\SettingsInventoryController::class, 'suppliers'])->name('admin.settings.suppliers');
        Route::post('/admin/settings/suppliers', [\App\Http\Controllers\SettingsInventoryController::class, 'storeSupplier'])->name('admin.settings.suppliers.store');
        Route::put('/admin/settings/suppliers/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'updateSupplier'])->name('admin.settings.suppliers.update');
        Route::delete('/admin/settings/suppliers/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'destroySupplier'])->name('admin.settings.suppliers.destroy');

        Route::get('/admin/settings/locations', [\App\Http\Controllers\SettingsInventoryController::class, 'locations'])->name('admin.settings.locations');
        Route::post('/admin/settings/locations', [\App\Http\Controllers\SettingsInventoryController::class, 'storeLocation'])->name('admin.settings.locations.store');
        Route::put('/admin/settings/locations/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'updateLocation'])->name('admin.settings.locations.update');
        Route::delete('/admin/settings/locations/{id}', [\App\Http\Controllers\SettingsInventoryController::class, 'destroyLocation'])->name('admin.settings.locations.destroy');
    });

    // ==========================================
    // BORROWER ROUTES
    // ==========================================
    Route::get('/borrower/dashboard', [\App\Http\Controllers\BorrowerController::class, 'dashboard'])->name('borrower.dashboard');
    Route::get('/borrower/requests', [\App\Http\Controllers\BorrowerController::class, 'requests'])->name('borrower.requests');
    Route::get('/borrower/history', [\App\Http\Controllers\BorrowerController::class, 'history'])->name('borrower.history');
    Route::get('/borrower/returns', [\App\Http\Controllers\BorrowerController::class, 'returns'])->name('borrower.returns');
    Route::get('/borrower/account', [\App\Http\Controllers\BorrowerController::class, 'account'])->name('borrower.account');
    Route::get('/borrower/issuance', [\App\Http\Controllers\BorrowerController::class, 'issuance'])->name('borrower.issuance');

    Route::post('/borrower/returns/{id}', [\App\Http\Controllers\BorrowerController::class, 'submitReturn'])->name('borrower.returns.submit');
    
    Route::post('/borrower/request', [\App\Http\Controllers\BorrowerController::class, 'submitRequest'])->name('borrower.request.submit');
    Route::post('/borrower/request/{id}/cancel', [\App\Http\Controllers\BorrowerController::class, 'cancelRequest'])->name('borrower.request.cancel');

    Route::post('/borrower/issuance/request', [\App\Http\Controllers\BorrowerController::class, 'requestConsumable'])->name('borrower.issuance.request');
    Route::post('/borrower/issuance/confirm/{id}', [\App\Http\Controllers\BorrowerController::class, 'confirmReceipt'])->name('borrower.issuance.confirm');
    Route::post('/borrower/issuance/cancel/{id}', [\App\Http\Controllers\BorrowerController::class, 'cancelConsumableRequest'])->name('borrower.issuance.cancel');

    // Borrower Account Settings
    Route::put('/borrower/account/profile', [App\Http\Controllers\BorrowerController::class, 'updateProfile'])->name('borrower.account.profile');
    Route::put('/borrower/account/password', [App\Http\Controllers\BorrowerController::class, 'updatePassword'])->name('borrower.account.password');
    Route::put('/borrower/account/pin', [App\Http\Controllers\BorrowerController::class, 'updatePin'])->name('borrower.account.pin');

    // ==========================================
    // ACCOUNT LIFECYCLE (any authenticated user)
    // User-initiated deactivation / deletion requests
    // ==========================================
    Route::post('/account/request-deactivation', [\App\Http\Controllers\AccountLifecycleController::class, 'requestDeactivation'])->name('account.request-deactivation');
    Route::post('/account/request-deletion', [\App\Http\Controllers\AccountLifecycleController::class, 'requestDeletion'])->name('account.request-deletion');
    Route::post('/account/cancel-request', [\App\Http\Controllers\AccountLifecycleController::class, 'cancelRequest'])->name('account.cancel-request');

    // ==========================================
    // NOTIFICATIONS
    // ==========================================
    Route::match(['get', 'post'], '/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/{id}/toggle-read', [\App\Http\Controllers\NotificationController::class, 'toggleRead'])->name('notifications.toggle-read');
    Route::delete('/notifications/{id}',          [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/read-all',         [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/clear-all',        [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/poll',              [\App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');

    // ==========================================
    // PIN MANAGEMENT
    // ==========================================
    Route::post('/auth/set-pin',    [\App\Http\Controllers\AuthController::class, 'setPin'])->middleware('throttle:5,1')->name('auth.set-pin');
    Route::post('/auth/verify-pin', [\App\Http\Controllers\AuthController::class, 'verifyPin'])->middleware('throttle:5,1')->name('auth.verify-pin');

    // ==========================================
    // PIN RESET VIA EMAIL OTP (Logged-in users)
    // ==========================================
    Route::post('/auth/send-pin-reset-otp', [\App\Http\Controllers\AuthController::class, 'sendPinResetOtp'])->middleware('throttle:3,5')->name('auth.send-pin-reset-otp');
    Route::post('/auth/verify-pin-reset-otp', [\App\Http\Controllers\AuthController::class, 'verifyPinResetOtp'])->middleware('throttle:5,1')->name('auth.verify-pin-reset-otp');
});

// ==========================================
// PIN RESET VIA EMAIL OTP (Guests - forgot PIN from login page)
// ==========================================
Route::post('/auth/forgot-pin-send-otp', [\App\Http\Controllers\AuthController::class, 'forgotPinSendOtp'])->middleware('throttle:3,5')->name('auth.forgot-pin-send-otp');
Route::post('/auth/forgot-pin-reset', [\App\Http\Controllers\AuthController::class, 'forgotPinReset'])->middleware('throttle:5,1')->name('auth.forgot-pin-reset');


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

Route::post('/verify-otp-process', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/verify-otp-resend', [AuthController::class, 'resendOtp'])->middleware('throttle:3,10');

Route::get('/force-change-password', function () {
    if (!session()->has('pending_verification_email')) {
        return redirect('/');
    }
    return view('auth.force-change-password');
});

Route::post('/force-change-password-process', [AuthController::class, 'processForcePasswordChange']);


if (app()->environment('local')) {
    Route::get('/fix-db', function () {
        \Illuminate\Support\Facades\Schema::dropIfExists('notifications');
        \Illuminate\Support\Facades\DB::table('migrations')
            ->where('migration', 'like', '%create_notifications_table%')
            ->delete();
        return "Memory cleared! Now go to your terminal and run: php artisan migrate";
    });
}