<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Land
            ['name' => 'Land', 'ppe_sub_major' => '01', 'gl_ledger_acct' => '01'],

            // Infrastructure Assets (03)
            ['name' => 'Water Supply Systems', 'ppe_sub_major' => '03', 'gl_ledger_acct' => '04'],
            ['name' => 'Power Supply Systems', 'ppe_sub_major' => '03', 'gl_ledger_acct' => '05'],
            ['name' => 'Other Infrastructure Assets', 'ppe_sub_major' => '03', 'gl_ledger_acct' => '99'],

            // Buildings and Other Structures (04)
            ['name' => 'Buildings', 'ppe_sub_major' => '04', 'gl_ledger_acct' => '01'],
            ['name' => 'School Buildings', 'ppe_sub_major' => '04', 'gl_ledger_acct' => '02'],

            // Machinery and Equipment (05)
            ['name' => 'Machinery', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '01'],
            ['name' => 'Office Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '02'],
            ['name' => 'ICT Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '03'],
            ['name' => 'Communication Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '07'],
            ['name' => 'Disaster Response and Rescue Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '09'],
            ['name' => 'Medical Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '11'],
            ['name' => 'Printing Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '12'],
            ['name' => 'Sports Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '13'],
            ['name' => 'Technical and Scientific Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '14'],
            ['name' => 'Other Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '99'],

            // Transportation Equipment (06)
            ['name' => 'Motor Vehicles', 'ppe_sub_major' => '06', 'gl_ledger_acct' => '01'],
            ['name' => 'Other Transportation Equipment', 'ppe_sub_major' => '06', 'gl_ledger_acct' => '99'],

            // Furnitures, Fixtures & Books (07)
            ['name' => 'Furnitures & Fixtures', 'ppe_sub_major' => '07', 'gl_ledger_acct' => '01'],
            ['name' => 'Books', 'ppe_sub_major' => '07', 'gl_ledger_acct' => '02'],

            // Heritage Assets (10)
            ['name' => 'Historical Buildings', 'ppe_sub_major' => '10', 'gl_ledger_acct' => '01'],
        ];

        // 1. Disable Foreign Key Checks
        Schema::disableForeignKeyConstraints();

        // 2. Safely Clear the table
        DB::table('categories')->truncate();
        
        // 3. Insert the new accurate list
        DB::table('categories')->insert($categories);

        // 4. Re-enable Foreign Key Checks (Crucial!)
        Schema::enableForeignKeyConstraints();
    }
}