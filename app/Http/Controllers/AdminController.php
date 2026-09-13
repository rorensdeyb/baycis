<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\BorrowRequest;
use App\Models\User;
use App\Models\ConsumableStock;
use App\Models\ConsumableIssuance;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Fetch metrics using the exact new models
        $pendingReqs = BorrowRequest::where('status', 'pending')->count();
        $pendingRets = BorrowRequest::where('status', 'return_pending')->count();
        $activeBorrows = BorrowRequest::whereIn('status', ['approved', 'active'])->count();
        
        $totalItems = Cache::remember('dashboard.total_items', 300, fn() => Item::count());
        $availableItems = Cache::remember('dashboard.available_items', 300, fn() => Item::where('status', 'available')->count());
        $totalUsers = Cache::remember('dashboard.total_users', 300, fn() => User::where('role', 'borrower')->count());

        // Fetch recent activity
        $recentActivity = BorrowRequest::with(['user', 'item'])->latest()->take(5)->get();

        // ── Trend comparison (compare to last week's counts) ──
        $lastWeekPendingReqs = BorrowRequest::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))->count();
        $lastWeekActiveBorrows = BorrowRequest::whereIn('status', ['approved', 'active'])
            ->where('created_at', '<', now()->subDays(7))->count();

        // ── Overdue returns (active borrows older than 7 days) ──
        $overdueReturns = BorrowRequest::whereIn('status', ['approved', 'active'])
            ->where('created_at', '<', now()->subDays(7))->count();

        // ── Low stock consumables ──
        $lowStockCount = 0;
        try {
            $lowStockCount = ConsumableStock::whereColumn('stock_quantity', '<=', 'reorder_level')
                ->where('stock_quantity', '>', 0)->count();
        } catch (\Throwable $e) {}

        // ── Top borrowers this month ──
        $topBorrowers = BorrowRequest::selectRaw('user_id, COUNT(*) as request_count')
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('user_id')
            ->orderBy('request_count', 'desc')
            ->take(5)
            ->with('user')
            ->get()
            ->map(function ($item) {
                $item->user_name = $item->user?->name ?? 'Unknown';
                return $item;
            });

        // ── Extended Operational Analytics ──
        $monthlyBorrows = BorrowRequest::where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonthBorrows = BorrowRequest::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();
        $borrowGrowthPct = $lastMonthBorrows > 0 
            ? round((($monthlyBorrows - $lastMonthBorrows) / $lastMonthBorrows) * 100) 
            : 0;

        $monthlySupplies = 0;
        try {
            $monthlySupplies = (int) ConsumableIssuance::where('created_at', '>=', now()->startOfMonth())
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('quantity');
        } catch (\Throwable $e) {}

        // Return compliance rate (% of returned items that were on time within standard 7-day loan)
        $returnComplianceRate = 100;
        try {
            $returnedItems = BorrowRequest::where('status', 'returned')
                ->select(['id', 'created_at', 'updated_at'])
                ->get();
            if ($returnedItems->count() > 0) {
                $onTimeCount = $returnedItems->filter(function ($item) {
                    if (!$item->created_at || !$item->updated_at) return true;
                    return $item->created_at->diffInDays($item->updated_at) <= 7;
                })->count();
                $returnComplianceRate = round(($onTimeCount / $returnedItems->count()) * 100);
            }
        } catch (\Throwable $e) {
            $returnComplianceRate = 100;
        }

        // Unique active borrowers this month
        $activeBorrowersCount = BorrowRequest::where('created_at', '>=', now()->startOfMonth())
            ->distinct('user_id')
            ->count('user_id');

        // Top categories breakdown
        $topCategories = Cache::remember('dashboard.top_categories', 180, function () use ($totalItems) {
            return Item::selectRaw('category_id, COUNT(*) as count')
                ->whereNotNull('category_id')
                ->groupBy('category_id')
                ->orderByDesc('count')
                ->take(5)
                ->with('category')
                ->get()
                ->map(fn($c) => [
                    'name'  => $c->category?->name ?? 'Uncategorized',
                    'count' => (int) $c->count,
                    'pct'   => $totalItems > 0 ? round(($c->count / $totalItems) * 100) : 0,
                ])
                ->toArray();
        });

        // Send exact variable names to the view
        return view('admin.dashboard', compact(
            'pendingReqs', 'pendingRets', 'activeBorrows', 
            'totalItems', 'availableItems', 'totalUsers', 'recentActivity',
            'lastWeekPendingReqs', 'lastWeekActiveBorrows',
            'overdueReturns', 'lowStockCount', 'topBorrowers',
            'monthlyBorrows', 'borrowGrowthPct', 'monthlySupplies',
            'returnComplianceRate', 'activeBorrowersCount', 'topCategories'
        ));
    }

    /**
     * AJAX endpoint: returns JSON chart data for dashboard widgets.
     */
    public function chartData()
    {
        // Cache for 60 seconds to reduce DB load
        $data = Cache::remember('dashboard.chart.' . Auth::id(), 60, function () {
            // Status breakdown for donut
            $statusBreakdown = Item::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $totalItems = array_sum($statusBreakdown->toArray()) ?: 1;
            $availableVal = $statusBreakdown->get('available', 0);
            $availPct = round(($availableVal / $totalItems) * 100);

            // 7-day continuous borrow trend
            $dailyBorrows = [];
            for ($i = 6; $i >= 0; $i--) {
                $dayCarbon = now()->subDays($i);
                $dailyBorrows[$dayCarbon->format('Y-m-d')] = [
                    'day'   => $dayCarbon->format('D'),
                    'date'  => $dayCarbon->format('M d'),
                    'count' => 0,
                ];
            }
            $rawBorrows = BorrowRequest::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy('date')
                ->pluck('count', 'date');

            foreach ($rawBorrows as $d => $c) {
                if (isset($dailyBorrows[$d])) {
                    $dailyBorrows[$d]['count'] = (int) $c;
                }
            }

            // Category breakdown
            $categoryBreakdown = Item::selectRaw('category_id, COUNT(*) as count')
                ->whereNotNull('category_id')
                ->groupBy('category_id')
                ->orderByDesc('count')
                ->take(5)
                ->with('category')
                ->get()
                ->map(function ($item) use ($totalItems) {
                    return [
                        'label' => $item->category?->name ?? 'Uncategorized',
                        'count' => (int) $item->count,
                        'pct'   => $totalItems > 0 ? round(($item->count / $totalItems) * 100) : 0,
                    ];
                });

            return [
                'status_breakdown' => [
                    'available'   => (int) $statusBreakdown->get('available', 0),
                    'borrowed'    => (int) $statusBreakdown->get('borrowed', 0),
                    'damaged'     => (int) $statusBreakdown->get('damaged', 0),
                    'maintenance' => (int) $statusBreakdown->get('maintenance', 0),
                ],
                'availability_pct' => $availPct,
                'daily_borrows'    => array_values($dailyBorrows),
                'categories'       => $categoryBreakdown,
            ];
        });

        return response()->json($data);
    }

    /**
     * Compile and compile a standard database SQL backup file.
     */
    public function downloadBackup()
    {
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(), 'action' => 'Backup', 'table_name' => 'system', 'record_id' => 0,
            'description' => 'System database backup downloaded'
        ]);
        // 1. Fetch all structural tables inside the active schema connection
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'inventory_management_system');
        
        $sqlDump = "-- BayCIS Inventory Management System Database Dump\n";
        $sqlDump .= "-- Generated: " . now()->format('F d, Y h:i:s A') . "\n";
        $sqlDump .= "-- Environment: Local Backup Workflow\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Uses current() to isolate the table name string array value regardless of key properties
            $tableName = current((array)$table);
            
            // 2. Generate Structural Table Schema Build DDL
            $createTableQuery = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
            $sqlDump .= "DROP TABLE IF EXISTS `$tableName`;\n";
            $sqlDump .= $createTableQuery . ";\n\n";
            
            // 3. Extract and Serialize Data Rows into standard INSERT blocks
            $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
            foreach ($rows as $row) {
                $rowArray = (array)$row;
                $columns = array_keys($rowArray);
                
                $escapedValues = array_map(function($value) {
                    if (is_null($value)) return 'NULL';
                    return "'" . addslashes($value) . "'";
                }, array_values($rowArray));
                
                $sqlDump .= "INSERT INTO `$tableName` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sqlDump .= "\n\n";
        }
        
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // 4. Construct a unique file attachment wrapper name token
        $fileName = 'baycis_backup_' . now()->format('Ymd_His') . '.sql';
        
        return response($sqlDump)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Overwrite current records by uploading a valid SQL backup scheme file.
     * Protected by secondary cryptographic password verification and optimized with a streaming buffer.
     */
    public function restoreBackup(\Illuminate\Http\Request $request)
    {
        // 1. Enforce strict input requirements for both payload segments
        $request->validate([
            'backup_file' => 'required|file',
            'password'    => 'required|string'
        ]);

        // 2. High-Priority Security Gate: Verify active administrator credentials
        if (!\Illuminate\Support\Facades\Hash::check($request->password, \Illuminate\Support\Facades\Auth::user()->password)) {
            return redirect()->back()->with('error', 'Authentication failed: Invalid administrator password. System recovery aborted.');
        }

        // ==========================================================================
        // SENIOR DEVELOPER RESOURCE PROTECTION GUARDS
        // ==========================================================================
        set_time_limit(0);          // Removes the 30-second PHP execution timeout limit entirely
        ini_set('memory_limit', '512M'); // Temporarily expands allowable server RAM usage

        try {
            $file = $request->file('backup_file');
            
            // Read incoming SQL text file content stream
            $sqlContent = file_get_contents($file->getRealPath());

            // Establish raw PDO instance to manage unbuffered processing safely
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            
            // Enable preparation emulation to support raw multi-query structures safely
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            
            // Isolate individual operation queries by disabling foreign keys temporarily
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Split the file line-by-line into an iterable array stream
            $lines = explode("\n", $sqlContent);
            $statementBuffer = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip empty lines, SQL header info, or text-block comments
                if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                    continue;
                }
                
                // Append current line to the statement buffer
                $statementBuffer .= $line . "\n";
                
                // Check if we reached the final terminal boundary of a single SQL statement (Semicolon)
                if (substr($line, -1) === ';') {
                    // Execute the clean isolated query statement via raw PDO
                    $pdo->exec($statementBuffer);
                    
                    // Reset the buffer container for the next database transaction
                    $statementBuffer = ''; 
                }
            }

            // Restore data integrity requirements
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            return redirect()->back()->with('success', 'Database successfully restored! All data has been rolled back to the backup state.');
        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled even if an execution step fails mid-stream
            try {
                \Illuminate\Support\Facades\DB::connection()->getPdo()->exec("SET FOREIGN_KEY_CHECKS=1;");
            } catch (\Exception $ignored) {}

            return redirect()->back()->with('error', 'System recovery routine failed: ' . $e->getMessage());
        }
    }
}