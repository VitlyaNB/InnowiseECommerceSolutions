<?php

namespace App\Http\Controllers\Api\Order;

use App\Dto\UserDto;
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

            $userDto = new UserDto(
                id: $user->id,
                name: $user->name,
                email: $user->email,
                role: $user->role,
                balance: (float) $user->balance,
            );

            $order = $this->orderService->createOrder($userDto, $request->toDto());
            $freshUser = $user->fresh();
            $newBalance = $freshUser ? (float) $freshUser->balance : (float) $user->balance;

            return response()->json([
                'message' => 'Заказ успешно оформлен',
                'order' => $order->toArray(),
                'new_balance' => $newBalance,
            ], 201);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
