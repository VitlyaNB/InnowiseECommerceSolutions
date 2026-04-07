<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ClearController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    #[OA\Delete(
        path: '/api/cart',
        summary: 'Clear all items from the cart',
        description: 'Removes all items from the cart for the authenticated user.',
        tags: ['Cart'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Cart cleared'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $this->cartService->clearCart($userId);

        return response()->json(['message' => 'Cart cleared']);
    }
}
