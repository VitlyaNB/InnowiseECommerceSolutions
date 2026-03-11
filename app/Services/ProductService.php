<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\DTO\UploadImageDTO;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected FileService $fileService
    ) {}

    /** 
     * @param array<string, mixed> $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<(int|string), Product> 
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->productRepository->getAll($filters, $perPage);
    }

    /** @return Collection<int, Product> */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->productRepository->getByCategory($categoryId);
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->getById($id);
    }

    public function createProduct(ProductDTO $dto): Product
    {
        /** @var Product $finalProduct */
        $finalProduct = DB::transaction(function () use ($dto) {
            $productData = [
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'old_price' => $dto->old_price,
                'quantity' => $dto->quantity,
                'category_id' => $dto->category_id,
            ];

            $product = $this->productRepository->create($productData);

            $this->handleImages($product, $dto->images);

            return $product->load('images');
        });
        
        return $finalProduct;
    }

    public function updateProduct(int $id, ProductDTO $dto): Product
    {
        /** @var Product $finalProduct */
        $finalProduct = DB::transaction(function () use ($id, $dto) {
            $product = $this->productRepository->getById($id);

            if (!$product) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Product with ID {$id} not found.");
            }

            $productData = array_filter([
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'old_price' => $dto->old_price,
                'quantity' => $dto->quantity,
                'category_id' => $dto->category_id,
            ], fn ($value) => !is_null($value));

            $this->productRepository->update($product, $productData);

            if (!empty($dto->images)) {
                $this->deleteProductImages($product);
                $this->handleImages($product, $dto->images);
            }

            return $product->fresh('images') ?? $product;
        });
        
        return $finalProduct;
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->getById($id);

        if (!$product) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Product with ID {$id} not found.");
        }

        $this->deleteProductImages($product);

        $this->productRepository->delete($product);
    }

    /** @param array<int, \Illuminate\Http\UploadedFile> $images */
    private function handleImages(Product $product, ?array $images): void
    {
        if (empty($images)) {
            return;
        }

        /** @var string $disk */
        $disk = config('filesystems.media_disk', 's3');

        foreach ($images as $file) {
            $dto = new UploadImageDTO($file, 'products', $disk);
            $pathOrUrl = $this->fileService->upload($dto);
            $product->images()->create(['image_path' => $pathOrUrl]);
        }
    }

    private function deleteProductImages(Product $product): void
    {
        /** @var string|null $disk */
        $disk = config('filesystems.media_disk', 's3');

        /** @var ProductImage $img */
        foreach ($product->images as $img) {
            $img->delete();
        }
    }
}
