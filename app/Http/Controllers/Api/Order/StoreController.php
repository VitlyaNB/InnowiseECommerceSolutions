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
        try {
            /** @var User $user */
            $user = auth()->user();

            if (! $user) {
                return response()->json(['message' => 'Требуется авторизация'], 401);
            }

            $order = $this->orderService->createOrderFromUser($user, $request->toDto());

            return response()->json([
                'message' => 'Заказ успешно оформлен',
                'order' => $order->toArray(),
            ], 201);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
