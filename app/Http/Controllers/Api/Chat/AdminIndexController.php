<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class AdminIndexController extends Controller
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    #[OA\Get(
        path: '/api/admin/chats',
        summary: 'Get all chats (Admin)',
        description: 'Retrieves all chat conversations with messages. Admin only.',
        tags: ['Chats'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of all chats',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $chats = $this->chatRepository->getAllWithMessages();

        return response()->json(array_map(static fn ($chatDto) => $chatDto->toArray(), $chats));
    }
}
