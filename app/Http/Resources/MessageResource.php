<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property string $message
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon $created_at
 * @property \App\Models\User $user
 * 
 * @mixin \App\Models\Message
 */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role,
            ],
        ];
    }
}
