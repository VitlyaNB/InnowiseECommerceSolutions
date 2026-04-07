<?php

namespace App\Dto;

final readonly class ExternalCategoryApiResponseDto extends BaseDto
{
    /**
     * @param  array<int, ExternalCategoryItemDto>  $categories
     */
    public function __construct(
        public array $categories,
    ) {}
}
