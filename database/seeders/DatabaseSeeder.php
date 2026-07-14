<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Categories (Mapping from your DepEd Image)
        $categories = [
            ['name' => 'Machinery', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '01'],
            ['name' => 'Office Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '02'],
            ['name' => 'ICT Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '03'],
            ['name' => 'Communication Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '07'],
            ['name' => 'Furnitures & Fixtures', 'ppe_sub_major' => '07', 'gl_ledger_acct' => '01'],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 2. Seed Locations
        \App\Models\Location::updateOrCreate(['name' => 'Main Office'], ['code' => '01']);
        \App\Models\Location::updateOrCreate(['name' => 'ICT Lab'], ['code' => '02']);

        // 3. Seed Suppliers
        \App\Models\Supplier::updateOrCreate(['name' => 'CO/RO']);
        \App\Models\Supplier::updateOrCreate(['name' => 'MOOE']);
        \App\Models\Supplier::updateOrCreate(['name' => 'LGU']);
        \App\Models\Supplier::updateOrCreate(['name' => 'Donation']);

        // 4. Call your Admin Seeder
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}