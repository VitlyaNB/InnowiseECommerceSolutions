<?php

namespace Tests\Unit\Repositories;

use App\Models\Product;
use App\Models\ProductView;
use App\Repositories\ProductViewRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductViewRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductViewRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductViewRepository();
    }

    public function test_it_can_record_a_view()
    {
        $product = Product::factory()->create();

        $this->repository->recordView(null, 'test-session', $product->id);

        $this->assertDatabaseHas('product_views', [
            'product_id' => $product->id,
            'session_id' => 'test-session'
        ]);
    }
}
