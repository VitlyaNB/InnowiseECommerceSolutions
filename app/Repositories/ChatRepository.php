<?php

namespace App\Repositories;

use App\Dto\ChatDto;
use App\Dto\ChatMessageDto;
use App\Models\Chat;
use App\Models\Message;
use App\Repositories\Interfaces\ChatRepositoryInterface;

final class ChatRepository implements ChatRepositoryInterface
{
    /** @return array<int, ChatDto> */
    public function getAllWithMessages(): array
    {
        return Chat::query()
            ->with(['user', 'messages.user'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Chat $chat): ChatDto => $this->mapChatToDto($chat))
            ->all();
    }

    public function findByUserIdWithMessages(int $userId): ?ChatDto
    {
        /** @var Chat|null $chat */
        $chat = Chat::query()
            ->with(['user', 'messages.user'])
            ->where('user_id', $userId)
            ->first();

        return $chat ? $this->mapChatToDto($chat) : null;
    }

    public function findByIdWithMessages(int $chatId): ?ChatDto
    {
        /** @var Chat|null $chat */
        $chat = Chat::query()
            ->with(['user', 'messages.user'])
            ->find($chatId);

        return $chat ? $this->mapChatToDto($chat) : null;
    }

    public function createByUserId(int $userId): ChatDto
    {
        /** @var Chat $chat */
        $chat = Chat::query()->create([
            'user_id' => $userId,
            'last_message_at' => now(),
        ]);

        /** @var Chat|null $reloaded */
        $reloaded = Chat::query()->with(['user', 'messages.user'])->find($chat->id);

        if (!$reloaded) {
            return new ChatDto(id: $chat->id, userId: $chat->user_id);
        }

        return $this->mapChatToDto($reloaded);
    }

    public function markMessagesAsReadExceptUser(int $chatId, int $userId): void
    {
        Message::query()
            ->where('chat_id', $chatId)
            ->where('user_id', '!=', $userId)
            ->update(['is_read' => true]);
    }

    public function createMessage(int $chatId, int $userId, string $message): ChatMessageDto
    {
        /** @var Message $model */
        $model = Message::query()->create([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'message' => $message,
        ]);

        /** @var Message|null $reloaded */
        $reloaded = Message::query()
            ->with('user')
            ->find($model->id);

        return $this->mapMessageToDto($reloaded ?? $model);
    }

    public function findMessageByIdWithUser(int $messageId): ?ChatMessageDto
    {
        /** @var Message|null $message */
        $message = Message::query()
            ->with('user')
            ->find($messageId);

        return $message ? $this->mapMessageToDto($message) : null;
    }

    public function updateLastMessageAt(int $chatId): void
    {
        Chat::query()
            ->where('id', $chatId)
            ->update(['last_message_at' => now()]);
    }

    public function hasAccess(int $chatId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) {
            return Chat::query()->where('id', $chatId)->exists();
        }

        return Chat::query()
            ->where('id', $chatId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function existsById(int $chatId): bool
    {
        return Chat::query()
            ->where('id', $chatId)
            ->exists();
    }

    private function mapChatToDto(Chat $chat): ChatDto
    {
        $messages = $chat->relationLoaded('messages')
            ? $chat->messages
                ->sortBy('created_at')
                ->map(fn (Message $message): ChatMessageDto => $this->mapMessageToDto($message))
                ->values()
                ->all()
            : [];

        $lastMessageDto = $messages !== [] ? $messages[array_key_last($messages)] : null;

        return new ChatDto(
            id: (int) $chat->id,
            userId: (int) $chat->user_id,
            userName: $chat->user?->name,
            userEmail: $chat->user?->email,
            lastMessageAt: $chat->last_message_at?->toDateTimeString(),
            messages: $messages,
            lastMessage: $lastMessageDto instanceof ChatMessageDto ? $lastMessageDto : null,
        );
    }

    private function mapMessageToDto(Message $message): ChatMessageDto
    {
        return new ChatMessageDto(
            id: (int) $message->id,
            chatId: (int) $message->chat_id,
            userId: (int) $message->user_id,
            message: (string) $message->message,
            isRead: (bool) $message->is_read,
            createdAt: $message->created_at?->toDateTimeString(),
            userName: $message->user?->name,
            userRole: $message->user?->role,
        );
    }
}
