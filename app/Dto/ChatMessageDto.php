<?php

namespace App\Dto;

final readonly class ChatMessageDto extends BaseDto
{
    public function __construct(
        public int $id,
        public int $chatId,
        public int $userId,
        public string $message,
        public bool $isRead,
        public ?string $createdAt = null,
        public ?string $userName = null,
        public ?string $userRole = null,
    ) {}
}
