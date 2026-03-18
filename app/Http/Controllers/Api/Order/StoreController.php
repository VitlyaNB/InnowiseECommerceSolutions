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
        /** @var User $user */
        $user = $request->user();

        try {
            $order = $this->orderService->createOrder($user->id, $request->toDto());

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
