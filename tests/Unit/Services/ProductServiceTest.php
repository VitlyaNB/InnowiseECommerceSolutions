<?php

namespace Tests\Unit\Services;

use App\Dto\ProductDto;
use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\FileService;
use App\Services\ProductService;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_create_product_calls_repository(): void
    {
        $repo = Mockery::mock(ProductRepositoryInterface::class);
        $fileService = new FileService();
        $transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $elastic = Mockery::mock(ElasticsearchClientInterface::class);
        $service = new ProductService($repo, $fileService, $transactionManager, $elastic);

        $dto = new ProductDto(
            name: 'Service Product',
            price: 200.0,
            categoryId: 1,
            images: [],
        );

        $created = new ProductDto(
            id: 1,
            name: 'Service Product',
            price: 200.0,
            categoryId: 1,
            images: [],
        );

        $repo->shouldReceive('create')->once()->with($dto)->andReturn($created);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($created);

        $transactionManager->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $result = $service->createProduct($dto);

        $this->assertSame(1, $result->id);
    }

    public function test_delete_product_removes_images(): void
    {
        $repo = Mockery::mock(ProductRepositoryInterface::class);
        $fileService = new FileService();
        $transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $elastic = Mockery::mock(ElasticsearchClientInterface::class);
        $service = new ProductService($repo, $fileService, $transactionManager, $elastic);

        $product = new ProductDto(id: 1, name: 'Service Product');
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($product);
        $repo->shouldReceive('deleteImages')->once()->with(1);
        $repo->shouldReceive('delete')->once()->with(1)->andReturnTrue();

        $service->deleteProduct(1);

        $this->assertTrue(true);
    }
}
