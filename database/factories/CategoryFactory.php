<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private const CATEGORIES = [
        'electronics', 'clothing', 'books', 'home', 'sports', 'toys', 'food', 'beauty', 'garden', 'automotive',
    ];

    public function definition(): array
    {
        $category = fake()->randomElement(self::CATEGORIES);

        return [
            'name' => fake()->unique()->word(),
            'image_path' => "https://loremflickr.com/640/480/{$category}",
        ];
    }
}
