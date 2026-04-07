<?php

namespace App\Services\Interfaces;

use App\Dto\ProductSearchQueryDto;
use App\Dto\ProductSearchResultDto;

interface ProductSearcherInterface
{
    public function search(ProductSearchQueryDto $queryDto): ProductSearchResultDto;
}
