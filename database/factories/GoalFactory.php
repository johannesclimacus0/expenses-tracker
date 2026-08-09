<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Goal> */
final class GoalFactory extends Factory
{
    protected $model = Goal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 10000, 500000),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'status' => GoalStatus::Active,
        ];
    }
}
