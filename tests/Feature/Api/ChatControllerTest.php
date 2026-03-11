<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Chat;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_message_broadcasts_event()
    {
        Event::fake();

        $user = User::factory()->create();
        $chat = Chat::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Hello, support!'
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(MessageSent::class, function ($event) use ($user, $chat) {
            return $event->message->message === 'Hello, support!' &&
                $event->message->user_id === $user->id;
        });
    }

    public function test_user_cannot_view_others_chats()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $chatOfUser2 = Chat::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->getJson("/api/chats/{$chatOfUser2->id}");

        $response->assertStatus(403);
    }
}
