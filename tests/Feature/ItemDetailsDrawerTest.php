<?php

namespace Tests\Feature;

use App\Models\AssetTag;
use App\Models\BorrowRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemDetailsDrawerTest extends TestCase
{
    use RefreshDatabase;

    private function makeItem(array $overrides = []): array
    {
        $cat = Category::create(['name' => 'ICT Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '03']);
        $tag = AssetTag::create(['category_id' => $cat->id, 'name' => 'Projector']);
        $locId = DB::table('locations')->insertGetId(['name' => 'ICT Room', 'code' => 'IT', 'created_at' => now(), 'updated_at' => now()]);
        $supId = DB::table('suppliers')->insertGetId(['name' => 'LGU', 'created_at' => now(), 'updated_at' => now()]);

        $itemId = DB::table('items')->insertGetId(array_merge([
            'property_tag' => '2026-05-03-0001(1)-108200',
            'name' => 'Epson EB-X51 Projector',
            'category_id' => $cat->id,
            'tag_id' => $tag->id,
            'location_id' => $locId,
            'supplier_id' => $supId,
            'accountable_personnel' => 'Mr. Reyes',
            'acquisition_date' => '2026-05-03',
            'acquisition_cost' => 25000,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return [$itemId, $tag->id];
    }

    public function test_admin_drawer_details_include_tag_and_property_tag(): void
    {
        $admin = User::factory()->role('admin')->create();
        [$itemId] = $this->makeItem();

        $response = $this->actingAs($admin)
            ->get("/admin/inventory/{$itemId}/details", ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonPath('property_tag', '2026-05-03-0001(1)-108200')
            ->assertJsonPath('tag', 'Projector')
            ->assertJsonPath('category', 'ICT Equipment')
            ->assertJsonPath('current_holder', null);
    }

    public function test_borrowed_items_report_their_current_holder(): void
    {
        $admin = User::factory()->role('admin')->create();
        $borrower = User::factory()->role('borrower')->create();
        [$itemId] = $this->makeItem(['status' => 'borrowed']);

        BorrowRequest::create([
            'user_id' => $borrower->id,
            'item_id' => $itemId,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/inventory/{$itemId}/details", ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertEquals($borrower->name, $response->json('current_holder.name'));
    }

    public function test_borrowers_cannot_open_admin_item_details(): void
    {
        $borrower = User::factory()->role('borrower')->create();
        [$itemId] = $this->makeItem();

        $this->actingAs($borrower)
            ->get("/admin/inventory/{$itemId}/details", ['Accept' => 'application/json'])
            ->assertStatus(403);
    }
}
