<?php

namespace Tests\Feature;

use App\Models\AssetTag;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssetTagTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    private function makeIctCategory(): Category
    {
        return Category::create(['name' => 'ICT Equipment', 'ppe_sub_major' => '05', 'gl_ledger_acct' => '03']);
    }

    public function test_admin_can_create_update_and_delete_tags(): void
    {
        $admin = $this->admin();
        $cat = $this->makeIctCategory();

        // Create
        $store = $this->actingAs($admin)
            ->post('/admin/settings/tags', ['category_id' => $cat->id, 'name' => 'Laptop']);
        $store->assertRedirect();
        $this->assertDatabaseHas('asset_tags', ['category_id' => $cat->id, 'name' => 'Laptop']);

        $tag = AssetTag::where('name', 'Laptop')->first();

        // Duplicate rejected
        $dup = $this->actingAs($admin)
            ->post('/admin/settings/tags', ['category_id' => $cat->id, 'name' => 'Laptop']);
        $dup->assertRedirect();
        $this->assertEquals(1, AssetTag::where('category_id', $cat->id)->count());

        // Update
        $upd = $this->actingAs($admin)->post("/admin/settings/tags/{$tag->id}", [
            'category_id' => $cat->id,
            'name' => 'Printer',
            '_method' => 'PUT',
        ]);
        $upd->assertRedirect();
        $this->assertDatabaseHas('asset_tags', ['id' => $tag->id, 'name' => 'Printer']);
    }

    public function test_deleting_a_tag_unlinks_items_without_touching_them(): void
    {
        $admin = $this->admin();
        $cat = $this->makeIctCategory();
        $tag = AssetTag::create(['category_id' => $cat->id, 'name' => 'Projector']);

        $locId = DB::table('locations')->insertGetId(['name' => 'ICT Room', 'code' => 'IT', 'created_at' => now(), 'updated_at' => now()]);
        $supId = DB::table('suppliers')->insertGetId(['name' => 'LGU', 'created_at' => now(), 'updated_at' => now()]);

        $itemId = DB::table('items')->insertGetId([
            'property_tag' => '2026-05-03-0001(1)-108200',
            'name' => 'Epson EB-X51',
            'category_id' => $cat->id,
            'tag_id' => $tag->id,
            'location_id' => $locId,
            'supplier_id' => $supId,
            'accountable_personnel' => 'Test Personnel',
            'acquisition_date' => '2026-05-03',
            'acquisition_cost' => 25000,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $del = $this->actingAs($admin)->delete("/admin/settings/tags/{$tag->id}");
        $del->assertRedirect();

        $this->assertDatabaseMissing('asset_tags', ['id' => $tag->id]);
        // The item itself survives with the link cleared
        $item = DB::table('items')->find($itemId);
        $this->assertNotNull($item);
        $this->assertNull($item->tag_id);
    }

    public function test_settings_page_shows_the_tags_manager(): void
    {
        $admin = $this->admin();
        $cat = $this->makeIctCategory();
        AssetTag::create(['category_id' => $cat->id, 'name' => 'Laptop']);

        $response = $this->actingAs($admin)->get('/admin/settings');
        $response->assertStatus(200);
        // The Categories tab links to the Inventory Reference manager
        $response->assertSee('Sub-Categories / Tags', false);
        $response->assertSee('Inventory Reference', false);
    }

    public function test_inventory_reference_page_lists_categories_with_inline_add_forms(): void
    {
        $admin = $this->admin();
        $cat = $this->makeIctCategory();
        AssetTag::create(['category_id' => $cat->id, 'name' => 'Laptop']);
        AssetTag::create(['category_id' => $cat->id, 'name' => 'Projector']);

        $response = $this->actingAs($admin)->get('/admin/settings/inventory-reference');

        $response->assertStatus(200);
        $response->assertSee('Inventory Reference', false);
        $response->assertSee('ICT Equipment', false);
        $response->assertSee('Laptop', false);
        $response->assertSee('Projector', false);
        // One inline add-form per category posting to the tag store endpoint
        $response->assertSee('/admin/settings/tags', false);

        // Manually add through the same endpoint the page posts to
        $add = $this->actingAs($admin)
            ->post('/admin/settings/tags', ['category_id' => $cat->id, 'name' => 'Scanner']);
        $add->assertRedirect();
        $this->assertDatabaseHas('asset_tags', ['category_id' => $cat->id, 'name' => 'Scanner']);

        // Page now reflects it
        $again = $this->actingAs($admin)->get('/admin/settings/inventory-reference');
        $again->assertSee('Scanner', false);
    }

    public function test_item_form_contains_dependent_tag_dropdown(): void
    {
        $admin = $this->admin();
        $this->makeIctCategory();

        $response = $this->actingAs($admin)->get('/admin/inventory/create');
        $response->assertStatus(200);
        $response->assertSee('id="tag_id"', false);
        $response->assertSee('Sub-Category / Tag', false);
    }
}

