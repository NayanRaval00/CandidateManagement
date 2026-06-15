<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->words(3, true)),
            'amount' => $this->faker->randomFloat(2, 10, 1500),
            'expense_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'category' => $this->faker->randomElement(['Travel', 'Food', 'Utilities', 'Software', 'Rent', 'Marketing', 'Other']),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
