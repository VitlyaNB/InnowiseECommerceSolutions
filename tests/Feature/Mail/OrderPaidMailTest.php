<?php

namespace Tests\Feature\Mail;

use App\Mail\OrderPaidMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaidMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_contains_order_details(): void
    {
        $mailable = new OrderPaidMail(1, 123.45);

        $mailable->assertSeeInHtml('1');
        $mailable->assertSeeInHtml('123.45');
        $this->assertEquals('Order Paid Success', $mailable->envelope()->subject);
    }
}
