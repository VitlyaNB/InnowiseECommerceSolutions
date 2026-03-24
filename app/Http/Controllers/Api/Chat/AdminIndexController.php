<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\JsonResponse;

final class AdminIndexController extends Controller
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        $chats = $this->chatRepository->getAllWithMessages();

        return response()->json(array_map(static fn ($chatDto) => $chatDto->toArray(), $chats));
    }
}
