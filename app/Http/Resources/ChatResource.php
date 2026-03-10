<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property \App\Models\User $user
 * @property \Illuminate\Support\Carbon $last_message_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * 
 * @mixin \App\Models\Chat
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
