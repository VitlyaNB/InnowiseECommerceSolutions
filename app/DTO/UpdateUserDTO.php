<?php


namespace App\DTO;

use Illuminate\Http\Request;

final readonly class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $role = null,
        public ?float $balance = null
    )
    {
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            $request->has('name') ? $request->string('name')->value() : null,
            $request->has('email') ? $request->string('email')->value() : null,
            $request->has('role') ? $request->string('role')->value() : null,
            $request->has('balance') ? (float) $request->float('balance') : null
        );
    }

    /**
     * @return array<string, string|float>
     */
    public function toArray(): array
    {
        /** @var array<string, string|float> $filtered */
        $filtered = array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'balance' => $this->balance,
        ], fn($value) => !is_null($value));

        return $filtered;
    }
}
