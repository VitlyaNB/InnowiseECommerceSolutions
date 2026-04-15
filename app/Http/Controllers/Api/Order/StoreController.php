<?php

namespace App\Http\Controllers\Api\Order;

use App\Dto\UserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Throwable;

final class StoreController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    #[OA\Post(
        path: '/api/orders',
        summary: 'Create a new order',
        description: 'Places a new order using items from the user cart. Deducts payment from user balance.',
        tags: ['Orders'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order placed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order placed successfully'),
                        new OA\Property(property: 'order', type: 'object'),
                        new OA\Property(property: 'new_balance', type: 'number', format: 'float', example: 850.00),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 400, description: 'Order creation failed'),
        ]
    )]
    public function __invoke(StoreOrderRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $userDto = new UserDto(
                id: $user->id,
                name: $user->name,
                email: $user->email,
                role: $user->role,
                balance: (float) $user->balance,
            );

            $order = $this->orderService->createOrder($userDto, $request->toDto());
            $updatedUser = $this->userRepository->findById($user->id);
            $newBalance = $updatedUser !== null ? $updatedUser->balance : (float) $user->balance;

            return response()->json([
                'message' => 'Order placed successfully',
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
