<?php

namespace App\Dto;

final readonly class ExternalCategoryItemDto extends BaseDto
{
    public function __construct(
        public string $name,
    ) {}
}
