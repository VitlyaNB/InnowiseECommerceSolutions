<?php

namespace App\Dto;

final readonly class ExternalCategorySyncResultDto extends BaseDto
{
    public function __construct(
        public bool $status,
        public string $message,
        public int $synced,
        public int $httpStatus,
    ) {}
}
