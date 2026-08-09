<?php

namespace Database\Factories;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timeInFuture = fake()->dateTimeBetween('+1 day', '+1 year');

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => $this->faker->randomFloat(2, 10),
            'description' => fake()->optional()->sentence(3),
            'period' => fake()->randomElement(RecurringPeriod::cases()),
            'starts_at' => $timeInFuture,
            'next_run_at' => $timeInFuture,
            'last_run_at' => null,
            'is_active' => true,
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
