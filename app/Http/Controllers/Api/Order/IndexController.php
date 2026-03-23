<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;

final class IndexController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        $orders = $this->orderRepository->getByUserId(auth()->id());

        return OrderResource::collection($orders)->response();
    }
}
