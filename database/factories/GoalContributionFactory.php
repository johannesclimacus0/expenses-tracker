<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GoalContributionType;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GoalContribution> */
final class GoalContributionFactory extends Factory
{
    protected $model = GoalContribution::class;

    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'type' => GoalContributionType::Deposit,
            'amount' => fake()->randomFloat(2, 100, 10000),
            'contributed_at' => fake()->dateTimeBetween('-1 year'),
            'note' => fake()->optional()->sentence(3),
        ];
    }
}
