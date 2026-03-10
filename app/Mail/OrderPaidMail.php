<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order
    ) {}

    public function build(): self
    {
        return $this->subject('Ваш заказ подтвержден')
            ->view('emails.order_paid')
            ->with([
                'order' => $this->order,
            ]);
    }
}
