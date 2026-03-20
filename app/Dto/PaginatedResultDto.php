<?php

namespace App\Dto;

final readonly class PaginatedResultDto extends BaseDto
{
    /**
     * @param  array<int, mixed>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {}
}
