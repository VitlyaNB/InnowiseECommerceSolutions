<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\DTO\UploadImageDTO;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected FileService $fileService
    ) {}

    public function getAllProducts(int $perPage = 15)
    {
        return $this->productRepository->getAll($perPage);
    }

    public function getProductsByCategory(int $categoryId)
    {
        return $this->productRepository->getByCategory($categoryId);
    }

    public function getProductById(int $id)
    {
        return $this->productRepository->getById($id);
    }

    public function createProduct(ProductDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $productData = [
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'quantity' => $dto->quantity,
                'category_id' => $dto->category_id,
            ];

            $product = $this->productRepository->create($productData);

            $this->handleImages($product, $dto->images);

            return $product->load('images');
        });
    }

    public function updateProduct(int $id, ProductDTO $dto)
    {
        return DB::transaction(function () use ($id, $dto) {
            $product = $this->productRepository->getById($id);

            $productData = [
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'quantity' => $dto->quantity,
                'category_id' => $dto->category_id,
            ];

            $this->productRepository->update($product, $productData);

            if (!empty($dto->images)) {
                $this->deleteProductImages($product);
                $this->handleImages($product, $dto->images);
            }

            return $product->fresh('images');
        });
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->getById($id);

        $this->deleteProductImages($product);

        $this->productRepository->delete($product);
    }

    private function handleImages($product, ?array $images): void
    {
        if (empty($images)) {
            return;
        }

        $disk = config('filesystems.media_disk', 'public');

        foreach ($images as $file) {
            $dto = new UploadImageDTO($file, 'products', $disk);
            $pathOrUrl = $this->fileService->upload($dto);
            $product->images()->create(['image_path' => $pathOrUrl]);
        }
    }

    private function deleteProductImages($product): void
    {
        $disk = config('filesystems.media_disk', 'public');

        foreach ($product->images as $img) {
            $this->fileService->delete($img->image_path, $disk);
            $img->delete();
        }
    }
}
