<?php

namespace App\Http\Resources;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property User $user
 * @property Carbon $last_message_at
 * @property Collection<int, Message> $messages
 *
 * @mixin Chat
 */
class ChatResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'last_message_at' => $this->last_message_at,
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'last_message' => $this->messages()->exists()
                ? new MessageResource($this->messages()->latest()->first())
                : null,
        ];
    }
}
