<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    private $path;

    public function __construct()
    {
        $this->path = storage_path('app/settings.json');
    }

    private function defaultSettings(): array
    {
        return [
            'system_name' => 'BayCIS Inventory Management System',
            'org_name' => 'Bay Central Elementary School',
            'timezone' => 'Asia/Manila',
            'date_format' => 'Y-m-d',
            'auto_tags' => 'on',
            'low_stock_threshold' => 5,
            'density' => 'densityCozy',
        ];
    }

    private function loadSettings(): array
    {
        $settings = $this->defaultSettings();

        if (file_exists($this->path)) {
            $savedSettings = json_decode(file_get_contents($this->path), true) ?? [];
            $settings = array_merge($settings, $savedSettings);
        }

        return $settings;
    }

    public function index()
    {
        $settings = $this->loadSettings();
        // Fresh queries — no cache. The settings page is admin-only and low-traffic,
        // so caching withCount just causes stale counts when items are added/removed.
        $categories = Category::withCount('items')->orderBy('name')->get();
        $suppliers = Supplier::withCount('items')->orderBy('name')->get();
        $departments = Location::withCount('items')->orderBy('name')->get();

        // Sub-category tags grouped per category for the Categories tab
        $tags = \App\Models\AssetTag::with('category:id,name')->orderBy('name')->get()
            ->groupBy('category_id');

        return view('admin.settings', compact('settings', 'categories', 'suppliers', 'departments', 'tags'));
    }

    /**
     * Inventory Reference — dedicated page for manually managing
     * Sub-Categories ("Tags") on every Asset Category.
     */
    public function inventoryReference()
    {
        $categories = Category::withCount('items')->orderBy('name')->get();
        $categories->load(['tags' => fn ($q) => $q->orderBy('name')]);

        return view('admin.settings-inventory-reference', compact('categories'));
    }

    /**
     * Show the dedicated Account Settings page (separate from System Settings).
     */
    public function accountPage()
    {
        return view('admin.account-settings');
    }

    public function updateGeneral(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['system_name'] = $request->input('system_name');
        $settings['org_name'] = $request->input('org_name');
        $settings['timezone'] = $request->input('timezone');
        $settings['date_format'] = $request->input('date_format');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(), 
            'action' => 'Settings', 
            'table_name' => 'system', 
            'record_id' => 0,
            'description' => 'Updated System General Settings'
        ]);
        
        return redirect()->back()->with('success', 'General settings updated!');
    }

    public function updateInventory(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['auto_tags'] = $request->has('auto_tags') ? 'on' : 'off';
        $settings['low_stock_threshold'] = $request->input('low_stock_threshold');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Inventory rules updated!');
    }

    public function updateAppearance(Request $request)
    {
        $settings = $this->loadSettings();
        $settings['density'] = $request->input('density', 'densityCozy');
        file_put_contents($this->path, json_encode($settings, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Appearance updated!');
    }

    // ==========================================
    // AUDIT LOGS VIEWER
    // ==========================================
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Search by action, description, or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('table_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by action type
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        // Filter by date range (with basic date validation)
        if ($request->filled('from') && strtotime($request->from)) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to') && strtotime($request->to)) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Get unique action types for the filter dropdown
        $actionTypes = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $perPage = min(100, max(10, (int)($request->per_page ?? 25)));
        $logs = $query->paginate($perPage)->withQueryString();

        return view('admin.audit-logs', compact('logs', 'actionTypes'));
    }

    // ==========================================
    // ACTION: UPDATE ADMIN PROFILE INFO
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

        return redirect()->back()->with('success', 'Admin profile updated successfully.');
    }

    // ==========================================
    // ACTION: UPDATE ADMIN PASSWORD
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

        return redirect()->back()->with('success', 'Admin password successfully changed.');
    }

    // ==========================================
    // ACTION: UPDATE ADMIN PIN
    // ==========================================
    public function updatePin(Request $request)
    {
        $request->validateWithBag('pinUpdate', [
            'current_pin' => 'required|digits:4',
            'new_pin' => 'required|digits:4|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_pin, $user->pin)) {
            return back()->withErrors(['current_pin' => 'The provided PIN does not match your current PIN.'], 'pinUpdate');
        }

        $user->pin = Hash::make($request->new_pin);
        $user->pin_setup_completed = true; 
        $user->save();

        return redirect()->back()->with('success', 'Admin Security PIN successfully updated.');
    }
}