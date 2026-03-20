<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Throwable;

final class StoreController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function __invoke(StoreOrderRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Необходима авторизация',
            ], 401);
        }

        try {
            $order = $this->orderService->createOrder($user->id, $request->toDto());

            return response()->json([
                'message' => 'Заказ успешно оформлен',
                'order' => $order->toArray(),
                'new_balance' => $user->fresh()->balance ?? null,
            ], 201);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
