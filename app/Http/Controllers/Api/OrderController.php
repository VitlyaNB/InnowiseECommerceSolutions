<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        try {
            $order = $this->orderService->createOrder($request->user());

            return response()->json([
                'message' => 'Заказ успешно оформлен!',
                'order' => $order,
                'new_balance' => $request->user()->fresh()->balance
            ], 201);

        } catch (\Exception $e) {
            Log::error('Ошибка создания заказа: ' . $e->getMessage());
            $status = in_array($e->getMessage(), ['Недостаточно средств', 'Корзина пуста']) ? 400 : 500;

            return response()->json([
                'message' => $e->getMessage()
            ], $status);
        }
    }
}
