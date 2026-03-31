<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckAdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_protected_route(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);
        $this->actingAs($admin)->getJson('/api/users')->assertStatus(200);
    }

    public function test_user_cannot_access_protected_route(): void
    {
        $user = User::factory()->createOne(['role' => 'user']);
        $this->actingAs($user)->getJson('/api/users')->assertStatus(403);
    }
}
