<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_resource_formats_data_correctly(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne(['name' => 'John Doe']);
        /** @var Chat $chat */
        $chat = Chat::factory()->createOne();
        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => 'Test message content',
        ]);

        $resource = (new MessageResource($message->load('user')))->toArray(request());
        /** @var array<string, mixed> $resource */
        $resource = $resource;
        /** @var array<string, mixed> $resourceUser */
        $resourceUser = is_array($resource['user'] ?? null) ? $resource['user'] : [];

        $this->assertEquals('Test message content', $resource['message']);
        $this->assertEquals('John Doe', $resourceUser['name'] ?? null);
        $this->assertArrayHasKey('created_at', $resource);
    }
}
