<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::FREE_USER,
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::FREE_USER->value,
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals(UserRole::FREE_USER, $user->role);
    }

    public function test_user_can_be_updated()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'role' => UserRole::FREE_USER,
        ]);

        $user->update([
            'name' => 'Updated Name',
            'role' => UserRole::PAYED_USER,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => UserRole::PAYED_USER->value,
        ]);
    }

    public function test_user_can_be_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    public function test_user_role_can_be_changed_through_assign_role_method()
    {
        $user = User::factory()->create();

        $user->assignRole(UserRole::ADMIN);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRole::ADMIN->value,
        ]);

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_multiple_users_with_different_roles_can_coexist()
    {
        $admin = User::factory()->admin()->create();
        $payedUser = User::factory()->payedUser()->create();
        $freeUser = User::factory()->freeUser()->create();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => UserRole::ADMIN->value]);
        $this->assertDatabaseHas('users', ['id' => $payedUser->id, 'role' => UserRole::PAYED_USER->value]);
        $this->assertDatabaseHas('users', ['id' => $freeUser->id, 'role' => UserRole::FREE_USER->value]);

        $this->assertEquals(3, User::count());
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotEquals('password', $user->password);
    }
}
