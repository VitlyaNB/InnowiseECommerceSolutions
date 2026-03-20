<?php

namespace Tests\Feature\Mail;

use App\Dto\OrderDetailsDto;
use App\Mail\OrderPaidMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaidMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_contains_order_details()
    {
        $orderDto = new OrderDetailsDto(
            id: 1,
            userId: 1,
            totalAmount: 123.45,
            status: 'paid',
            shippingAddress: 'Test Street 1',
        );

        $mailable = new OrderPaidMail($orderDto);

        $mailable->assertSeeInHtml('1');
        $mailable->assertSeeInHtml('123.45');
        $this->assertEquals('Order Paid Success', $mailable->envelope()->subject);
    }
}
