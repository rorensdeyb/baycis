<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\BorrowRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * Display a paginated, searchable, filterable list of users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::latest();

            // UM-3: Search by name, email, or teacher_id
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereLike('name', "%{$search}%")
                      ->orWhereLike('email', "%{$search}%")
                      ->orWhereLike('teacher_id', "%{$search}%");
                });
            }

            // UM-3: Filter by role
            if ($request->filled('role') && $request->role !== 'all') {
                $query->where('role', $request->role);
            }

            // UM-3: Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                match ($request->status) {
                    'active'                 => $query->where('is_active', true),
                    'pending_otp'            => $query->whereNull('email_verified_at'),
                    'inactive'               => $query->where('is_active', false),
                    // Account Lifecycle filters
                    'pending_approval'       => $query->where('registration_source', 'self_registered')
                                                ->where('is_active', false)
                                                ->whereNotNull('email_verified_at'),
                    'deactivation_requested' => $query->whereNotNull('deactivation_requested_at'),
                    'deletion_requested'     => $query->whereNotNull('deletion_requested_at')
                                                ->whereNull('deletion_effective_at'),
                    'deletion_scheduled'     => $query->whereNotNull('deletion_effective_at'),
                    default                  => null,
                };
            }

            // UM-8: Dynamic per-page
            $perPage = min(100, max(5, (int)($request->per_page ?? 10)));
            $users = $query->paginate($perPage)->withQueryString();

            // Account Lifecycle request counters (for the Account Requests panel)
            $lifecycleCounts = [
                'activation'   => User::where('registration_source', 'self_registered')
                                    ->where('is_active', false)
                                    ->whereNotNull('email_verified_at')->count(),
                'deactivation' => User::whereNotNull('deactivation_requested_at')->count(),
                'deletion'     => User::whereNotNull('deletion_requested_at')
                                    ->whereNull('deletion_effective_at')->count(),
                'scheduled'    => User::whereNotNull('deletion_effective_at')
                                    ->whereNull('deletion_purged_at')->count(),
            ];
            $lifecycleCounts['total'] = array_sum($lifecycleCounts);

            // AJAX (live search / filtering): return only the re-rendered rows
            // so the page never has to reload while typing.
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'rows'   => view('admin.users._rows', ['users' => $users])->render(),
                    'total'  => $users->total(),
                    'first'  => $users->firstItem(),
                    'last'   => $users->lastItem(),
                    'page'   => $users->currentPage(),
                    'pages'  => $users->lastPage(),
                    'has_pages' => $users->hasPages(),
                    'lifecycleCounts' => $lifecycleCounts,
                ]);
            }

            return view('admin.users', compact('users', 'lifecycleCounts'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'teacher_id' => 'required|string|max:50|unique:users',
            'role' => 'required|in:admin,custodian,borrower',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'teacher_id' => $request->teacher_id,
            'role' => $request->role,
            'password' => Hash::make('BayCIS2026!'),
            'is_active' => false,
            'requires_password_change' => true,
            'login_count' => 0,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'User Created',
            'table_name' => 'users',
            'record_id' => $user->id,
            'description' => "Created user: {$user->name} ({$user->email}) as {$user->role}"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully! They can now log in to verify their email.'
        ], 201);
    }

    /**
     * UM-1: Return user data as JSON for the edit modal.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'teacher_id' => $user->teacher_id,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'pin_setup_completed' => $user->pin_setup_completed,
            'email_verified_at' => $user->email_verified_at,
        ]);
    }

    /**
     * UM-1: Update user data. Requires PIN verification when promoting to admin.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $adminUser = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'teacher_id' => 'required|string|max:50|unique:users,teacher_id,' . $id,
            'role' => 'required|in:admin,custodian,borrower',
            'is_active' => 'boolean',
            'promotion_pin' => 'nullable|string|digits:4',
        ]);

        $oldRole = $user->role;
        $newRole = $request->role;
        $isPromotion = ($oldRole !== 'admin' && $newRole === 'admin');
        $isDemotion  = ($oldRole === 'admin' && $newRole !== 'admin');
        $targetRoleLabel = match ($newRole) {
            'admin'      => 'Admin',
            'custodian'  => 'Property Custodian',
            default      => 'Borrower',
        };

            // 🔒 SECURITY: PIN verification required for role changes (promotion OR demotion)
            if ($isPromotion || $isDemotion) {
                $actionLabel = $isPromotion ? 'promoting' : 'demoting';
                $actionLabelPast = $isPromotion ? 'Promoted' : 'Demoted';
                $targetRole = $targetRoleLabel;
                $auditAction = $isPromotion ? 'Failed Admin Promotion PIN' : 'Failed Admin Demotion PIN';
                $auditSuccessAction = $isPromotion ? 'Admin Promotion PIN Verified' : 'Admin Demotion PIN Verified';

                $privilegePin = $request->input('promotion_pin');
                if (!$request->filled('promotion_pin')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "PIN verification required for {$actionLabel} this user. Please provide your PIN.",
                        'require_pin' => true
                    ], 422);
                }

                // Check if the admin has a PIN set up first
                if (!$adminUser->pin) {
                    AuditLog::create([
                        'user_id' => $adminUser->id,
                        'action' => $auditAction,
                        'table_name' => 'users',
                        'record_id' => $user->id,
                        'description' => "{$actionLabelPast} blocked: {$adminUser->name} has not set up their PIN"
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You must set up your PIN in Account Settings before changing user privileges.',
                        'require_pin' => true
                    ], 422);
                }

                if (!Hash::check($privilegePin, $adminUser->pin)) {
                    AuditLog::create([
                        'user_id' => $adminUser->id,
                        'action' => $auditAction,
                        'table_name' => 'users',
                        'record_id' => $user->id,
                        'description' => "Failed PIN verification for {$actionLabel} {$user->name} to {$targetRole}"
                    ]);
                    $errLabel = $isPromotion ? 'Promotion' : 'Demotion';
                    return response()->json([
                        'status' => 'error',
                        'message' => "Incorrect PIN. {$errLabel} cancelled.",
                        'require_pin' => true
                    ], 422);
                }

                // Log successful PIN verification
                AuditLog::create([
                    'user_id' => $adminUser->id,
                    'action' => $auditSuccessAction,
                    'table_name' => 'users',
                    'record_id' => $user->id,
                    'description' => "PIN verified for {$actionLabel} {$user->name} ({$user->email}) to {$targetRole} role"
                ]);
            }

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $newIsActive = $request->boolean('is_active', $user->is_active);
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'teacher_id' => $request->teacher_id,
            'role' => $newRole,
            'is_active' => $newIsActive,
        ];

        // 🔧 When activating a user, mark email as verified if not already
        if ($newIsActive && is_null($user->email_verified_at)) {
            $updateData['email_verified_at'] = now();
        }

        $user->update($updateData);

        $changes = [];
        if ($oldData['name'] !== $user->name) $changes[] = 'name';
        if ($oldData['email'] !== $user->email) $changes[] = 'email';
        if ($oldData['role'] !== $user->role) $changes[] = 'role';
        if ($oldData['is_active'] !== $user->is_active) $changes[] = 'active status';

        if ($isPromotion) {
            $actionLabel = 'User Promoted to Admin';
            $desc = "PROMOTED {$user->name} to Admin. Changed: " . implode(', ', $changes);
            $msg = "{$user->name} has been promoted to Admin!";
        } elseif ($isDemotion) {
            $actionLabel = "User Demoted to {$targetRoleLabel}";
            $desc = "DEMOTED {$user->name} to {$targetRoleLabel}. Changed: " . implode(', ', $changes);
            $msg = "{$user->name} has been demoted to {$targetRoleLabel}.";
        } elseif ($oldRole !== $newRole) {
            $actionLabel = "User Role Changed to {$targetRoleLabel}";
            $desc = "Changed role of {$user->name} to {$targetRoleLabel}. Changed: " . implode(', ', $changes);
            $msg = "{$user->name} is now a {$targetRoleLabel}.";
        } else {
            $actionLabel = 'User Updated';
            $desc = 'Changed: ' . implode(', ', $changes);
            $msg = 'User updated successfully!';
        }

        AuditLog::create([
            'user_id' => $adminUser->id,
            'action' => $actionLabel,
            'table_name' => 'users',
            'record_id' => $user->id,
            'description' => $desc
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    /**
     * UM-6: Show detailed user info.
     */
    public function show($id)
    {
        $user = User::withCount(['transactions'])->findOrFail($id);

        // Get active borrows count
        $activeBorrows = BorrowRequest::where('user_id', $id)
            ->whereIn('status', ['approved', 'active'])
            ->count();

        // Get recent activity
        $recentActivity = BorrowRequest::where('user_id', $id)
            ->with('item')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'status' => $r->status,
                    'item_name' => $r->item?->name ?? 'Unknown',
                    'created_at' => $r->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'teacher_id' => $user->teacher_id,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'pin_setup_completed' => $user->pin_setup_completed,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'login_count' => $user->login_count,
            'created_at' => $user->created_at,

            // Account lifecycle context (full-page account drawer)
            'registration_source'         => $user->registration_source,
            'requires_password_change'    => (bool) $user->requires_password_change,
            'deactivation_requested_at'   => $user->deactivation_requested_at?->toIso8601String(),
            'deactivation_requested_human'=> $user->deactivation_requested_at?->diffForHumans(),
            'deactivation_reason'         => $user->deactivation_reason,
            'deletion_requested_at'       => $user->deletion_requested_at?->toIso8601String(),
            'deletion_requested_human'    => $user->deletion_requested_at?->diffForHumans(),
            'deletion_reason'             => $user->deletion_reason,
            'deletion_effective_at'       => $user->deletion_effective_at?->toIso8601String(),
            'deletion_effective_human'    => $user->deletion_effective_at?->format('M d, Y'),
            'is_scheduled_for_deletion'   => $user->isScheduledForDeletion(),

            'active_borrows' => $activeBorrows,
            'total_transactions' => $user->transactions_count,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * UM-2: Admin-initiated password reset.
     *
     * Two modes:
     *  - generate (default): system creates a temporary password. The account is
     *    deactivated and flagged so the user must set a new password (plus OTP
     *    re-verification) at next sign-in.
     *  - assign: the admin supplies a specific password. If
     *    require_password_change is false the account state is left untouched so
     *    the user can sign in immediately with it.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $adminUser = Auth::user();

        // Resetting your own password here would deactivate your active session's
        // account — use Account Settings instead.
        if ((int) $user->id === (int) $adminUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot reset your own password from User Management. Use Account Settings.',
            ], 403);
        }

        $mode = $request->input('mode', 'generate');

        $requireChange = $mode === 'generate'
            ? true
            : $request->boolean('require_password_change', true);

        if ($mode === 'assign') {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ], [
                'password.min' => 'The assigned password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);
            $plain = $request->input('password');
        } else {
            $plain = 'BayCIS' . rand(1000, 9999) . '!';
        }

        $previousActive = (bool) $user->is_active;

        $user->forceFill([
            'password' => $plain,
            'requires_password_change' => $requireChange,
            'is_active' => ($requireChange || !$previousActive) ? false : $user->is_active,
        ])->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Password Reset',
            'table_name' => 'users',
            'record_id' => $user->id,
            'description' => "Password reset (mode: {$mode}, requires_change: "
                . ($requireChange ? 'yes' : 'no') . ") for user: {$user->name} ({$user->email})"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $mode === 'assign'
                ? "Password assigned for {$user->name}."
                : 'Password reset successfully!',
            'mode' => $mode,
            'temp_password' => $mode === 'generate' ? $plain : null,
            'requires_password_change' => $requireChange,
            'user_name' => $user->name,
        ]);
    }

    /**
     * 🔑 Admin-initiated PIN reset (forgotten PIN).
     *
     * Clears the user's PIN entirely; they will be prompted to create a new one
     * at next sign-in. Requires the ADMIN'S OWN PIN as authorization.
     */
    public function resetPin(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $adminUser = Auth::user();

        $request->validate([
            'admin_pin' => 'required|string|digits:4',
        ]);

        if (!$adminUser->pin || !Hash::check($request->input('admin_pin'), $adminUser->pin)) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Failed User PIN Reset',
                'table_name' => 'users',
                'record_id' => $user->id,
                'description' => "Failed admin PIN verification while resetting PIN of: {$user->name} ({$user->email})"
            ]);

            return response()->json([
                'status' => 'error',
                'message' => !$adminUser->pin
                    ? 'You must set up your own PIN in Account Settings before resetting another user\u2019s PIN.'
                    : 'Incorrect PIN. PIN reset cancelled.',
                'require_pin' => true,
            ], 422);
        }

        $user->forceFill([
            'pin' => null,
            'pin_setup_completed' => false,
            'failed_pin_attempts' => 0,
            'pin_locked_until' => null,
        ])->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => 'Security PIN Reset',
            'message' => 'Your security PIN was cleared by an administrator. You will be asked to create a new PIN the next time you sign in.',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'User PIN Reset',
            'table_name' => 'users',
            'record_id' => $user->id,
            'description' => "Cleared the security PIN of: {$user->name} ({$user->email}) — user must create a new PIN at next sign-in."
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$user->name}'s PIN has been cleared. They will create a new one at their next sign-in.",
        ]);
    }

    /**
     * UM-5: Bulk operations on users. Requires PIN for set_admin.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,set_admin,set_custodian,set_borrower',
            'promotion_pin' => 'nullable|string|digits:4',
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;
        $count = count($userIds);
        $description = '';
        $adminUser = Auth::user();

        // 🔒 SECURITY: PIN verification required for bulk role changes (set_admin OR set_borrower)
        $requiresPin = ($action === 'set_admin' || $action === 'set_borrower');
        if ($requiresPin) {
            $isBulkPromotion = ($action === 'set_admin');
            $actionLabel = $isBulkPromotion ? 'promoting' : 'demoting';
            $targetRole = $isBulkPromotion ? 'Admin' : 'Borrower';
            $auditFailAction = $isBulkPromotion ? 'Failed Bulk Admin Promotion PIN' : 'Failed Bulk Admin Demotion PIN';
            $auditSuccessAction = $isBulkPromotion ? 'Bulk Admin Promotion PIN Verified' : 'Bulk Admin Demotion PIN Verified';

            $privilegePin = $request->input('promotion_pin');
            if (!$privilegePin) {
                return response()->json([
                    'status' => 'error',
                    'message' => "PIN verification required to {$actionLabel} users to {$targetRole}.",
                    'require_pin' => true
                ], 422);
            }

            if (!$adminUser->pin || !Hash::check($privilegePin, $adminUser->pin)) {
                AuditLog::create([
                    'user_id' => $adminUser->id,
                    'action' => $auditFailAction,
                    'table_name' => 'users',
                    'record_id' => 0,
                    'description' => "Failed PIN verification for bulk {$actionLabel} {$count} user(s) to {$targetRole}. IDs: [" . implode(',', $userIds) . "]"
                ]);
                $errLabel = $isBulkPromotion ? 'promotion' : 'demotion';
                return response()->json([
                    'status' => 'error',
                    'message' => "Incorrect PIN. Bulk {$errLabel} cancelled.",
                    'require_pin' => true
                ], 422);
            }

            AuditLog::create([
                'user_id' => $adminUser->id,
                'action' => $auditSuccessAction,
                'table_name' => 'users',
                'record_id' => 0,
                'description' => "PIN verified for bulk {$actionLabel} {$count} user(s) to {$targetRole} role"
            ]);
        }

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update([
                    'is_active' => true,
                    'email_verified_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(email_verified_at, NOW())'),
                ]);
                $description = "Activated {$count} user(s)";
                break;
            case 'deactivate':
                User::whereIn('id', $userIds)->update(['is_active' => false]);
                $description = "Deactivated {$count} user(s)";
                break;
            case 'set_admin':
                User::whereIn('id', $userIds)->update(['role' => 'admin']);
                $description = "Changed {$count} user(s) role to Admin";
                break;
            case 'set_custodian':
                User::whereIn('id', $userIds)->update(['role' => 'custodian']);
                $description = "Changed {$count} user(s) role to Property Custodian";
                break;
            case 'set_borrower':
                User::whereIn('id', $userIds)->update(['role' => 'borrower']);
                $description = "Changed {$count} user(s) role to Borrower";
                break;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Bulk User Update',
            'table_name' => 'users',
            'record_id' => 0,
            'description' => $description . ' — IDs: [' . implode(',', $userIds) . ']'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $description . ' successfully!',
            'count' => $count,
        ]);
    }

    /**
     * Return active borrowers as JSON for the walk-in borrow selector.
     */
    public function borrowers()
    {
        $users = User::where('role', 'borrower')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'teacher_id']);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * 🔒 Account Lifecycle: approve/reject account activation, deactivation and
     * deletion requests. Admins can NO LONGER delete accounts directly —
     * deletion requires a user request plus approval, followed by a
     * 60-day grace period before the scheduled purge.
     *
     * Actions:
     *  - approve_activation   (self-registered → active)
     *  - reject_activation    (self-registered, never used → removed)
     *  - approve_deactivation (is_active = false)
     *  - reject_deactivation
     *  - approve_deletion     (deletion_effective_at = now + 60 days)
     *  - reject_deletion
     *  - cancel_deletion      (admin mercy-cancel during the grace period)
     */
    public function handleLifecycleAction(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve_activation,reject_activation,approve_deactivation,reject_deactivation,approve_deletion,reject_deletion,cancel_deletion',
        ]);

        $user = User::withTrashed()->findOrFail($id);
        $adminUser = Auth::user();
        $action = $request->action;

        // Guard self-targeting for destructive actions
        if ($user->id === $adminUser->id && in_array($action, ['approve_deletion', 'approve_deactivation'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot approve deletion or deactivation of your own admin account.'
            ], 422);
        }

        // Never purge the last remaining administrator
        if (in_array($action, ['approve_deletion', 'approve_deactivation'], true) && $user->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdmins <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot deactivate or delete the last active administrator account.'
                ], 422);
            }
        }

        [$title, $message] = match ($action) {
            'approve_activation' => $this->approveActivation($user),
            'reject_activation'  => $this->rejectActivation($user),
            'approve_deactivation' => $this->approveDeactivation($user),
            'reject_deactivation' => $this->rejectRequest($user, 'deactivation_requested_at', 'deactivation_reason', 'Account Deactivation Request Rejected', 'Your deactivation request was reviewed and rejected by an administrator. Your account remains active.'),
            'approve_deletion'   => $this->approveDeletion($user),
            'reject_deletion'    => $this->rejectRequest($user, 'deletion_requested_at', 'deletion_reason', 'Account Deletion Request Rejected', 'Your account deletion request was reviewed and rejected by an administrator. Your account remains active.'),
            'cancel_deletion'    => $this->cancelScheduledDeletion($user),
        };

        AuditLog::create([
            'user_id' => $adminUser->id,
            'action' => $title,
            'table_name' => 'users',
            'record_id' => $user->id,
            'description' => $this->describeLifecycleAction($action, $user),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    private function describeLifecycleAction(string $action, User $user): string
    {
        $label = match ($action) {
            'approve_activation'   => 'Activated',
            'reject_activation'    => 'Rejected & removed',
            'approve_deactivation' => 'Deactivated',
            'reject_deactivation'  => 'Deactivation request rejected',
            'approve_deletion'     => 'Approved for permanent deletion on ' . ($user->deletion_effective_at?->format('M d, Y') ?? '?'),
            'reject_deletion'      => 'Deletion request rejected',
            'cancel_deletion'      => 'Scheduled deletion cancelled — account reactivated',
        };
        return "Account lifecycle [{$label}]: {$user->name} ({$user->email})";
    }

    /** @return array{0:string,1:string} [notification_title, response_message] */
    private function approveActivation(User $user): array
    {
        $user->forceFill([
            'is_active'         => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => 'Account Approved',
            'message' => 'Good news! Your account has been reviewed and activated by an administrator. You may now sign in.',
        ]);

        return ['Account Activated', "{$user->name}'s account has been approved and activated."];
    }

    /** Self-registered accounts that are rejected were never used — safe to remove entirely. */
    private function rejectActivation(User $user): array
    {
        if (!$user->isSelfRegistered() || $user->transactions()->exists()) {
            // Fall back to plain rejection (keep record) when it is not safe to remove
            $user->clearLifecycleRequests();
            $user->forceFill(['registration_source' => null])->save();
            return ['Registration Rejected', "{$user->name}'s registration was rejected. The record was kept for auditing."];
        }

        $name = $user->name;
        $user->notifications()->delete();
        $user->auditLogs()->update(['user_id' => null]);
        $user->forceDelete();

        return ['Registration Rejected', "{$name}'s registration request was rejected and the pending account was removed."];
    }

    private function approveDeactivation(User $user): array
    {
        $reason = $user->deactivation_reason;
        $user->clearLifecycleRequests();
        $user->forceFill(['is_active' => false])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => 'Account Deactivated',
            'message' => 'Your account has been deactivated upon your request. Contact an administrator if you wish to regain access.'
                . ($reason ? " Reason noted: {$reason}" : ''),
        ]);

        return ['Account Deactivated', "{$user->name}'s account has been deactivated."];
    }

    private function approveDeletion(User $user): array
    {
        $effectiveAt = now()->addDays(User::DELETION_BUFFER_DAYS);

        $user->clearLifecycleRequests();
        $user->forceFill([
            'is_active' => false,
            'deletion_effective_at' => $effectiveAt,
        ])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => 'Account Deletion Approved',
            'message' => 'Your account deletion has been approved. It will be permanently removed on '
                . $effectiveAt->format('M d, Y') . '. Sign in before that date and cancel the request to keep your account.'
                . ' All transaction records will be preserved.',
        ]);

        return [
            'Account Deletion Scheduled',
            "{$user->name}'s deletion was approved — permanent removal on {$effectiveAt->format('M d, Y')} ("
            . User::DELETION_BUFFER_DAYS . "-day grace period). Transaction history is preserved.",
        ];
    }

    private function cancelScheduledDeletion(User $user): array
    {
        $user->clearLifecycleRequests();
        $user->forceFill([
            'deletion_effective_at' => null,
            'deletion_purged_at' => null,
            'is_active' => true,
        ])->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => 'Account Restored',
            'message' => 'The scheduled deletion of your account was cancelled by an administrator. Your account is active again.',
        ]);

        return ['Scheduled Deletion Cancelled', "{$user->name}'s scheduled deletion was cancelled and the account is active again."];
    }

    private function rejectRequest(User $user, string $timestampField, string $reasonField, string $notifTitle, string $notifMessage): array
    {
        $user->clearLifecycleRequests();
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'alert',
            'title' => $notifTitle,
            'message' => $notifMessage,
        ]);

        $kind = str_contains($notifTitle, 'Deletion') ? 'deletion' : 'deactivation';
        return ['Request Rejected', "{$user->name}'s {$kind} request was rejected. The account remains active."];
    }
}
