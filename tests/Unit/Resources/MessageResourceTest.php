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

    public function test_message_resource_formats_data_correctly()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $chat = Chat::factory()->create();
        $message = Message::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => 'Test message content',
        ]);

        $resource = (new MessageResource($message->load('user')))->toArray(request());

        $this->assertEquals('Test message content', $resource['message']);
        $this->assertEquals('John Doe', $resource['user']['name']);
        $this->assertArrayHasKey('created_at', $resource);
    }
}
