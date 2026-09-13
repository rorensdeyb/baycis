<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_custodian_cannot_access_user_management(): void
    {
        $custodian = User::factory()->role('custodian')->create();

        $response = $this->actingAs($custodian)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_borrower_cannot_access_admin_dashboard(): void
    {
        $borrower = User::factory()->role('borrower')->create();

        $response = $this->actingAs($borrower)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_custodian_can_access_admin_dashboard(): void
    {
        $custodian = User::factory()->role('custodian')->create();

        $response = $this->actingAs($custodian)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/');
    }

    public function test_user_management_renders_toolbar_and_filter_chips_when_filtered(): void
    {
        $admin = User::factory()->role('admin')->create();
        // Ensure the results footer (pagination) is present
        User::factory()->count(12)->create();

        // Unfiltered: no chips row
        $clean = $this->actingAs($admin)->get('/admin/users');
        $clean->assertStatus(200);
        $clean->assertSee('userSearchInput', false);
        $clean->assertDontSee('Filters:', false);

        // Filtered: chips + Clear all appear
        $filtered = $this->actingAs($admin)->get('/admin/users?role=borrower&status=active');
        $filtered->assertStatus(200);
        $filtered->assertSee('Filters:', false);
        $filtered->assertSee('Clear all', false);
        $filtered->assertSee('toolbarRole', false);
        $filtered->assertSee('toolbarStatus', false);
        $filtered->assertSee('Rows per page', false);
    }

    public function test_ajax_search_returns_rows_without_full_page(): void
    {
        $admin = User::factory()->role('admin')->create();
        User::factory()->create(['name' => 'Juan Dela Cruz']);

        $response = $this->actingAs($admin)
            ->get('/admin/users?search=juan', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200)
            ->assertJsonStructure(['rows', 'total', 'first', 'last', 'page', 'pages', 'has_pages', 'lifecycleCounts']);

        $this->assertStringContainsString('Juan Dela Cruz', $response->json('rows'));
    }
}
