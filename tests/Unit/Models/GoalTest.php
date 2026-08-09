<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\GoalContributionType;
use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_relations_and_casts_are_configured(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create([
            'target_amount' => '10000',
            'deadline' => '2027-01-01',
            'status' => GoalStatus::Paused,
        ]);
        $contribution = GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Withdrawal,
            'amount' => '100.50',
            'contributed_at' => '2026-08-01 10:00:00',
        ]);

        $this->assertInstanceOf(BelongsTo::class, $goal->user());
        $this->assertInstanceOf(HasMany::class, $goal->contributions());
        $this->assertTrue($goal->user->is($user));
        $this->assertTrue($goal->contributions->contains($contribution));
        $this->assertSame('10000.00', $goal->target_amount);
        $this->assertInstanceOf(CarbonImmutable::class, $goal->deadline);
        $this->assertSame(GoalStatus::Paused, $goal->status);
        $this->assertSame(GoalContributionType::Withdrawal, $contribution->type);
        $this->assertSame('100.50', $contribution->amount);
        $this->assertInstanceOf(CarbonImmutable::class, $contribution->contributed_at);
        $this->assertTrue($contribution->goal->is($goal));
    }

    public function test_goals_and_contributions_use_uuid_route_keys(): void
    {
        $goal = Goal::factory()->create();
        $contribution = GoalContribution::factory()->for($goal)->create();

        $this->assertSame('uuid', $goal->getRouteKeyName());
        $this->assertNotNull($goal->uuid);
        $this->assertSame('uuid', $contribution->getRouteKeyName());
        $this->assertNotNull($contribution->uuid);
    }

    public function test_deleting_user_or_goal_cascades_to_financial_goal_data(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();
        $contribution = GoalContribution::factory()->for($goal)->create();

        $goal->delete();

        $this->assertModelMissing($goal);
        $this->assertModelMissing($contribution);

        $secondGoal = Goal::factory()->for($user)->create();
        $user->delete();

        $this->assertModelMissing($secondGoal);
    }
}
