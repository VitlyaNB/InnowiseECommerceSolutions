<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function getCatalog()
    {
        return $this->productRepository->getAllActive();
    }
}
