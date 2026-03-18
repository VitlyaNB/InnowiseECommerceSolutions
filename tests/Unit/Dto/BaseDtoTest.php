<?php

namespace Tests\Unit\Dto;

use App\Dto\BaseDto;
use Tests\TestCase;

class BaseDtoTest extends TestCase
{
    public function test_to_array_returns_public_properties(): void
    {
        $dto = new readonly class ('value') extends BaseDto {
            public function __construct(
                public string $field,
            ) {}
        };

        $this->assertSame(['field' => 'value'], $dto->toArray());
    }
}
