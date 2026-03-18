<?php

namespace App\Dto;

final readonly class ExternalCategorySyncCommandDto extends BaseDto
{
    public function __construct(
        public bool $async = false,
    ) {}
}
