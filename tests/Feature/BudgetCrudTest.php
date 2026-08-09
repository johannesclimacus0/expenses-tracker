<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Budgets\BudgetUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_budgets(): void
    {
        $this->get(route('budgets.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_budgets(): void
    {
        $user = User::factory()->create();
        Budget::factory()->for($user)->create(['amount' => '12345.67']);
        Budget::factory()->create(['amount' => '76543.21']);

        $this->actingAs($user)
            ->get(route('budgets.index'))
            ->assertOk()
            ->assertSee('12 345,67')
            ->assertDontSee('76 543,21');
    }

    public function test_user_can_create_an_overall_budget(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'amount' => '80000.00',
                'month' => '2026-08',
                'category_id' => '',
            ])
            ->assertRedirect(route('budgets.index', ['month' => '2026-08']));

        $budget = $user->budgets()->sole();

        $this->assertNull($budget->category_id);
        $this->assertSame('80000.00', $budget->amount);
        $this->assertSame('2026-08-01', $budget->month->toDateString());
        $this->assertNotNull($budget->uuid);
    }

    public function test_duplicate_overall_budget_for_same_month_is_rejected(): void
    {
        $user = User::factory()->create();
        Budget::factory()->for($user)->create([
            'month' => '2026-08-01',
        ]);

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'amount' => '50000.00',
                'month' => '2026-08',
                'category_id' => null,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertCount(1, $user->budgets()->get());
    }

    public function test_user_can_create_category_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                'amount' => '15000.00',
                'month' => '2026-08',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('budgets.index', ['month' => '2026-08']));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => '2026-08-01',
        ]);
    }

    public function test_foreign_or_income_category_is_rejected(): void
    {
        $user = User::factory()->create();
        $foreignCategory = Category::factory()->create([
            'type' => TransactionType::Expense,
        ]);
        $incomeCategory = Category::factory()->for($user)->create([
            'type' => TransactionType::Income,
        ]);
        $payload = [
            'amount' => '10000.00',
            'month' => '2026-08',
        ];

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                ...$payload,
                'category_id' => $foreignCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->actingAs($user)
            ->post(route('budgets.store'), [
                ...$payload,
                'category_id' => $incomeCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_user_can_open_edit_page_using_uuid(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('budgets.edit', $budget))
            ->assertOk()
            ->assertSee('Изменить бюджет');

        $this->assertStringContainsString($budget->uuid, route('budgets.edit', $budget));
    }

    public function test_user_can_update_their_budget(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create([
            'amount' => '10000.00',
            'month' => '2026-08-01',
        ]);

        $this->actingAs($user)
            ->patch(route('budgets.update', $budget), [
                'amount' => '25000.50',
                'month' => '2026-09',
                'category_id' => null,
            ])
            ->assertRedirect(route('budgets.index', ['month' => '2026-09']));

        $budget->refresh();

        $this->assertSame('25000.50', $budget->amount);
        $this->assertSame('2026-09-01', $budget->month->toDateString());
    }

    public function test_user_cannot_edit_update_or_delete_foreign_budget(): void
    {
        $user = User::factory()->create();
        $foreignBudget = Budget::factory()->create();

        $this->actingAs($user)
            ->get(route('budgets.edit', $foreignBudget))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('budgets.update', $foreignBudget), [
                'amount' => '10000.00',
                'month' => '2026-08',
                'category_id' => null,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('budgets.destroy', $foreignBudget))
            ->assertForbidden();

        $this->assertModelExists($foreignBudget);
    }

    public function test_user_can_delete_their_budget(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('budgets.destroy', $budget))
            ->assertRedirect(route('budgets.index', [
                'month' => $budget->month->format('Y-m'),
            ]));

        $this->assertModelMissing($budget);
    }

    public function test_budget_usage_is_calculated_for_overall_and_category_budgets(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);
        Budget::factory()->for($user)->create([
            'amount' => '500.00',
            'month' => '2026-08-01',
        ]);
        Budget::factory()->for($user)->create([
            'category_id' => $category->id,
            'amount' => '200.00',
            'month' => '2026-08-01',
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
            'amount' => '125.50',
            'occurred_at' => '2026-08-10 10:00:00',
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'amount' => '50.00',
            'occurred_at' => '2026-08-11 10:00:00',
        ]);

        $usage = app(BudgetUsageService::class)
            ->forMonth($user, now()->setDate(2026, 8, 1)->startOfMonth());

        $overall = $usage->first(fn ($item) => $item->budget->isOverall());
        $categoryUsage = $usage->first(fn ($item) => !$item->budget->isOverall());

        $this->assertSame('175.50', $overall->spent);
        $this->assertSame('324.50', $overall->remaining);
        $this->assertSame('125.50', $categoryUsage->spent);
        $this->assertSame('74.50', $categoryUsage->remaining);
    }

    public function test_month_filter_shows_requested_budgets(): void
    {
        $user = User::factory()->create();
        Budget::factory()->for($user)->create([
            'amount' => '11111.00',
            'month' => '2026-08-01',
        ]);
        Budget::factory()->for($user)->create([
            'amount' => '22222.00',
            'month' => '2026-09-01',
        ]);

        $this->actingAs($user)
            ->get(route('budgets.index', ['month' => '2026-09']))
            ->assertOk()
            ->assertSee('22 222,00')
            ->assertDontSee('11 111,00');
    }
}
