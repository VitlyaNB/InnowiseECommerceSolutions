<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $orders = $user->orders()->with('items.product.images')->latest()->get();

        return response()->json([
            'data' => $orders
        ]);
    }

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

            return response()->json([
                'message' => 'Заказ успешно оформлен',
                'order' => $order->fresh(['items.product.images'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
