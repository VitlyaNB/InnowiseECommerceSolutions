<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckAdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_protected_route()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->getJson('/api/users')->assertStatus(200);
    }

    public function test_user_cannot_access_protected_route()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->getJson('/api/users')->assertStatus(403);
    }
}
