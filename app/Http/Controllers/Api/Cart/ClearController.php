<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Support\CartSessionResolver;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClearController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSessionResolver $sessionService
    ) {}

    #[OA\Delete(
        path: '/api/cart',
        summary: 'Clear all items from the cart',
        tags: ['Cart'],
        responses: [
            new OA\Response(response: 200, description: 'Cart cleared'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $userId ? null : $this->sessionService->resolveSessionId($request);

        $this->cartService->clearCart($userId, $sessionId);

        return response()->json(['message' => 'Cart cleared']);
    }
}
