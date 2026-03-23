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
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof UserDto) {
            return [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'role' => $this->resource->role,
                'balance' => (float) $this->resource->balance,
                'created_at' => $this->resource->createdAt,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'balance' => (float) ($this->balance ?? 0),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
