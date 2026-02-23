<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    // Внедряем интеррфейс наш а не сам класс репозитория
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getCatalog(): Collection
    {
        return $this->productRepository->getAllActive();
    }

    public function createProduct(ProductDTO $dto): Product
    {
        // бизнес-логика (расчет скидок, логирование, отправка писем)

        return $this->productRepository->create([
            'name' => $dto->name,
            'price' => $dto->price,
            'category_id' => $dto->category_id,
            'description' => $dto->description,
            'old_price' => $dto->old_price,
            'quantity' => $dto->quantity,
            'is_active' => true,
        ]);
    }
}
