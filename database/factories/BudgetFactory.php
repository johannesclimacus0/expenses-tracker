<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Database\Factories\Concerns\CreatesAccountMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    use CreatesAccountMembership;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => fn (array $attributes) => $this->defaultAccountId($attributes),
            'category_id' => null,
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'month' => now()->startOfMonth(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Budget $budget): void {
            $this->createAccountMembership($budget->user_id, $budget->account_id);
        });
    }
}
