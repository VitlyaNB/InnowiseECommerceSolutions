<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartResource;
use App\Http\Support\CartSessionResolver;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class StoreController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSessionResolver $sessionService
    ) {}

    #[OA\Post(
        path: '/api/cart',
        summary: 'Add a product to the cart',
        description: 'Adds the given product to the cart. If the product already exists in the cart, the quantity is incremented.',
        tags: ['Cart'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 5),
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Item added to cart',
                content: new OA\JsonContent(ref: '#/components/schemas/CartResource')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(AddToCartRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $userId ? null : $this->sessionService->resolveSessionId($request);

        $item = $this->cartService->addToCart($request->toDto(), $userId, $sessionId);

        return (new CartResource($item))
            ->response()
            ->setStatusCode(201);
    }
}
