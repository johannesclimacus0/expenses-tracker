<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => fake()->randomFloat(2, 1, 100_000),
            'description' => fake()->optional()->sentence(3),
            'occurred_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Expense,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Income,
        ]);
    }
}
