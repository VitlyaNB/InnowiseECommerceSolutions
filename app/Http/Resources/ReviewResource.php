<?php

namespace App\Http\Resources;

use App\Dto\ReviewDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ReviewDto) {
            return [
                'id' => $this->resource->id,
                'user' => [
                    'id' => $this->resource->userId,
                    'name' => $this->resource->userName ?? 'Аноним',
                ],
                'rating' => $this->resource->rating,
                'comment' => $this->resource->comment,
                'likes_count' => $this->resource->likesCount,
                'is_liked' => $this->resource->isLiked,
                'created_at' => $this->resource->createdAt,
                'replies' => $this->resource->replies
                    ? array_map(fn (ReviewDto $reply) => [
                        'id' => $reply->id,
                        'user' => [
                            'id' => $reply->userId,
                            'name' => $reply->userName ?? 'Аноним',
                        ],
                        'rating' => $reply->rating,
                        'comment' => $reply->comment,
                        'likes_count' => $reply->likesCount,
                        'is_liked' => $reply->isLiked,
                        'created_at' => $reply->createdAt,
                    ], $this->resource->replies)
                    : [],
            ];
        }

        return parent::toArray($request);
    }
}
