<?php

namespace Tests\Unit\DTO;

use App\DTO\BaseDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class BaseDTOTest extends TestCase
{
    public function test_base_dto_instantiation_from_array()
    {
        $dto = new readonly class('value') extends BaseDTO {
            public function __construct(public string $testField) {}
            public static function fromRequest(Request $request): static {
                return new static($request->input('testField'));
            }
        };

        $this->assertEquals('value', $dto->testField);
    }

    public function test_base_dto_throws_error_on_missing_required_property()
    {
        $this->expectException(\TypeError::class);

        new readonly class() extends BaseDTO {
            public function __construct(public string $requiredField) {}
            public static function fromRequest(Request $request): static {
                return new static($request->input('requiredField'));
            }
        };
    }
}
