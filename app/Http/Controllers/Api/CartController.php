<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Services\CartService;
use App\DTO\CartItemDTO;
use Illuminate\Http\JsonResponse;


class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart();

        return response()->json([
            'items' => CartResource::collection($cart['items'])->resolve(),
            'totals' => $cart['totals'],
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $dto = CartItemDTO::fromRequest($request);
        $item = $this->cartService->addToCart($dto);

        return (new CartResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $quantity = $request->integer('quantity');
        $item = $this->cartService->updateQuantity($id, $quantity);

        if (!$item) {
            return response()->json([
                'message' => $quantity < 1
                    ? 'Item removed from cart'
                    : 'Cart item not found',
            ], $quantity < 1 ? 200 : 404);
        }

        return response()->json(new CartResource($item));
    }

    public function destroy(int $id): JsonResponse
    {
        $removed = $this->cartService->removeItem($id);

        if (!$removed) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return response()->json(['message' => 'Cart cleared']);
    }
}
