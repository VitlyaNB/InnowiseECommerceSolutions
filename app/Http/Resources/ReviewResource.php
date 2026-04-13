<?php

namespace App\Http\Resources;

use App\Dto\ReviewDto;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (! $this->resource instanceof ReviewDto) {
            return [];
        }

        $replies = [];
        if (! empty($this->resource->replies)) {
            foreach ($this->resource->replies as $reply) {
                $replies[] = $this->formatReply($reply);
            }
        }

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
            'replies' => $replies,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReply(ReviewDto $reply): array
    {
        return [
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
        ];
    }
}
