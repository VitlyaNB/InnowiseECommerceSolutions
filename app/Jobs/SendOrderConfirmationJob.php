<?php

namespace App\Jobs;

use App\Mail\OrderPaidMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $orderId
    ) {}

    public function handle(): void
    {
        /** @var Order|null $order */
        $order = Order::query()->with('user')->find($this->orderId);
        if (!$order || !$order->user) {
            return;
        }

        Mail::to($order->user->email)->send(new OrderPaidMail($order));
    }
}
