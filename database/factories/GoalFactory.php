<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Database\Factories\Concerns\CreatesAccountMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Goal> */
final class GoalFactory extends Factory
{
    use CreatesAccountMembership;

    protected $model = Goal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => fn (array $attributes) => $this->defaultAccountId($attributes),
            'name' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 10000, 500000),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'status' => GoalStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Goal $goal): void {
            $this->createAccountMembership($goal->user_id, $goal->account_id);
        });
    }
}
