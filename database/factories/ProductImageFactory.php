<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    private const IMAGE_KEYWORDS = [
        'product', 'item', 'goods', 'shop', 'store',
    ];

    public function definition(): array
    {
        $keyword = fake()->randomElement(self::IMAGE_KEYWORDS);
        $width = fake()->randomElement([640, 800, 1024]);
        $height = fake()->randomElement([480, 600, 768]);

        return [
            'image_path' => "https://loremflickr.com/{$width}/{$height}/{$keyword}",
        ];
    }
}
