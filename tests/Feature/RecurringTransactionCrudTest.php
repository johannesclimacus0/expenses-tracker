<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class RecurringTransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_open_recurring_transactions(): void
    {
        $this->get(route('recurring-transactions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_show_route_is_not_registered(): void
    {
        $this->assertFalse(Route::has('recurring-transactions.show'));
    }

    public function test_user_sees_only_their_recurring_transactions(): void
    {
        $user = User::factory()->create();
        RecurringTransaction::factory()->for($user)->create([
            'description' => 'Моя подписка',
        ]);
        RecurringTransaction::factory()->create([
            'description' => 'Чужая подписка',
        ]);

        $this->actingAs($user)
            ->get(route('recurring-transactions.index'))
            ->assertOk()
            ->assertSee('Моя подписка')
            ->assertDontSee('Чужая подписка');
    }

    public function test_user_can_create_a_recurring_transaction(): void
    {
        CarbonImmutable::setTestNow('2026-08-03 10:00:00');
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->post(route('recurring-transactions.store'), [
                'type' => TransactionType::Expense->value,
                'amount' => '1490.50',
                'category_id' => $category->id,
                'description' => '  Подписка  ',
                'period' => RecurringPeriod::Monthly->value,
                'starts_at' => '2026-07-15 09:00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('recurring-transactions.index'));

        $recurringTransaction = $user->recurringTransactions()->sole();

        $this->assertSame('Подписка', $recurringTransaction->description);
        $this->assertSame(RecurringPeriod::Monthly, $recurringTransaction->period);
        $this->assertSame('2026-08-15 09:00:00', $recurringTransaction->next_run_at->toDateTimeString());
        $this->assertTrue($recurringTransaction->is_active);

    }

    public function test_foreign_or_wrong_type_category_is_rejected(): void
    {
        $user = User::factory()->create();
        $foreignCategory = Category::factory()->create([
            'type' => TransactionType::Expense,
        ]);
        $incomeCategory = Category::factory()->for($user)->create([
            'type' => TransactionType::Income,
        ]);
        $payload = $this->payload();

        $this->actingAs($user)
            ->post(route('recurring-transactions.store'), [
                ...$payload,
                'category_id' => $foreignCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->actingAs($user)
            ->post(route('recurring-transactions.store'), [
                ...$payload,
                'category_id' => $incomeCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('recurring_transactions', 0);
    }

    public function test_user_can_open_edit_page_using_uuid(): void
    {
        $user = User::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('recurring-transactions.edit', $recurringTransaction))
            ->assertOk();

        $this->assertStringContainsString(
            $recurringTransaction->uuid,
            route('recurring-transactions.edit', $recurringTransaction),
        );
    }

    public function test_user_can_update_their_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->expense()->create([
            'starts_at' => '2026-08-01 09:00:00',
            'next_run_at' => '2026-09-01 09:00:00',
            'period' => RecurringPeriod::Monthly,
        ]);
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Income,
        ]);

        $this->actingAs($user)
            ->patch(route('recurring-transactions.update', $recurringTransaction), [
                'type' => TransactionType::Income->value,
                'amount' => '5000.00',
                'category_id' => $category->id,
                'description' => 'Выплата',
                'period' => RecurringPeriod::Monthly->value,
                'starts_at' => '2026-08-01 09:00',
                'is_active' => '0',
            ])
            ->assertRedirect(route('recurring-transactions.index'));

        $recurringTransaction->refresh();

        $this->assertSame(TransactionType::Income, $recurringTransaction->type);
        $this->assertSame('5000.00', $recurringTransaction->amount);
        $this->assertSame($category->id, $recurringTransaction->category_id);
        $this->assertFalse($recurringTransaction->is_active);
    }

    public function test_user_cannot_modify_a_foreign_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $foreignRecurringTransaction = RecurringTransaction::factory()->create();

        $this->actingAs($user)
            ->get(route('recurring-transactions.edit', $foreignRecurringTransaction))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(
                route('recurring-transactions.update', $foreignRecurringTransaction),
                $this->payload(),
            )
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('recurring-transactions.destroy', $foreignRecurringTransaction))
            ->assertForbidden();
    }

    public function test_user_can_delete_their_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('recurring-transactions.destroy', $recurringTransaction))
            ->assertRedirect(route('recurring-transactions.index'));

        $this->assertModelMissing($recurringTransaction);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'type' => TransactionType::Expense->value,
            'amount' => '100.00',
            'category_id' => null,
            'description' => null,
            'period' => RecurringPeriod::Monthly->value,
            'starts_at' => '2026-08-01 09:00',
            'is_active' => '1',
        ];
    }
}
