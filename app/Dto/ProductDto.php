<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

final readonly class ProductDto extends BaseDto
{
    /**
     * @param  array<int, string>|array<int, UploadedFile>  $images
     */
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?float $price = null,
        public ?float $oldPrice = null,
        public ?int $quantity = null,
        public ?int $categoryId = null,
        public ?bool $isActive = null,
        public array $images = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'old_price' => $this->oldPrice,
            'quantity' => $this->quantity,
            'category_id' => $this->categoryId,
            'is_active' => $this->isActive,
        ];

        if ($this->images !== [] && $this->id !== null) {
            $data['images'] = $this->images;
        }

        return array_filter($data, fn ($value) => ! is_null($value));
    }
}
