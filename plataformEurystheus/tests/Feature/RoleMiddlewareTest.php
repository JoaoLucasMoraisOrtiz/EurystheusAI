<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_only_admin_can_access_admin_panel()
    {
        $freeUser = User::factory()->freeUser()->create();
        $payedUser = User::factory()->payedUser()->create();
        $admin = User::factory()->admin()->create();

        // Free user cannot access admin panel
        $response = $this->actingAs($freeUser)->get('/admin');
        $response->assertStatus(403);

        // Payed user cannot access admin panel
        $response = $this->actingAs($payedUser)->get('/admin');
        $response->assertStatus(403);

        // Admin can access admin panel
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_change_user_roles()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}/role", [
                'role' => UserRole::PAYED_USER->value,
            ]);

        $response->assertRedirect();
        $this->assertEquals(UserRole::PAYED_USER, $user->fresh()->role);
    }

    public function test_non_admin_cannot_change_user_roles()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->patch("/admin/users/{$targetUser->id}/role", [
                'role' => UserRole::ADMIN->value,
            ]);

        $response->assertStatus(403);
    }
}
