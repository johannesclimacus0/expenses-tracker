<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\GoalContributionType;
use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Services\Goals\GoalProgressService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GoalProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_calculates_goal_progress_from_deposits_and_withdrawals(): void
    {
        $goal = Goal::factory()->create(['target_amount' => '1000.00']);
        GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Deposit,
            'amount' => '600.00',
        ]);
        GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Withdrawal,
            'amount' => '100.00',
        ]);

        $progress = app(GoalProgressService::class)->for($goal);

        $this->assertSame('600.00', $progress->deposited);
        $this->assertSame('100.00', $progress->withdrawn);
        $this->assertSame('500.00', $progress->currentAmount);
        $this->assertSame('500.00', $progress->remainingAmount);
        $this->assertSame(50, $progress->percentage);
        $this->assertFalse($progress->isCompleted);
    }

    public function test_it_detects_completed_and_overdue_goals(): void
    {
        CarbonImmutable::setTestNow('2026-08-04 10:00:00');
        $completed = Goal::factory()->create([
            'target_amount' => '100.00',
            'deadline' => '2026-08-01',
        ]);
        GoalContribution::factory()->for($completed)->create([
            'amount' => '150.00',
            'type' => GoalContributionType::Deposit,
        ]);
        $overdue = Goal::factory()->create([
            'target_amount' => '100.00',
            'deadline' => '2026-08-01',
            'status' => GoalStatus::Active,
        ]);

        $completedProgress = app(GoalProgressService::class)->for($completed);
        $overdueProgress = app(GoalProgressService::class)->for($overdue);

        $this->assertTrue($completedProgress->isCompleted);
        $this->assertFalse($completedProgress->isOverdue);
        $this->assertSame(100, $completedProgress->percentage);
        $this->assertSame('0.00', $completedProgress->remainingAmount);
        $this->assertTrue($overdueProgress->isOverdue);
    }
}
