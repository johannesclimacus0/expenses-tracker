<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_categories(): void
    {
        $this->get(route('categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_categories(): void
    {
        $user = User::factory()->create();
        $ownCategory = Category::factory()->for($user)->create(['name' => 'Продукты']);
        $foreignCategory = Category::factory()->create(['name' => 'Чужая категория']);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee($ownCategory->name)
            ->assertDontSee($foreignCategory->name);
    }

    public function test_income_and_expense_lists_are_paginated_independently(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 11) as $number) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            Category::factory()->for($user)->create([
                'name' => "Расход {$suffix}",
                'type' => TransactionType::Expense,
            ]);
            Category::factory()->for($user)->create([
                'name' => "Доход {$suffix}",
                'type' => TransactionType::Income,
            ]);
        }

        $this->actingAs($user)
            ->get(route('categories.index', ['expenses_page' => 2]))
            ->assertOk()
            ->assertSee('Расход 11')
            ->assertDontSee('Расход 01')
            ->assertSee('Доход 01')
            ->assertDontSee('Доход 11');
    }

    public function test_user_can_create_a_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'name' => '  Продукты  ',
                'type' => TransactionType::Expense->value,
            ])
            ->assertRedirect(route('categories.index'));

        $category = $user->categories()->sole();

        $this->assertSame('Продукты', $category->name);
        $this->assertSame(TransactionType::Expense, $category->type);
        $this->assertNotNull($category->uuid);
        $this->assertSame($category->uuid, $category->getRouteKey());
    }

    public function test_duplicate_name_and_type_are_rejected_for_same_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'Продукты',
                'type' => TransactionType::Expense->value,
            ])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHasErrors('name');
    }

    public function test_same_name_is_allowed_for_another_type(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create([
            'name' => 'Переводы',
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'name' => 'Переводы',
                'type' => TransactionType::Income->value,
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertCount(2, $user->categories()->where('name', 'Переводы')->get());
    }

    public function test_user_can_open_edit_page_using_uuid(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('categories.edit', $category))
            ->assertOk()
            ->assertSee($category->name);

        $this->assertStringContainsString($category->uuid, route('categories.edit', $category));
    }

    public function test_user_can_update_their_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->patch(route('categories.update', $category), [
                'name' => 'Зарплата',
                'type' => TransactionType::Income->value,
            ])
            ->assertRedirect(route('categories.index'));

        $category->refresh();

        $this->assertSame('Зарплата', $category->name);
        $this->assertSame(TransactionType::Income, $category->type);
    }

    public function test_user_cannot_change_type_of_category_that_is_in_use(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);
        $user->transactions()->create([
            'account_id' => $category->account_id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '100.00',
            'description' => null,
            'occurred_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('categories.update', $category), [
                'name' => $category->name,
                'type' => TransactionType::Income->value,
            ])
            ->assertSessionHasErrors('type');

        $this->assertSame(TransactionType::Expense, $category->refresh()->type);
    }

    public function test_user_cannot_edit_or_update_foreign_category(): void
    {
        $user = User::factory()->create();
        $foreignCategory = Category::factory()->create();

        $this->actingAs($user)
            ->get(route('categories.edit', $foreignCategory))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('categories.update', $foreignCategory), [
                'name' => 'Изменённая',
                'type' => TransactionType::Expense->value,
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($category);
    }

    public function test_user_cannot_delete_foreign_category(): void
    {
        $user = User::factory()->create();
        $foreignCategory = Category::factory()->create();

        $this->actingAs($user)
            ->delete(route('categories.destroy', $foreignCategory))
            ->assertForbidden();

        $this->assertModelExists($foreignCategory);
    }

    public function test_deleting_category_preserves_transactions_and_removes_category_budgets(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);
        $transaction = $user->transactions()->create([
            'account_id' => $category->account_id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '125.50',
            'description' => 'Покупка',
            'occurred_at' => now(),
        ]);
        $budget = $user->budgets()->create([
            'account_id' => $category->account_id,
            'category_id' => $category->id,
            'amount' => '5000.00',
            'month' => now()->startOfMonth(),
        ]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertModelExists($transaction);
        $this->assertNull($transaction->refresh()->category_id);
        $this->assertModelMissing($budget);
    }
}
