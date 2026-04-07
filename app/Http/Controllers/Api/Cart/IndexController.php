<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class IndexController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    #[OA\Get(
        path: '/api/cart',
        summary: 'Get current cart contents',
        description: 'Returns cart items for the authenticated user along with subtotal, tax, and total.',
        tags: ['Cart'],
        security: [['bearerAuth' => []]],
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
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;
        $cartDto = $this->cartService->getCart($userId);

        return response()->json([
            'items' => CartResource::collection($cartDto->items)->resolve(),
            'totals' => $cartDto->totals->toArray(),
        ]);
    }
}
