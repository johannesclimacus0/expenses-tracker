<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GoalContributionType;
use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GoalCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_open_goals(): void
    {
        $this->get(route('goals.index'))->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_goals(): void
    {
        $user = User::factory()->create();
        Goal::factory()->for($user)->create(['name' => 'Моя цель']);
        Goal::factory()->create(['name' => 'Чужая цель']);

        $this->actingAs($user)->get(route('goals.index'))
            ->assertOk()
            ->assertSee('Моя цель')
            ->assertDontSee('Чужая цель');
    }

    public function test_user_can_create_update_and_delete_a_goal_using_uuid(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('goals.store'), [
            'name' => '  Отпуск  ',
            'target_amount' => '150000.50',
            'deadline' => '2027-06-01',
        ])->assertRedirect();

        $goal = $user->goals()->sole();
        $this->assertSame('Отпуск', $goal->name);
        $this->assertSame('150000.50', $goal->target_amount);
        $this->assertSame(GoalStatus::Active, $goal->status);
        $this->assertStringContainsString($goal->uuid, route('goals.show', $goal));

        $this->actingAs($user)->patch(route('goals.update', $goal), [
            'name' => 'Путешествие',
            'target_amount' => '175000.00',
            'deadline' => '',
            'status' => GoalStatus::Paused->value,
        ])->assertRedirect(route('goals.show', $goal));

        $this->assertSame('Путешествие', $goal->refresh()->name);
        $this->assertNull($goal->deadline);
        $this->assertSame(GoalStatus::Paused, $goal->status);

        $this->actingAs($user)->delete(route('goals.destroy', $goal))
            ->assertRedirect(route('goals.index'));
        $this->assertModelMissing($goal);
    }

    public function test_user_cannot_view_or_modify_a_foreign_goal(): void
    {
        $user = User::factory()->create();
        $foreignGoal = Goal::factory()->create();

        $this->actingAs($user)->get(route('goals.show', $foreignGoal))->assertForbidden();
        $this->actingAs($user)->get(route('goals.edit', $foreignGoal))->assertForbidden();
        $this->actingAs($user)->patch(route('goals.update', $foreignGoal), [
            'name' => 'Чужая',
            'target_amount' => '100.00',
            'deadline' => null,
            'status' => GoalStatus::Active->value,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('goals.destroy', $foreignGoal))->assertForbidden();
    }

    public function test_deposit_completes_goal_and_withdrawal_reactivates_it(): void
    {
        CarbonImmutable::setTestNow('2026-08-04 10:00:00');
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create(['target_amount' => '1000.00']);

        $this->actingAs($user)->post(route('goals.contributions.store', $goal), [
            'type' => GoalContributionType::Deposit->value,
            'amount' => '1000.00',
            'contributed_at' => '2026-08-04 09:00:00',
            'note' => '  Первый взнос  ',
        ])->assertRedirect(route('goals.show', $goal));

        $this->assertSame(GoalStatus::Completed, $goal->refresh()->status);
        $this->assertSame('Первый взнос', $goal->contributions()->sole()->note);

        $this->actingAs($user)->post(route('goals.contributions.store', $goal), [
            'type' => GoalContributionType::Withdrawal->value,
            'amount' => '200.00',
            'contributed_at' => '2026-08-04 09:30:00',
            'note' => null,
        ])->assertRedirect(route('goals.show', $goal));

        $this->assertSame(GoalStatus::Active, $goal->refresh()->status);
        $this->assertDatabaseCount('goal_contributions', 2);
    }

    public function test_user_cannot_withdraw_more_than_goal_contains(): void
    {
        CarbonImmutable::setTestNow('2026-08-04 10:00:00');
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();
        GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Deposit,
            'amount' => '100.00',
        ]);

        $this->actingAs($user)->from(route('goals.show', $goal))
            ->post(route('goals.contributions.store', $goal), [
                'type' => GoalContributionType::Withdrawal->value,
                'amount' => '101.00',
                'contributed_at' => '2026-08-04 09:00:00',
            ])
            ->assertRedirect(route('goals.show', $goal))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('goal_contributions', 1);
    }

    public function test_contribution_must_belong_to_nested_goal_and_its_owner(): void
    {
        $user = User::factory()->create();
        $ownGoal = Goal::factory()->for($user)->create();
        $foreignGoal = Goal::factory()->create();
        $foreignContribution = GoalContribution::factory()->for($foreignGoal)->create();

        $this->actingAs($user)
            ->delete(route('goals.contributions.destroy', [$ownGoal, $foreignContribution]))
            ->assertNotFound();
        $this->actingAs($user)
            ->delete(route('goals.contributions.destroy', [$foreignGoal, $foreignContribution]))
            ->assertForbidden();
        $this->assertModelExists($foreignContribution);
    }

    public function test_user_can_delete_a_contribution_without_making_progress_negative(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();
        $deposit = GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Deposit,
            'amount' => '100.00',
        ]);
        GoalContribution::factory()->for($goal)->create([
            'type' => GoalContributionType::Withdrawal,
            'amount' => '50.00',
        ]);

        $this->actingAs($user)->from(route('goals.show', $goal))
            ->delete(route('goals.contributions.destroy', [$goal, $deposit]))
            ->assertSessionHasErrors('contribution');

        $this->assertModelExists($deposit);
    }
}
