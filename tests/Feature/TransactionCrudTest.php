<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_transactions(): void
    {
        $this->get(route('transactions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_show_route_is_not_registered(): void
    {
        $this->assertFalse(Route::has('transactions.show'));
    }

    public function test_user_sees_only_their_transactions(): void
    {
        $user = User::factory()->create();
        $ownTransaction = Transaction::factory()->for($user)->create([
            'description' => 'Моя операция',
        ]);
        $foreignTransaction = Transaction::factory()->create([
            'description' => 'Чужая операция',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee($ownTransaction->description)
            ->assertDontSee($foreignTransaction->description);
    }

    public function test_user_can_create_a_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'type' => TransactionType::Expense->value,
                'amount' => '1250.50',
                'category_id' => $category->id,
                'description' => '  Продукты  ',
                'occurred_at' => '2026-08-01 12:30',
            ])
            ->assertRedirect(route('transactions.index'));

        $transaction = $user->transactions()->sole();

        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame('1250.50', $transaction->amount);
        $this->assertSame($category->id, $transaction->category_id);
        $this->assertSame('Продукты', $transaction->description);
        $this->assertNotNull($transaction->uuid);
    }

    public function test_transaction_can_be_created_without_a_category_or_description(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'type' => TransactionType::Income->value,
                'amount' => '500.00',
                'category_id' => '',
                'description' => '   ',
                'occurred_at' => '2026-08-01 12:30',
            ])
            ->assertRedirect(route('transactions.index'));

        $transaction = $user->transactions()->sole();

        $this->assertNull($transaction->category_id);
        $this->assertNull($transaction->description);
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
        $payload = [
            'type' => TransactionType::Expense->value,
            'amount' => '100.00',
            'description' => null,
            'occurred_at' => '2026-08-01 12:30',
        ];

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                ...$payload,
                'category_id' => $foreignCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                ...$payload,
                'category_id' => $incomeCategory->id,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_user_can_open_edit_page_using_uuid(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->assertSee($category->name);

        $this->assertStringContainsString(
            $transaction->uuid,
            route('transactions.edit', $transaction),
        );
    }

    public function test_user_can_update_their_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->expense()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Income,
        ]);

        $this->actingAs($user)
            ->patch(route('transactions.update', $transaction), [
                'type' => TransactionType::Income->value,
                'amount' => '750.25',
                'category_id' => $category->id,
                'description' => 'Зарплата',
                'occurred_at' => '2026-08-01 09:00',
            ])
            ->assertRedirect(route('transactions.index'));

        $transaction->refresh();

        $this->assertSame(TransactionType::Income, $transaction->type);
        $this->assertSame('750.25', $transaction->amount);
        $this->assertSame($category->id, $transaction->category_id);
        $this->assertSame('Зарплата', $transaction->description);
    }

    public function test_user_cannot_edit_or_update_foreign_transaction(): void
    {
        $user = User::factory()->create();
        $foreignTransaction = Transaction::factory()->create();

        $this->actingAs($user)
            ->get(route('transactions.edit', $foreignTransaction))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('transactions.update', $foreignTransaction), [
                'type' => TransactionType::Expense->value,
                'amount' => '100.00',
                'category_id' => null,
                'description' => null,
                'occurred_at' => '2026-08-01 09:00',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        $this->assertModelMissing($transaction);
    }

    public function test_user_cannot_delete_foreign_transaction(): void
    {
        $user = User::factory()->create();
        $foreignTransaction = Transaction::factory()->create();

        $this->actingAs($user)
            ->delete(route('transactions.destroy', $foreignTransaction))
            ->assertForbidden();

        $this->assertModelExists($foreignTransaction);
    }

    public function test_transactions_can_be_filtered(): void
    {
        $user = User::factory()->create();
        $food = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);
        $other = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);

        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $food->id,
            'description' => 'Продукты января',
            'occurred_at' => '2026-01-15 10:00:00',
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $other->id,
            'description' => 'Покупка июля',
            'occurred_at' => '2026-07-15 10:00:00',
        ]);
        Transaction::factory()->for($user)->income()->create([
            'description' => 'Зарплата',
            'occurred_at' => '2026-07-20 10:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index', ['category' => $food->uuid]))
            ->assertOk()
            ->assertSee('Продукты января')
            ->assertDontSee('Покупка июля')
            ->assertDontSee('Зарплата');

        $this->actingAs($user)
            ->get(route('transactions.index', ['type' => TransactionType::Income->value]))
            ->assertOk()
            ->assertSee('Зарплата')
            ->assertDontSee('Продукты января');

        $this->actingAs($user)
            ->get(route('transactions.index', [
                'from' => '2026-07-01',
                'to' => '2026-07-31',
            ]))
            ->assertOk()
            ->assertSee('Покупка июля')
            ->assertSee('Зарплата')
            ->assertDontSee('Продукты января');

        $this->actingAs($user)
            ->get(route('transactions.index', ['to' => '2026-01-31']))
            ->assertOk()
            ->assertSee('Продукты января')
            ->assertDontSee('Покупка июля');
    }

    public function test_transactions_are_paginated(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 11) as $number) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            Transaction::factory()->for($user)->create([
                'description' => "Операция {$suffix}",
                'occurred_at' => now()->subDays($number),
            ]);
        }

        $this->actingAs($user)
            ->get(route('transactions.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Операция 11')
            ->assertDontSee('Операция 01');
    }
}
