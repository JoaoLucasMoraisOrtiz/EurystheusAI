<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_user_list()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('users');
    }

    public function test_admin_can_update_user_role()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/role", [
                'role' => UserRole::PAYED_USER->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(UserRole::PAYED_USER, $user->fresh()->role);
    }

    public function test_admin_cannot_remove_last_admin_role()
    {
        // Ensure this is the only admin
        User::where('role', UserRole::ADMIN)->where('id', '!=', $this->admin->id)->delete();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$this->admin->id}/role", [
                'role' => UserRole::FREE_USER->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(UserRole::ADMIN, $this->admin->fresh()->role);
    }

    public function test_admin_can_remove_admin_role_when_other_admins_exist()
    {
        $anotherAdmin = User::factory()->admin()->create();
        $targetAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$targetAdmin->id}/role", [
                'role' => UserRole::FREE_USER->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(UserRole::FREE_USER, $targetAdmin->fresh()->role);
    }

    public function test_admin_role_update_validates_role_input()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/role", [
                'role' => 'invalid_role',
            ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertEquals(UserRole::FREE_USER, $user->fresh()->role);
    }

    public function test_admin_can_promote_user_to_admin()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/role", [
                'role' => UserRole::ADMIN->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(UserRole::ADMIN, $user->fresh()->role);
    }
}
