<?php

namespace App\Jobs;

use App\Mail\OrderPaidMail;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(
        OrderRepositoryInterface $orderRepository,
        UserRepositoryInterface $userRepository,
    ): void {
        $order = $orderRepository->findByIdWithItems($this->orderId);

        if (! $order) {
            return;
        }

        $user = $userRepository->findById($order->userId);

        if (! $user) {
            return;
        }

        Mail::to($user->email)->send(new OrderPaidMail($order->id, $order->totalAmount));
    }
}
