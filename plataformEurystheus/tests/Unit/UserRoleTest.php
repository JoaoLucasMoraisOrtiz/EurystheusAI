<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_role_enum_values()
    {
        $roles = UserRole::values();
        
        $this->assertContains('admin', $roles);
        $this->assertContains('free_user', $roles);
        $this->assertContains('payed_user', $roles);
        $this->assertCount(3, $roles);
    }

    public function test_user_role_labels()
    {
        $this->assertEquals('Administrator', UserRole::ADMIN->label());
        $this->assertEquals('Free User', UserRole::FREE_USER->label());
        $this->assertEquals('Payed User', UserRole::PAYED_USER->label());
    }

    public function test_user_role_checking_methods()
    {
        $this->assertTrue(UserRole::ADMIN->isAdmin());
        $this->assertFalse(UserRole::ADMIN->isFree());
        $this->assertFalse(UserRole::ADMIN->isPayed());

        $this->assertTrue(UserRole::FREE_USER->isFree());
        $this->assertFalse(UserRole::FREE_USER->isAdmin());
        $this->assertFalse(UserRole::FREE_USER->isPayed());

        $this->assertTrue(UserRole::PAYED_USER->isPayed());
        $this->assertFalse(UserRole::PAYED_USER->isAdmin());
        $this->assertFalse(UserRole::PAYED_USER->isFree());
    }
}
