<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Support\CartSessionResolver;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UpdateController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSessionResolver $sessionService
    ) {}

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
    public function __invoke(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $userId = $request->user()?->id;
        $sessionId = $userId ? null : $this->sessionService->resolveSessionId($request);

        $quantity = $request->toDto()->quantity;
        $item = $this->cartService->updateQuantity($id, $quantity, $userId, $sessionId);

        if (!$item) {
            return response()->json([
                'message' => $quantity < 1
                    ? 'Item removed from cart'
                    : 'Cart item not found',
            ], $quantity < 1 ? 200 : 404);
        }

        return response()->json(new CartResource($item));
    }
}
