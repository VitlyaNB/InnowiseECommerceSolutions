<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopUpBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_top_up_balance()
    {
        $user = User::factory()->create(['balance' => 100]);

        $response = $this->actingAs($user)->postJson('/api/wallet/top-up', [
            'amount' => 150,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(250, $user->fresh()->balance);
    }

    public function test_top_up_requires_positive_amount()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/wallet/top-up', [
            'amount' => -50,
        ]);

        $response->assertStatus(422);
    }
}
