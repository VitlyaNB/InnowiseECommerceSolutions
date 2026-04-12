<?php

namespace App\Services;

use App\Dto\ChatDto;
use App\Dto\ChatMessageDto;
use App\Repositories\Interfaces\ChatRepositoryInterface;

final readonly class ChatService
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository
    ) {}

    public function getChat(int $chatId, int $userId): ?ChatDto
    {
        $this->chatRepository->markMessagesAsReadExceptUser($chatId, $userId);

        return $this->chatRepository->findByIdWithMessages($chatId);
    }

    public function startChat(int $userId): ChatDto
    {
        $chat = $this->chatRepository->findByUserIdWithMessages($userId);

        if ($chat !== null) {
            return $chat;
        }

        return $this->chatRepository->createByUserId($userId);
    }

    public function sendMessage(int $chatId, int $userId, string $message): ChatMessageDto
    {
        $createdMessage = $this->chatRepository->createMessage($chatId, $userId, $message);
        $this->chatRepository->updateLastMessageAt($chatId);

        $enriched = $this->chatRepository->findMessageByIdWithUser($createdMessage->id);

        return $enriched ?? $createdMessage;
    }

    public function hasAccess(int $chatId, int $userId, bool $isAdmin): bool
    {
        return $this->chatRepository->hasAccess($chatId, $userId, $isAdmin);
    }

    public function exists(int $chatId): bool
    {
        return $this->chatRepository->existsById($chatId);
    }
}
