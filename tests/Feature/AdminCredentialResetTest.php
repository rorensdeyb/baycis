<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCredentialResetTest extends TestCase
{
    use RefreshDatabase;

    // ── PASSWORD RESET ──────────────────────────────────────────────────

    public function test_generate_mode_returns_temp_password_and_forces_change(): void
    {
        $admin = User::factory()->role('admin')->create();
        $target = User::factory()->role('borrower')->create(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-password", ['mode' => 'generate']);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('temp_password'));
        $this->assertStringStartsWith('BayCIS', $response->json('temp_password'));

        $target->refresh();
        $this->assertTrue(Hash::check($response->json('temp_password'), $target->password));
        $this->assertTrue((bool)$target->requires_password_change);
        $this->assertFalse((bool)$target->is_active);
    }

    public function test_assign_mode_stores_admin_chosen_password(): void
    {
        $admin = User::factory()->role('admin')->create();
        $target = User::factory()->role('borrower')->create(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-password", [
                'mode' => 'assign',
                'password' => 'MyChosen#2026',
                'password_confirmation' => 'MyChosen#2026',
                'require_password_change' => 1,
            ]);

        $response->assertStatus(200);
        $this->assertNull($response->json('temp_password')); // never echoes assigned passwords

        $target->refresh();
        $this->assertTrue(Hash::check('MyChosen#2026', $target->password));
        $this->assertTrue((bool)$target->requires_password_change);
    }

    public function test_assign_without_forced_change_keeps_account_state(): void
    {
        $admin = User::factory()->role('admin')->create();
        $target = User::factory()->role('borrower')->create(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-password", [
                'mode' => 'assign',
                'password' => 'Instant#Login1',
                'password_confirmation' => 'Instant#Login1',
                'require_password_change' => 0,
            ]);

        $response->assertStatus(200);

        $target->refresh();
        $this->assertTrue(Hash::check('Instant#Login1', $target->password));
        $this->assertFalse((bool)$target->requires_password_change);
        $this->assertTrue((bool)$target->is_active); // untouched — can sign in immediately
    }

    public function test_assign_mode_validates_confirmation_and_length(): void
    {
        $admin = User::factory()->role('admin')->create();
        $target = User::factory()->role('borrower')->create();

        $mismatch = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-password", [
                'mode' => 'assign',
                'password' => 'GoodEnough123',
                'password_confirmation' => 'Different456',
            ]);
        $mismatch->assertStatus(422);

        $short = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-password", [
                'mode' => 'assign',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);
        $short->assertStatus(422);
    }

    public function test_admin_cannot_reset_own_password_from_user_management(): void
    {
        $admin = User::factory()->role('admin')->create(['email_verified_at' => now(), 'is_active' => true]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$admin->id}/reset-password", ['mode' => 'generate']);

        $response->assertStatus(403);
        $this->assertFalse((bool)$admin->refresh()->requires_password_change);
    }

    // ── PIN RESET ───────────────────────────────────────────────────────

    public function test_pin_reset_requires_correct_admin_pin(): void
    {
        $admin = User::factory()->role('admin')->create([
            'pin' => Hash::make('7777'),
            'pin_setup_completed' => true,
        ]);
        $target = User::factory()->role('borrower')->create([
            'pin' => Hash::make('1111'),
            'pin_setup_completed' => true,
            'failed_pin_attempts' => 2,
            'pin_locked_until' => now()->addMinutes(10),
        ]);

        $bad = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-pin", ['admin_pin' => '0000']);
        $bad->assertStatus(422);

        $target->refresh();
        $this->assertNotNull($target->pin); // untouched

        $good = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-pin", ['admin_pin' => '7777']);
        $good->assertStatus(200);

        $target->refresh();
        $this->assertNull($target->pin);
        $this->assertFalse((bool)$target->pin_setup_completed);
        $this->assertEquals(0, $target->failed_pin_attempts);
        $this->assertNull($target->pin_locked_until);

        // User is notified and the action is audited
        $this->assertTrue(Notification::where('user_id', $target->id)->where('title', 'Security PIN Reset')->exists());
        $this->assertTrue(AuditLog::where('record_id', $target->id)->where('action', 'User PIN Reset')->exists());
    }

    public function test_pin_reset_blocked_when_admin_has_no_pin(): void
    {
        $admin = User::factory()->role('admin')->create(['pin' => null, 'pin_setup_completed' => false]);
        $target = User::factory()->role('borrower')->create(['pin' => Hash::make('1111')]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$target->id}/reset-pin", ['admin_pin' => '1234']);

        $response->assertStatus(422);
        $target->refresh();
        $this->assertNotNull($target->pin); // untouched
    }
}
