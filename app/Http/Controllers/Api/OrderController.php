<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\User;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    #[OA\Get(
        path: '/api/orders',
        summary: 'Get the authenticated user\'s orders',
        tags: ['Orders'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of orders',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
                                    new OA\Property(property: 'status', type: 'string', example: 'paid'),
                                    new OA\Property(property: 'shipping_address', type: 'string', example: '123 Main St'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orders = $user->orders()->with('items.product.images')->latest()->get();

        return OrderResource::collection($orders)->response();
    }

    #[OA\Post(
        path: '/api/orders',
        summary: 'Place a new order from selected cart items',
        tags: ['Orders'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['selected_item_ids', 'shipping_address'],
                properties: [
                    new OA\Property(
                        property: 'selected_item_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 3]
                    ),
                    new OA\Property(property: 'shipping_address', type: 'string', example: '123 Main St, New York, NY 10001'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order placed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Заказ успешно оформлен'),
                        new OA\Property(property: 'order', ref: '#/components/schemas/OrderResource'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request (insufficient funds, out of stock, etc.)'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreOrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array<int, int> $selectedItemIds */
        $selectedItemIds = $request->input('selected_item_ids');

        /** @var string $shippingAddress */
        $shippingAddress = $request->input('shipping_address');

        try {
            $order = $this->orderService->createOrder($user, $selectedItemIds, $shippingAddress);

            return (new OrderResource($order->load(['items.product.images'])))
                ->additional(['message' => 'Заказ успешно оформлен'])
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
