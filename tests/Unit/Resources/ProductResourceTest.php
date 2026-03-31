<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_has_required_fields(): void
    {
        $mockFileService = $this->createMock(FileServiceInterface::class);
        $this->app->instance(FileServiceInterface::class, $mockFileService);

        $category = Category::factory()->createOne(['name' => 'Electronics']);
        $product = Product::factory()->createOne([
            'name' => 'Test Product',
            'price' => 100.50,
            'category_id' => $category->id,
            'quantity' => 10,
        ]);

        $resource = (new ProductResource($product->fresh()))->toArray(request());

        $this->assertEquals('Test Product', $resource['name']);
        $this->assertEquals(100.50, $resource['price']);
        $this->assertEquals('Electronics', $resource['category_name']);
        $this->assertArrayHasKey('id', $resource);
    }
}
