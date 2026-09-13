<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account (matched by unique email so re-seeding never duplicates)
        $this->upsert(
            ['email' => env('INITIAL_ADMIN_EMAIL', 'admin@example.com')],
            [
                'teacher_id' => '0000-0000',
                'name' => 'Admin: JJ',
                'role' => 'admin',
                'pin_setup_completed' => false,
                'is_active' => true,
                'password' => bcrypt(env('INITIAL_ADMIN_PASSWORD', 'default_secure_password')),
                'requires_password_change' => false,
            ]
        );

        // Test borrower account
        $this->upsert(
            ['email' => 'borrower@bces.edu.ph'],
            [
                'teacher_id' => '0000-0001',
                'name' => 'Borrower: JJ',
                'role' => 'borrower',
                'pin_setup_completed' => false,
                'is_active' => true,
                'password' => bcrypt('Borrower@123'),
                'requires_password_change' => false,
            ]
        );

        // RBAC-8: Demo Property Custodian account
        $this->upsert(
            ['email' => 'custodian@bces.edu.ph'],
            [
                'teacher_id' => '0000-0002',
                'name' => 'Custodian: JJ',
                'role' => 'custodian',
                'pin_setup_completed' => false,
                'is_active' => true,
                'password' => bcrypt('Custodian@123'),
                'requires_password_change' => false,
            ]
        );
    }

    /**
     * Restore a soft-deleted row matching the unique key (if any),
     * then create/update. Soft-deleted rows are invisible to updateOrCreate
     * but still occupy the unique index.
     */
    private function upsert(array $uniqueBy, array $values): void
    {
        User::withTrashed()
            ->where($uniqueBy)
            ->get()
            ->each(fn (User $u) => $u->restore());

        User::updateOrCreate($uniqueBy, $values);
    }
}
