<?php

namespace App\Dto;

final readonly class ExternalCategorySyncResultDto extends BaseDto
{
    public function __construct(
        public bool $ok,
        public string $message,
        public int $synced,
        public int $status,
    ) {}
}
