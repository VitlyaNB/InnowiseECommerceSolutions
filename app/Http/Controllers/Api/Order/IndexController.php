<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IndexController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orders = $this->orderRepository->getByUserId($user->id);

        return OrderResource::collection($orders)->response();
    }
}
