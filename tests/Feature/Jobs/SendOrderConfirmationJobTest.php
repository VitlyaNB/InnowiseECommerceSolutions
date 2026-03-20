<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendOrderConfirmationJob;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class SendOrderConfirmationJobTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepositoryInterface|MockInterface $orderRepository;

    private UserRepositoryInterface|MockInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->orderRepository = $this->app->make(OrderRepositoryInterface::class);
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_it_sends_email_to_user(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 500,
        ]);

        $job = new SendOrderConfirmationJob($order->id);
        $job->handle($this->orderRepository, $this->userRepository);

        Mail::assertSent(OrderPaidMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
