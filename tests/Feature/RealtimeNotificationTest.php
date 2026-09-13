<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use App\Models\ConsumableIssuance;
use App\Models\ConsumableStock;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_poll_returns_realtime_counts_and_notifications(): void
    {
        $admin = User::factory()->role('admin')->create();

        $notif = Notification::create([
            'user_id' => $admin->id,
            'title' => 'New Borrow Request',
            'message' => 'John Doe requested a projector.',
            'type' => 'request',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->getJson(route('notifications.poll'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'unread',
            'notifications',
            'max_id',
            'counts' => [
                'pending_borrow_requests',
                'pending_returns',
                'pending_issue_requests',
                'pending_confirmations',
                'pending_consumable_issuances',
                'pending_account_requests',
                'total_nav_pending',
                'latest_request_id',
                'latest_request_updated_at',
                'latest_return_id',
                'latest_return_updated_at',
                'latest_issuance_id',
                'latest_issuance_updated_at',
            ],
        ]);

        $response->assertJsonFragment([
            'unread' => 1,
            'max_id' => $notif->id,
        ]);
    }

    public function test_poll_since_id_returns_new_notifications_only(): void
    {
        $admin = User::factory()->role('admin')->create();

        $notif1 = Notification::create([
            'user_id' => $admin->id,
            'title' => 'First Notification',
            'message' => 'First message',
            'type' => 'alert',
            'is_read' => false,
        ]);

        $notif2 = Notification::create([
            'user_id' => $admin->id,
            'title' => 'Second Notification',
            'message' => 'Second message',
            'type' => 'request',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->getJson(route('notifications.poll', ['since_id' => $notif1->id]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals($notif2->id, $data['max_id']);
        $this->assertArrayHasKey('new_notifications', $data);
        $this->assertCount(1, $data['new_notifications']);
        $this->assertEquals($notif2->id, $data['new_notifications'][0]['id']);
        $this->assertEquals('Second Notification', $data['new_notifications'][0]['title']);
    }

    public function test_borrower_poll_returns_borrower_specific_counts(): void
    {
        $borrower = User::factory()->role('borrower')->create();

        $response = $this->actingAs($borrower)->getJson(route('notifications.poll'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'unread',
            'notifications',
            'max_id',
            'counts' => [
                'pending_confirmations',
                'active_loans',
                'pending_borrow_requests',
                'pending_consumable_issuances',
                'total_nav_pending',
                'latest_return_updated_at',
            ],
        ]);
    }

    public function test_admin_requests_returns_partial_table_on_ajax_or_param(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get(route('admin.requests', ['ajax_table' => 1]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('requests-table', $content);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $content);
        $this->assertStringNotContainsString('<body', $content);
    }

    public function test_admin_returns_returns_partial_table_on_ajax_or_param(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get(route('admin.returns', ['ajax_table' => 1]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('returns-table', $content);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $content);
        $this->assertStringNotContainsString('<body', $content);
    }

    public function test_admin_issuance_returns_partial_pending_panel_on_ajax_or_param(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->get(route('items.issuance', ['ajax_pending' => 1]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('pending-panel-content', $content);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $content);
        $this->assertStringNotContainsString('<body', $content);
    }

    public function test_notification_mark_read_returns_json_on_ajax(): void
    {
        $admin = User::factory()->role('admin')->create();
        $notif = Notification::create([
            'user_id' => $admin->id,
            'title' => 'Test Notification',
            'message' => 'Test Message',
            'type' => 'request',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->postJson(route('notifications.read', $notif->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_read' => true,
            'unread' => 0,
        ]);
        $this->assertTrue((bool) $notif->fresh()->is_read);
    }

    public function test_notification_toggle_read_toggles_status_and_returns_json(): void
    {
        $admin = User::factory()->role('admin')->create();
        $notif = Notification::create([
            'user_id' => $admin->id,
            'title' => 'Test Notification',
            'message' => 'Test Message',
            'type' => 'request',
            'is_read' => false,
        ]);

        // First toggle: unread -> read
        $res1 = $this->actingAs($admin)->postJson(route('notifications.toggle-read', $notif->id));
        $res1->assertStatus(200);
        $res1->assertJson([
            'success' => true,
            'is_read' => true,
            'unread' => 0,
        ]);
        $this->assertTrue((bool) $notif->fresh()->is_read);

        // Second toggle: read -> unread
        $res2 = $this->actingAs($admin)->postJson(route('notifications.toggle-read', $notif->id));
        $res2->assertStatus(200);
        $res2->assertJson([
            'success' => true,
            'is_read' => false,
            'unread' => 1,
        ]);
        $this->assertFalse((bool) $notif->fresh()->is_read);
    }

    public function test_notification_destroy_deletes_notification(): void
    {
        $admin = User::factory()->role('admin')->create();
        $notif = Notification::create([
            'user_id' => $admin->id,
            'title' => 'To Delete',
            'message' => 'Delete me',
            'type' => 'alert',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->deleteJson(route('notifications.destroy', $notif->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'unread' => 0,
        ]);
        $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
    }

    public function test_notification_mark_all_read_marks_all_as_read_and_returns_json(): void
    {
        $admin = User::factory()->role('admin')->create();
        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Notif 1',
            'message' => 'Msg 1',
            'type' => 'request',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Notif 2',
            'message' => 'Msg 2',
            'type' => 'alert',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->postJson(route('notifications.read-all'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'unread' => 0,
        ]);
        $this->assertEquals(0, Notification::where('user_id', $admin->id)->where('is_read', 0)->count());
    }

    public function test_notification_clear_all_deletes_notifications(): void
    {
        $admin = User::factory()->role('admin')->create();
        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Notif 1',
            'message' => 'Msg 1',
            'type' => 'request',
            'is_read' => true,
        ]);
        Notification::create([
            'user_id' => $admin->id,
            'title' => 'Notif 2',
            'message' => 'Msg 2',
            'type' => 'alert',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->postJson(route('notifications.clear-all'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'unread' => 0,
        ]);
        $this->assertEquals(0, Notification::where('user_id', $admin->id)->count());
    }

    public function test_poll_returns_enriched_notification_meta(): void
    {
        $admin = User::factory()->role('admin')->create();
        $notif = Notification::create([
            'user_id' => $admin->id,
            'title' => 'Return requested',
            'message' => 'Item returned',
            'type' => 'return',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->getJson(route('notifications.poll'));

        $response->assertStatus(200);
        $notifs = $response->json('notifications');
        $this->assertNotEmpty($notifs);
        $item = $notifs[0];
        $this->assertEquals($notif->id, $item['id']);
        $this->assertArrayHasKey('target_url', $item);
        $this->assertArrayHasKey('type_icon', $item);
        $this->assertArrayHasKey('type_class', $item);
        $this->assertArrayHasKey('type_bg', $item);
        $this->assertEquals('bi-arrow-return-left', $item['type_icon']);
    }
}