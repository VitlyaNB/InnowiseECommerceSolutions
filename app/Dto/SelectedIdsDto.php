<?php

namespace App\Dto;

final readonly class SelectedIdsDto extends BaseDto
{
    /**
     * @param  array<int, int>  $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
