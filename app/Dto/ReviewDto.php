<?php

namespace App\Dto;

final readonly class ReviewDto extends BaseDto
{
    /**
     * @param array<int, ReviewDto> $replies
     */
    public function __construct(
        public int $id = 0,
        public int $userId = 0,
        public int $productId = 0,
        public ?int $parentId = null,
        public ?int $rating = null,
        public ?string $comment = null,
        public int $likesCount = 0,
        public bool $isLiked = false,
        public array $replies = [],
    ) {}
}
