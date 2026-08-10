<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\GoalContributionType;
use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GoalContributionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_status_is_synced_when_contribution_is_created_directly(): void
    {
        $goal = Goal::factory()->create([
            'target_amount' => '100.00',
            'status' => GoalStatus::Active,
        ]);

        GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Deposit,
            'amount' => '100.00',
        ]);

        $this->assertSame(GoalStatus::Completed, $goal->refresh()->status);
    }

    public function test_goal_status_is_synced_when_contribution_is_deleted_directly(): void
    {
        $goal = Goal::factory()->create([
            'target_amount' => '100.00',
            'status' => GoalStatus::Active,
        ]);
        $contribution = GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Deposit,
            'amount' => '100.00',
        ]);

        $this->assertSame(GoalStatus::Completed, $goal->refresh()->status);

        $contribution->delete();

        $this->assertSame(GoalStatus::Active, $goal->refresh()->status);
    }
}
