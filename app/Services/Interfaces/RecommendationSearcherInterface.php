<?php

namespace App\Services\Interfaces;

use App\Dto\ProductDto;

interface RecommendationSearcherInterface
{
    /** @return array<int, int> */
    public function searchSimilarIds(ProductDto $product, int $limit): array;
}
