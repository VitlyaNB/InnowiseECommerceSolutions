<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Repositories\UserRepository;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Throwable;

final class StoreController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private UserRepository $userRepository
    ) {}

    public function __invoke(StoreOrderRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $userDto = $this->userRepository->findById($userId);

            if (! $userDto) {
                return response()->json(['message' => 'Пользователь не найден'], 401);
            }

            $order = $this->orderService->createOrder($userDto, $request->toDto());

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
