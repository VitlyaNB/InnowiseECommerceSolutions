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
        public readonly Order $order
    ) {}

    public function handle(): void
    {
        if (!$this->order->user) {
            return;
        }

        Mail::to($this->order->user->email)->send(new OrderPaidMail($this->order));
    }
}
