<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly float $totalAmount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Paid Success',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_paid',
            with: [
                'orderId' => $this->orderId,
                'totalAmount' => $this->totalAmount,
            ],
        );
    }
}
