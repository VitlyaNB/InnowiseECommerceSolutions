<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'last_message_at' => null,
        ];
    }
}
