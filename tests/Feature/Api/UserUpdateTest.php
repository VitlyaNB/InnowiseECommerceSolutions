<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_user_profile(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);
        $user = User::factory()->createOne(['email' => 'me@example.com']);

        $response = $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'me@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_admin_cannot_take_another_users_email_for_user(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);
        $user1 = User::factory()->createOne(['email' => 'user1@example.com']);
        $user2 = User::factory()->createOne(['email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->putJson("/api/users/{$user1->id}", [
            'name' => 'Hack Name',
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
