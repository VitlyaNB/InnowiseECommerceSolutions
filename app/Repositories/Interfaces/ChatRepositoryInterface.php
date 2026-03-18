<?php

namespace App\Repositories\Interfaces;

use App\Dto\ChatDto;
use App\Dto\ChatMessageDto;

interface ChatRepositoryInterface
{
    /** @return array<int, ChatDto> */
    public function getAllWithMessages(): array;

    public function findByUserIdWithMessages(int $userId): ?ChatDto;

    public function findByIdWithMessages(int $chatId): ?ChatDto;

    public function createByUserId(int $userId): ChatDto;

    public function markMessagesAsReadExceptUser(int $chatId, int $userId): void;

    public function createMessage(int $chatId, int $userId, string $message): ChatMessageDto;

    public function findMessageByIdWithUser(int $messageId): ?ChatMessageDto;

    public function updateLastMessageAt(int $chatId): void;

    public function hasAccess(int $chatId, int $userId, bool $isAdmin): bool;

    public function existsById(int $chatId): bool;
}
