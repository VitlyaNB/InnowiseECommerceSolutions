<?php

namespace Tests\Feature\Api\Chat;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_their_own_chat_channel()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->postJson('/api/broadcasting/auth', [
            'channel_name' => "chat.{$chat->id}",
            'socket_id' => '1234.1234'
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_others_chat_channel()
    {
        $user = User::factory()->create();
        $otherChat = Chat::factory()->create(); // different user by default in factory

        $this->actingAs($user);

        $response = $this->postJson('/api/broadcasting/auth', [
            'channel_name' => "chat.{$otherChat->id}",
            'socket_id' => '1234.1234'
        ]);

        $response->assertStatus(403);
    }
}
