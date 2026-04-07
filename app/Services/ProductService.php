<?php

namespace App\Services;

use App\Dto\ProductDto;
use App\Dto\ProductSearchQueryDto;
use App\Dto\ProductSearchResultDto;
use App\Dto\UploadImageDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\FileServiceInterface;
use App\Services\Interfaces\ProductSearcherInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileServiceInterface $fileService,
        private TransactionManagerInterface $transactionManager,
        private ProductSearcherInterface $productSearcher
    ) {}

    public function createProduct(ProductDto $dto): ?ProductDto
    {
        /** @var ProductDto|null $result */
        $result = $this->transactionManager->transaction(function () use ($dto): ProductDto|null {
            $productDto = $this->productRepository->create($dto);

            if ($productDto->id === null) {
                return null;
            }

            $this->handleImages($productDto->id, $dto->images);

            return $this->productRepository->findById($productDto->id);
        });

        return $result;
    }

    public function updateProduct(int $id, ProductDto $dto): ?ProductDto
    {
        /** @var ProductDto|null $result */
        $result = $this->transactionManager->transaction(function () use ($id, $dto): ProductDto|null {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                throw new ModelNotFoundException("Product with ID {$id} not found.");
            }

            $this->productRepository->update($id, $dto);

            if (! empty($dto->images)) {
                $this->productRepository->deleteImages($id);
                $this->handleImages($id, $dto->images);
            }

            return $this->productRepository->findById($id);
        });

        return $result;
    }

    public function deleteProduct(int $id): void
    {
        $this->transactionManager->transaction(function () use ($id): void {
            $product = $this->productRepository->findById($id);
            if (! $product) {
                throw new ModelNotFoundException("Product with ID {$id} not found.");
            }

            $this->productRepository->deleteImages($id);
            $this->productRepository->deleteOrderItemsByProductId($id);
            $this->productRepository->delete($id);
        });
    }

    public function search(ProductSearchQueryDto $queryDto): ProductSearchResultDto
    {
        return $this->productSearcher->search($queryDto);
    }

    /**
     * @param  array<int, string|UploadedFile>  $images
     */
    private function handleImages(int $productId, array $images): void
    {
        /** @var string $disk */
        $disk = config('filesystems.media_disk', 's3');

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $dto = new UploadImageDto($file, 'products', $disk);
            $pathOrUrl = $this->fileService->upload($dto);
            $this->productRepository->saveImage($productId, $pathOrUrl);
        }
    }
}
