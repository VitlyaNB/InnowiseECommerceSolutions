<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}


    public function getAllProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage);
    }

    /**
     * Найти конкретный товар.
     * @throws ModelNotFoundException
     */
    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new ModelNotFoundException("Product with ID {$id} not found.");
        }

        return $product;
    }

    /**
     * Создаём новый товар.
     */
    public function createProduct(ProductDTO $data): Product
    {
        return $this->productRepository->create($data);
    }

    /**
     * Обновляем товар
     */
    public function updateProduct(int $id, ProductDTO $data): Product
    {
        $updated = $this->productRepository->update($id, $data);

        if (!$updated) {
            throw new \RuntimeException("Failed to update product with ID {$id}.");
        }

        return $this->getProductById($id);
    }

    /**
     * Удалить товар.
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new ModelNotFoundException("Product with ID {$id} not found.");
        }

        return $this->productRepository->delete($id);
    }
}
