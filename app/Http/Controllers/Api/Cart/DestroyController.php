<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CartSessionResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

final class DestroyController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSessionResolver $sessionService
    ) {}

    #[OA\Delete(
        path: '/api/cart/{id}',
        summary: 'Remove a specific item from the cart',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Cart item ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item removed from cart'),
            new OA\Response(response: 404, description: 'Cart item not found'),
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $userIdRaw = Auth::id();
        $userId = $userIdRaw !== null ? (int) $userIdRaw : null;
        $sessionId = $userId !== null ? null : $this->sessionService->resolveSessionId($request);

        $removed = $this->cartService->removeItem($id, $userId, $sessionId);

        if (! $removed) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json(['message' => 'Item removed from cart']);
    }
}
