<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'month' => now()->startOfMonth(),
        ];
    }
}
