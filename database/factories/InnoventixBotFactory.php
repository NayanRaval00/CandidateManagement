<?php

namespace Database\Factories;

use App\Models\InnoventixBot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InnoventixBot>
 */
class InnoventixBotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt' => $this->faker->sentence(),
            'sql_query' => 'SELECT * FROM users LIMIT 10;',
            'results' => [
                'success' => true,
                'count' => 1,
                'rows' => [
                    ['id' => 1, 'name' => $this->faker->name(), 'email' => $this->faker->safeEmail()],
                ],
            ],
            'is_successful' => true,
            'error_message' => null,
        ];
    }
}
