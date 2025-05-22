<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_can_access_dashboard()
    {
        $user = User::factory()->freeUser()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Free User');
        $response->assertDontSee('Admin Panel');
    }

    public function test_payed_user_can_access_dashboard()
    {
        $user = User::factory()->payedUser()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Premium Features');
        $response->assertDontSee('Admin Panel');
    }

    public function test_admin_user_can_access_dashboard_with_admin_link()
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Features');
        $response->assertSee('Admin Panel');
    }

    public function test_dashboard_displays_user_information()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('Free User');
    }
}
