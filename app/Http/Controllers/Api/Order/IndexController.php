<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class IndexController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    #[OA\Get(
        path: '/api/orders',
        summary: 'Get user orders',
        description: 'Retrieves all orders for the authenticated user.',
        tags: ['Orders'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of user orders',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderResource')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orders = $this->orderRepository->getByUserId($user->id);

        return OrderResource::collection($orders)->response();
    }
}
