<?php

namespace App\Dto;

final readonly class ChatDto extends BaseDto
{
    /**
     * @param  array<int, ChatMessageDto>  $messages
     */
    public function __construct(
        public int $id,
        public int $userId,
        public ?string $userName = null,
        public ?string $userEmail = null,
        public ?string $lastMessageAt = null,
        public array $messages = [],
        public ?ChatMessageDto $lastMessage = null,
    ) {}
}
