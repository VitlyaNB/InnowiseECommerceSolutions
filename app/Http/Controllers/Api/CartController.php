<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use App\DTO\CartItemDTO;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
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
                                    new OA\Property(property: 'product_id', type: 'integer', example: 5),
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
    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart();

        return response()->json([
            'items'  => CartResource::collection($cart['items'])->resolve(),
            'totals' => $cart['totals'],
        ]);
    }

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
    public function store(AddToCartRequest $request): JsonResponse
    {
        $dto  = CartItemDTO::fromRequest($request);
        $item = $this->cartService->addToCart($dto);

        return (new CartResource($item))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/cart/{id}',
        summary: 'Update the quantity of a cart item',
        description: 'Updates quantity. If quantity is 0, the item is removed from the cart.',
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 0, example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cart item updated or removed'),
            new OA\Response(response: 404, description: 'Cart item not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $quantity = $request->integer('quantity');
        $item     = $this->cartService->updateQuantity($id, $quantity);

        if (!$item) {
            return response()->json([
                'message' => $quantity < 1
                    ? 'Item removed from cart'
                    : 'Cart item not found',
            ], $quantity < 1 ? 200 : 404);
        }

        return response()->json(new CartResource($item));
    }

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
    public function destroy(int $id): JsonResponse
    {
        $removed = $this->cartService->removeItem($id);

        if (!$removed) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json(['message' => 'Item removed from cart']);
    }

    #[OA\Delete(
        path: '/api/cart',
        summary: 'Clear all items from the cart',
        tags: ['Cart'],
        responses: [
            new OA\Response(response: 200, description: 'Cart cleared'),
        ]
    )]
    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return response()->json(['message' => 'Cart cleared']);
    }
}
