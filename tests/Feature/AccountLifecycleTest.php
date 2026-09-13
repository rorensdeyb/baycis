<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    // ── SELF-REGISTRATION → OTP → ADMIN ACTIVATION ──────────────────────

    public function test_self_registration_creates_inactive_unverified_user(): void
    {
        $response = $this->postJson('/auth/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@bces.edu.ph',
            'teacher_id' => 'TCH-9001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'juan@bces.edu.ph',
            'role' => 'borrower',
            'registration_source' => 'self_registered',
        ]);

        $user = User::where('email', 'juan@bces.edu.ph')->first();
        $this->assertFalse((bool)$user->is_active);
        $this->assertNull($user->email_verified_at);
    }

    public function test_verified_self_registered_user_stays_inactive_until_admin_approves(): void
    {
        // Regression guard: email_verified_at must be mass-assignable so that
        // verifyOtp() can actually persist it (was silently dropped before).
        $user = User::factory()->create([
            'role' => 'borrower',
            'registration_source' => 'self_registered',
            'is_active' => false,
            'requires_password_change' => false,
            'email_verified_at' => null,
            'otp_code' => Hash::make('123456'),
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        $this->assertNull($user->fresh()->email_verified_at);

        $response = $this->withSession(['pending_verification_email' => $user->email])
            ->postJson('/verify-otp-process', ['otp' => '123456']);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at); // The actual regression
        $this->assertFalse((bool)$user->is_active);     // Still awaiting admin approval
        $this->assertTrue($user->isAwaitingActivation());
    }

    public function test_admin_can_activate_a_self_registered_account(): void
    {
        $admin = User::factory()->role('admin')->create();
        $pending = User::factory()->create([
            'role' => 'borrower',
            'registration_source' => 'self_registered',
            'is_active' => false,
            'email_verified_at' => now(),
        ]);
        $this->assertTrue($pending->isAwaitingActivation());

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$pending->id}/lifecycle-action", ['action' => 'approve_activation']);

        $response->assertStatus(200);
        $this->assertTrue((bool)$pending->refresh()->is_active);
        $this->assertFalse($pending->isAwaitingActivation());
    }

    public function test_awaiting_approval_user_cannot_login_and_gets_pending_message(): void
    {
        User::factory()->create([
            'email' => 'waiting@bces.edu.ph',
            'password' => 'password123',
            'role' => 'borrower',
            'registration_source' => 'self_registered',
            'is_active' => false,
            'requires_password_change' => false,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/login-process', [
            'login_id' => 'waiting@bces.edu.ph',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)->assertJsonPath('status', 'pending_approval');
        $this->assertGuest();
    }

    public function test_scheduled_deletion_user_cannot_login_via_pin(): void
    {
        User::factory()->create([
            'email' => 'purge-me@bces.edu.ph',
            'pin' => Hash::make('2222'),
            'role' => 'borrower',
            'is_active' => false,
            'requires_password_change' => false,
            'deletion_effective_at' => now()->addDays(10),
        ]);

        $response = $this->postJson('/login-process', [
            'login_id' => 'purge-me@bces.edu.ph',
            'use_pin' => true,
            'pin' => '2222',
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('scheduled for permanent deletion', $response->json('message'));
        $this->assertGuest();
    }

    // ── DEACTIVATION / DELETION REQUESTS ────────────────────────────────

    public function test_admin_cannot_directly_delete_accounts(): void
    {
        $admin = User::factory()->role('admin')->create();
        $target = User::factory()->create();

        // The DELETE route was removed entirely
        $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}");
        $response->assertStatus(405);

        $this->assertNotNull(User::find($target->id));
    }

    public function test_deletion_request_requires_valid_pin(): void
    {
        $user = User::factory()->create(['pin' => Hash::make('1111'), 'pin_setup_completed' => true]);

        $bad = $this->actingAs($user)->postJson('/account/request-deletion', [
            'reason' => 'I no longer need this account, thank you.',
            'pin' => '9999',
        ]);
        $bad->assertStatus(422);

        $good = $this->actingAs($user)->postJson('/account/request-deletion', [
            'reason' => 'I no longer need this account, thank you.',
            'pin' => '1111',
        ]);
        $good->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->hasPendingDeletionRequest());
        $this->assertFalse($user->isScheduledForDeletion());
    }

    public function test_admin_deletion_approval_starts_60_day_grace_period(): void
    {
        $admin = User::factory()->role('admin')->create();
        $requester = User::factory()->create([
            'deletion_requested_at' => now(),
            'deletion_reason' => 'Leaving the school.',
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/admin/users/{$requester->id}/lifecycle-action", ['action' => 'approve_deletion']);

        $response->assertStatus(200);

        $requester->refresh();
        $this->assertTrue($requester->isScheduledForDeletion());
        $this->assertFalse((bool)$requester->is_active);
        $this->assertEquals(
            now()->addDays(User::DELETION_BUFFER_DAYS)->format('Y-m-d'),
            $requester->deletion_effective_at->format('Y-m-d')
        );
    }

    public function test_purge_command_anonymizes_but_preserves_transactions(): void
    {
        $requester = User::factory()->create([
            'is_active' => false,
            'deletion_effective_at' => now()->subDay(),
        ]);

        // Simulate an existing closed transaction owned by the user
        $txnId = DB::table('transactions')->insertGetId([
            'user_id' => $requester->id,
            'type' => 'borrow',
            'status' => 'completed',
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('accounts:purge-expired')->assertSuccessful();

        $requester->refresh();
        // Account is anonymized...
        $this->assertTrue(str_starts_with($requester->name, 'Deleted User #'));
        $this->assertStringEndsWith('@deleted.local', $requester->email);
        $this->assertNull($requester->teacher_id);
        $this->assertNotNull($requester->deletion_purged_at);
        // ...but the transaction row is untouched and still linked
        $txn = DB::table('transactions')->find($txnId);
        $this->assertNotNull($txn);
        $this->assertEquals($requester->id, $txn->user_id);
        $this->assertEquals('completed', $txn->status);
    }

    public function test_user_can_cancel_scheduled_deletion_within_buffer(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'deletion_effective_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->postJson('/account/cancel-request');

        $response->assertStatus(200);
        $user->refresh();
        $this->assertNull($user->deletion_effective_at);
        $this->assertTrue((bool)$user->is_active);
    }

    public function test_last_active_admin_cannot_be_deleted_or_deactivated(): void
    {
        $lastAdmin = User::factory()->role('admin')->create([
            'is_active' => true,
            'deletion_requested_at' => now(),
        ]);

        // Another admin exists but is inactive — the active one must be protected
        User::factory()->role('admin')->create(['is_active' => false]);

        $response = $this->actingAs($lastAdmin)
            ->postJson("/admin/users/{$lastAdmin->id}/lifecycle-action", ['action' => 'approve_deletion']);

        $response->assertStatus(422);
        $lastAdmin->refresh();
        $this->assertNull($lastAdmin->deletion_effective_at);
    }
}
