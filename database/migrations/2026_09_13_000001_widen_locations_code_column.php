<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The locations.code column was created as VARCHAR(2), but the
     * Add Department form allows up to 50 chars (placeholder: "SCI-DEPT").
     * On Postgres this throws SQLSTATE 22001 (string data, right truncated)
     * and the save fails with a 500. Widen to VARCHAR(50) to match validation.
     * The code is display-only (never embedded in property numbers), so
     * longer values are safe everywhere.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "locations" ALTER COLUMN "code" TYPE VARCHAR(50)');
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE locations MODIFY COLUMN code VARCHAR(50) NULL');
            return;
        }

        // SQLite does not enforce VARCHAR lengths — no-op for local fallback.
    }

    public function down(): void
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::table('locations')->whereRaw('LENGTH(code) > 2')->update(['code' => DB::raw('LEFT(code, 2)')]);
                DB::statement('ALTER TABLE "locations" ALTER COLUMN "code" TYPE VARCHAR(2)');
            } elseif (DB::getDriverName() === 'mysql') {
                DB::statement("UPDATE locations SET code = LEFT(code, 2) WHERE CHAR_LENGTH(code) > 2");
                DB::statement('ALTER TABLE locations MODIFY COLUMN code VARCHAR(2) NULL');
            }
        } catch (\Throwable $e) {
            // Best-effort rollback only — safe to ignore.
        }
    }
};
