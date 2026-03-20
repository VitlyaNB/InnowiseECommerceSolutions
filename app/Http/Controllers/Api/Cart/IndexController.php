<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Http\Support\CartSessionResolver;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

final class IndexController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSessionResolver $sessionService
    ) {}

    #[OA\Get(
        path: '/api/cart',
        summary: 'Get current cart contents',
        description: 'Returns cart items (identified by user ID for authenticated users, or session cookie for guests) along with subtotal, tax, and total.',
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'productId', type: 'integer', example: 5),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                    new OA\Property(property: 'product', type: 'object'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'totals',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 199.98),
                                new OA\Property(property: 'tax', type: 'number', format: 'float', example: 0.0),
                                new OA\Property(property: 'total', type: 'number', format: 'float', example: 199.98),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $userIdRaw = Auth::id();
        $userId = $userIdRaw !== null ? (int) $userIdRaw : null;
        $sessionId = $userId !== null ? null : $this->sessionService->resolveSessionId($request);

        $cartDto = $this->cartService->getCart($userId, $sessionId);

        return response()->json([
            'items' => CartResource::collection($cartDto->items)->resolve(),
            'totals' => $cartDto->totals->toArray(),
        ]);
    }
}
