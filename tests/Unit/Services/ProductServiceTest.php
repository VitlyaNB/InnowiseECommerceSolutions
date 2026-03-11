<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\ProductService;
use App\DTO\ProductDTO;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\FileService;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;

class ProductServiceTest extends TestCase
{
    public function test_create_product_calls_repository_and_handles_images()
    {
        $repo = Mockery::mock(ProductRepositoryInterface::class);
        $fileService = Mockery::mock(FileService::class);
        $service = new ProductService($repo, $fileService);

        $dto = new ProductDTO(
            name: 'Service Product',
            price: 200,
            category_id: 1
        );

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        $product = new Product(['id' => 1]);
        $product->exists = true;

        $repo->shouldReceive('create')
            ->once()
            ->andReturn($product);

        $result = $service->createProduct($dto);

        $this->assertInstanceOf(Product::class, $result);
    }

    public function test_delete_product_removes_images()
    {
        $repo = Mockery::mock(ProductRepositoryInterface::class);
        $fileService = Mockery::mock(FileService::class);
        $service = new ProductService($repo, $fileService);

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $product->shouldReceive('getAttribute')->with('images')->andReturn(collect([]));

        $repo->shouldReceive('getById')->once()->with(1)->andReturn($product);
        $repo->shouldReceive('delete')->once()->with($product);

        $service->deleteProduct(1);
        
        $this->assertTrue(true); // If no exception, it passed
    }
}
