<?php


namespace Tests\Feature\Jobs;

use App\Jobs\SendOrderConfirmationJob;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOrderConfirmationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_email_to_user()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'customer@example.com']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 500
        ]);

        $job = new SendOrderConfirmationJob($order);
        $job->handle();

        Mail::assertSent(OrderPaidMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
