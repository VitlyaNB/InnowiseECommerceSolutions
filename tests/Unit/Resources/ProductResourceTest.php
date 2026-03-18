<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_has_required_fields()
    {
        $mockFileService = $this->createMock(FileService::class);
        $this->app->instance(FileService::class, $mockFileService);

        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.50,
            'category_id' => $category->id,
            'quantity' => 10
        ]);

        $resource = (new ProductResource($product->fresh()))->toArray(request());

        $this->assertEquals('Test Product', $resource['name']);
        $this->assertEquals(100.50, $resource['price']);
        $this->assertEquals('Electronics', $resource['category_name']);
        $this->assertArrayHasKey('id', $resource);
    }
}
