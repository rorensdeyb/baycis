<?php

namespace Tests\Feature;

use App\Models\AssetTag;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemExportTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleItem(array $attributes = []): Item
    {
        $cat = Category::firstOrCreate(['name' => 'ICT Equipment'], ['ppe_sub_major' => '05', 'gl_ledger_acct' => '03']);
        $tag = AssetTag::firstOrCreate(['name' => 'Projector', 'category_id' => $cat->id]);
        $locId = DB::table('locations')->insertGetId([
            'name' => 'ICT Room',
            'code' => 'IT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $supId = DB::table('suppliers')->insertGetId([
            'name' => 'DepEd Division Office',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Item::create(array_merge([
            'property_tag'          => '2026-05-03-0001(1)-108200',
            'name'                  => 'Epson EB-X51 Projector',
            'category_id'           => $cat->id,
            'tag_id'                => $tag->id,
            'location_id'           => $locId,
            'supplier_id'           => $supId,
            'accountable_personnel' => 'Mr. Reyes',
            'acquisition_date'      => '2026-05-03',
            'acquisition_cost'      => 25000.00,
            'serial_number'         => 'SN-12345678',
            'status'                => 'available',
        ], $attributes));
    }

    public function test_admin_can_export_items_to_csv(): void
    {
        $admin = User::factory()->role('admin')->create();
        $item = $this->createSampleItem();

        $response = $this->actingAs($admin)->get(route('items.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="inventory-items-', $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();

        $this->assertStringContainsString('Property Tag', $content);
        $this->assertStringContainsString('Item Name', $content);
        $this->assertStringContainsString('Category', $content);
        $this->assertStringContainsString('Location', $content);
        $this->assertStringContainsString('2026-05-03-0001(1)-108200', $content);
        $this->assertStringContainsString('Epson EB-X51 Projector', $content);
        $this->assertStringContainsString('ICT Equipment', $content);
        $this->assertStringContainsString('ICT Room', $content);
        $this->assertStringContainsString('Available', $content);
        $this->assertStringContainsString('25000.00', $content);
    }

    public function test_custodian_can_export_items_to_csv(): void
    {
        $custodian = User::factory()->role('custodian')->create();
        $this->createSampleItem();

        $response = $this->actingAs($custodian)->get(route('items.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment; filename="inventory-items-', $response->headers->get('Content-Disposition'));
    }

    public function test_borrower_cannot_export_items(): void
    {
        $borrower = User::factory()->role('borrower')->create();
        $this->createSampleItem();

        $response = $this->actingAs($borrower)->get(route('items.export'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_export(): void
    {
        $this->createSampleItem();

        $response = $this->get(route('items.export'));

        $response->assertRedirect('/');
    }

    public function test_export_filters_by_search(): void
    {
        $admin = User::factory()->role('admin')->create();
        $itemA = $this->createSampleItem([
            'property_tag' => 'TAG-AAA-001',
            'name'         => 'Acer Nitro 5 Laptop',
        ]);
        $itemB = $this->createSampleItem([
            'property_tag' => 'TAG-BBB-002',
            'name'         => 'Logitech C920 Webcam',
        ]);

        $response = $this->actingAs($admin)->get(route('items.export', ['search' => 'Acer']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('Acer Nitro 5 Laptop', $content);
        $this->assertStringNotContainsString('Logitech C920 Webcam', $content);
    }

    public function test_export_filters_by_category(): void
    {
        $admin = User::factory()->role('admin')->create();
        $catA = Category::create(['name' => 'IT Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '03']);
        $catB = Category::create(['name' => 'Office Furniture', 'ppe_sub_major' => '07', 'gl_ledger_acct' => '01']);

        $itemA = $this->createSampleItem([
            'category_id'  => $catA->id,
            'property_tag' => 'CAT-A-001',
            'name'         => 'Item in IT',
        ]);
        $itemB = $this->createSampleItem([
            'category_id'  => $catB->id,
            'property_tag' => 'CAT-B-002',
            'name'         => 'Item in Furniture',
        ]);

        $response = $this->actingAs($admin)->get(route('items.export', ['category' => $catA->id]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('Item in IT', $content);
        $this->assertStringNotContainsString('Item in Furniture', $content);
    }

    public function test_export_filters_by_status(): void
    {
        $admin = User::factory()->role('admin')->create();
        $itemA = $this->createSampleItem([
            'property_tag' => 'STAT-A-001',
            'name'         => 'Active Available Item',
            'status'       => 'available',
        ]);
        $itemB = $this->createSampleItem([
            'property_tag' => 'STAT-B-002',
            'name'         => 'Damaged Offline Item',
            'status'       => 'damaged',
        ]);

        $response = $this->actingAs($admin)->get(route('items.export', ['status' => 'available']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('Active Available Item', $content);
        $this->assertStringNotContainsString('Damaged Offline Item', $content);
    }
}
