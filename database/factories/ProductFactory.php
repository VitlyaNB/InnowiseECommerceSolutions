<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 10000),
            'old_price' => fake()->boolean() ? fake()->randomFloat(2, 11000, 20000) : null,
            'quantity' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
