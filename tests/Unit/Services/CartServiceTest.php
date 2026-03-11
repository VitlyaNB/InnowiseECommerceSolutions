<?php

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Tests\TestCase;
use Mockery;

class CartServiceTest extends TestCase
{
    public function test_calculate_totals_returns_correct_values()
    {
        $cartRepo = Mockery::mock(CartItemRepositoryInterface::class);
        $service = new CartService($cartRepo);

        $product1 = new Product(['price' => 100]);
        $item1 = new CartItem(['quantity' => 2]);
        $item1->setRelation('product', $product1);

        $product2 = new Product(['price' => 50]);
        $item2 = new CartItem(['quantity' => 1]);
        $item2->setRelation('product', $product2);

        $items = collect([$item1, $item2]);

        $totals = $service->calculateTotals($items);

        $this->assertEquals(250, $totals['total']);
        $this->assertEquals(250, $totals['subtotal']);
        $this->assertEquals(0, $totals['tax']);
    }
}
