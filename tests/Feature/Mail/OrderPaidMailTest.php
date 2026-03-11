<?php


namespace Tests\Feature\Mail;

use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaidMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_contains_order_details()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 123.45
        ]);

        $mailable = new OrderPaidMail($order);

        $mailable->assertSeeInHtml((string)$order->id);
        $mailable->assertSeeInHtml('123.45');
        $this->assertEquals('Order Paid Success', $mailable->envelope()->subject);
    }
}
