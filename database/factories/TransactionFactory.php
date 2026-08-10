<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Database\Factories\Concerns\CreatesAccountMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    use CreatesAccountMembership;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => fn (array $attributes) => $this->defaultAccountId($attributes),
            'category_id' => null,
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => fake()->randomFloat(2, 1, 100_000),
            'description' => fake()->optional()->sentence(3),
            'occurred_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Transaction $transaction): void {
            $this->createAccountMembership($transaction->user_id, $transaction->account_id);
        });
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
