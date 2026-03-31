<?php

namespace App\Http\Resources;

use App\Dto\UserDto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;

        if ($resource instanceof UserDto) {
            return [
                'id' => $resource->id,
                'name' => $resource->name,
                'email' => $resource->email,
                'role' => $resource->role,
                'balance' => (float) $resource->balance,
                'created_at' => $resource->createdAt,
            ];
        }

        if (! $resource instanceof User) {
            return [];
        }

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'email' => $resource->email,
            'role' => $resource->role,
            'balance' => (float) $resource->balance,
            'created_at' => $resource->created_at->toDateTimeString(),
        ];
    }
}
