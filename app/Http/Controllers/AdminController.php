<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\BorrowRequest;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Fetch metrics using the exact new models
        $pendingReqs = BorrowRequest::where('status', 'pending')->count();
        $pendingRets = BorrowRequest::where('status', 'return_pending')->count();
        $activeBorrows = BorrowRequest::whereIn('status', ['approved', 'active'])->count();
        
        $totalItems = Item::count();
        $availableItems = Item::where('status', 'available')->count();
        $totalUsers = User::where('role', 'borrower')->count();

        // Fetch recent activity
        $recentActivity = BorrowRequest::with(['user', 'item'])->latest()->take(5)->get();

        // Send exact variable names to the view
        return view('admin.dashboard', compact(
            'pendingReqs', 'pendingRets', 'activeBorrows', 
            'totalItems', 'availableItems', 'totalUsers', 'recentActivity'
        ));
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