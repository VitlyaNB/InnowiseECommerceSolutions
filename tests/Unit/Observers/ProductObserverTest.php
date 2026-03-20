<?php

namespace Tests\Unit\Observers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProductObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_product_creation()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($message) => str_contains($message, 'Product created'));

        $category = Category::factory()->create();

        Product::create([
            'name' => 'Observer Test Product',
            'price' => 100,
            'quantity' => 10,
            'category_id' => $category->id,
            'description' => 'Description',
        ]);
    }
}
