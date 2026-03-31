<?php

namespace Tests\Feature\Api;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_message_broadcasts_event(): void
    {
        Event::fake();

        $user = User::factory()->createOne();
        $chat = Chat::factory()->createOne(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Hello, support!',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(MessageSent::class, function ($event) use ($user, $chat) {
            return $event->message->message === 'Hello, support!' &&
                $event->message->userId === $user->id &&
                $event->message->chatId === $chat->id;
        });
    }

    public function test_user_cannot_view_others_chats(): void
    {
        $user1 = User::factory()->createOne();
        $user2 = User::factory()->createOne();
        $chatOfUser2 = Chat::factory()->createOne(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->getJson("/api/chats/{$chatOfUser2->id}");

        $response->assertStatus(403);
    }
}
