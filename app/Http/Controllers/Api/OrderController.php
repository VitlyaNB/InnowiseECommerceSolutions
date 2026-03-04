<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product.images']) // Подгружаем товары и картинки
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            // Передаем список ID выбранных товаров
            $selectedIds = $request->validated('items');

            $order = $this->orderService->createOrder($request->user(), $selectedIds);

            return response()->json([
                'message' => 'Заказ успешно оформлен!',
                'order' => $order,
                'new_balance' => $request->user()->fresh()->balance
            ], 201);

        } catch (\Exception $e) {
            Log::error('Order failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
