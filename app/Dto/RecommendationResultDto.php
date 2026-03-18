<?php

namespace App\Dto;

final readonly class RecommendationResultDto extends BaseDto
{
    /**
     * @param array<int, ProductDto> $alsoBought
     * @param array<int, ProductDto> $similar
     * @param array<int, ProductDto> $recentlyViewed
     */
    public function __construct(
        public array $alsoBought,
        public array $similar,
        public array $recentlyViewed,
    ) {}
}
