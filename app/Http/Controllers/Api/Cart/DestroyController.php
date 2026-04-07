<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class DestroyController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    #[OA\Delete(
        path: '/api/cart/{id}',
        summary: 'Remove a specific item from the cart',
        description: 'Removes an item from the cart for the authenticated user.',
        tags: ['Cart'],
        security: [['bearerAuth' => []]],
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
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Cart item not found'),
        ]
    )]
    public function __invoke(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;
        $removed = $this->cartService->removeItem($id, $userId);

        if (! $removed) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json(['message' => 'Item removed from cart']);
    }
}
