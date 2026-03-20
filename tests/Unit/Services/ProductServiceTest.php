<?php

namespace Tests\Unit\Services;

use App\Dto\ProductDto;
use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\FileServiceInterface;
use App\Services\ProductService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface|MockInterface $repo;

    private FileServiceInterface|MockInterface $fileService;

    private TransactionManagerInterface|MockInterface $transactionManager;

    private ElasticsearchClientInterface|MockInterface $elastic;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(ProductRepositoryInterface::class);
        $this->fileService = Mockery::mock(FileServiceInterface::class);
        $this->transactionManager = Mockery::mock(TransactionManagerInterface::class);
        $this->elastic = Mockery::mock(ElasticsearchClientInterface::class);
        $this->service = new ProductService($this->repo, $this->fileService, $this->transactionManager, $this->elastic);
    }

    public function test_create_product_calls_repository(): void
    {
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

        $this->repo->shouldReceive('create')->once()->with($dto)->andReturn($created);
        $this->repo->shouldReceive('findById')->once()->with(1)->andReturn($created);

        $this->transactionManager->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $result = $this->service->createProduct($dto);

        $this->assertSame(1, $result->id);
    }

    public function test_delete_product_removes_images(): void
    {
        $product = new ProductDto(id: 1, name: 'Service Product');
        $this->repo->shouldReceive('findById')->once()->with(1)->andReturn($product);
        $this->repo->shouldReceive('deleteImages')->once()->with(1);
        $this->repo->shouldReceive('delete')->once()->with(1)->andReturnTrue();

        $this->service->deleteProduct(1);

        $this->assertTrue(true);
    }
}
