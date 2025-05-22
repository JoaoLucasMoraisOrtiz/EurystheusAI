<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_default_free_role()
    {
        $user = User::factory()->create();
        
        $this->assertEquals(UserRole::FREE_USER, $user->role);
        $this->assertTrue($user->isFree());
    }

    public function test_user_can_be_created_with_specific_role()
    {
        $admin = User::factory()->admin()->create();
        $payedUser = User::factory()->payedUser()->create();
        $freeUser = User::factory()->freeUser()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($payedUser->isPayed());
        $this->assertTrue($freeUser->isFree());
    }

    public function test_user_can_have_role_assigned()
    {
        $user = User::factory()->create();
        
        $user->assignRole(UserRole::ADMIN);
        $this->assertTrue($user->fresh()->isAdmin());

        $user->assignRole(UserRole::PAYED_USER);
        $this->assertTrue($user->fresh()->isPayed());
    }

    public function test_user_has_role_method()
    {
        $admin = User::factory()->admin()->create();
        
        $this->assertTrue($admin->hasRole(UserRole::ADMIN));
        $this->assertFalse($admin->hasRole(UserRole::FREE_USER));
    }

    public function test_user_role_is_cast_to_enum()
    {
        $user = User::factory()->admin()->create();
        
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::ADMIN, $user->role);
    }
}
