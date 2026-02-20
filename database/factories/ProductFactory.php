<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
            'category_id' => \App\Models\Category::factory(), // Создаст категорию автоматически
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 10000),
            'old_price' => fake()->boolean() ? fake()->randomFloat(2, 11000, 20000) : null,
            'quantity' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
