<?php

namespace Tests\Unit\Services;

use App\Dto\ProductDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\FileServiceInterface;
use App\Services\Interfaces\ProductSearcherInterface;
use App\Services\ProductService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $repo;

    private FileServiceInterface&MockObject $fileService;

    private TransactionManagerInterface&MockObject $transactionManager;

    private ProductSearcherInterface&MockObject $productSearcher;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->createMock(ProductRepositoryInterface::class);
        $this->fileService = $this->createMock(FileServiceInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->productSearcher = $this->createMock(ProductSearcherInterface::class);
        $this->service = new ProductService($this->repo, $this->fileService, $this->transactionManager, $this->productSearcher);
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

        $this->repo
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($created);

        $this->repo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($created);

        $this->transactionManager
            ->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());

        $result = $this->service->createProduct($dto);

        $this->assertNotNull($result);
        $this->assertSame(1, $result->id);
    }

    public function test_delete_product_removes_images(): void
    {
        $product = new ProductDto(id: 1, name: 'Service Product');
        $this->repo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        $this->repo
            ->expects($this->once())
            ->method('deleteImages')
            ->with(1);

        $this->repo
            ->expects($this->once())
            ->method('deleteOrderItemsByProductId')
            ->with(1);

        $this->repo
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $this->transactionManager
            ->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());

        $this->service->deleteProduct(1);
    }
}
