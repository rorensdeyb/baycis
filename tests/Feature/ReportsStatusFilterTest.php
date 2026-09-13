<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_parameter_lists_every_inventory_status(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertOk();

        foreach (Item::STATUS_OPTIONS as $value => $label) {
            $response->assertSee('value="' . $value . '"', false);
            $response->assertSee($label, false);
        }
    }

    public function test_disposed_status_report_includes_archived_assets(): void
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::create([
            'name' => 'ICT Equipment',
            'ppe_sub_major' => '05',
            'gl_ledger_acct' => '03',
        ]);
        $location = Location::create(['name' => 'ICT Room', 'code' => 'IT']);
        $supplier = Supplier::create(['name' => 'LGU']);
        $item = Item::create([
            'property_tag' => '2026-05-03-0001(1)-108200',
            'name' => 'Archived Projector',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'supplier_id' => $supplier->id,
            'accountable_personnel' => 'Mr. Reyes',
            'acquisition_date' => '2026-05-03',
            'acquisition_cost' => 25000,
            'status' => 'disposed',
        ]);
        $item->delete();

        $response = $this->actingAs($admin)->get(
            '/admin/reports?generate=1&report_type=summary&status=disposed'
        );

        $response->assertOk()
            ->assertSee('Archived Projector', false)
            ->assertDontSee('No matching transactional asset elements', false);
    }
}
