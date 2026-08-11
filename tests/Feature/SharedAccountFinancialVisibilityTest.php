<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SharedAccountFinancialVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_member_sees_transactions_created_by_another_member(): void
    {
        [$owner, $member, $sharedAccount, $memberPersonalAccount] = $this->sharedAccountUsers();

        Transaction::factory()->for($owner)->create([
            'account_id' => $sharedAccount->id,
            'description' => 'Операция общего счета',
        ]);
        Transaction::factory()->for($member)->create([
            'account_id' => $memberPersonalAccount->id,
            'description' => 'Личная операция участника',
        ]);

        $this->actingAs($member)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('Операция общего счета')
            ->assertDontSee('Личная операция участника');
    }

    public function test_dashboard_includes_transactions_from_all_active_account_members(): void
    {
        [$owner, $member, $sharedAccount, $memberPersonalAccount] = $this->sharedAccountUsers();

        Transaction::factory()->for($owner)->income()->create([
            'account_id' => $sharedAccount->id,
            'amount' => '1000.00',
            'description' => 'Общий доход',
            'occurred_at' => now()->startOfMonth()->addDay(),
        ]);
        Transaction::factory()->for($member)->expense()->create([
            'account_id' => $sharedAccount->id,
            'amount' => '250.00',
            'description' => 'Общий расход',
            'occurred_at' => now()->startOfMonth()->addDays(2),
        ]);
        Transaction::factory()->for($member)->expense()->create([
            'account_id' => $memberPersonalAccount->id,
            'amount' => '999.00',
            'description' => 'Личный расход',
            'occurred_at' => now()->startOfMonth()->addDays(3),
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Общий доход')
            ->assertSee('Общий расход')
            ->assertDontSee('Личный расход')
            ->assertSee('1 000,00')
            ->assertSee('250,00')
            ->assertSee('750,00');
    }

    public function test_export_contains_transactions_from_all_active_account_members(): void
    {
        [$owner, $member, $sharedAccount, $memberPersonalAccount] = $this->sharedAccountUsers();

        Transaction::factory()->for($owner)->create([
            'account_id' => $sharedAccount->id,
            'description' => 'Экспорт общей операции',
        ]);
        Transaction::factory()->for($member)->create([
            'account_id' => $memberPersonalAccount->id,
            'description' => 'Экспорт личной операции',
        ]);

        $response = $this->actingAs($member)
            ->get(route('transactions.export'))
            ->assertOk();

        $csv = $response->streamedContent();
        $decodedCsv = mb_convert_encoding(substr($csv, 2), 'UTF-8', 'UTF-16LE');

        $this->assertStringContainsString('Экспорт общей операции', $decodedCsv);
        $this->assertStringNotContainsString('Экспорт личной операции', $decodedCsv);
    }

    public function test_account_member_sees_categories_created_by_another_member(): void
    {
        [$owner, $member, $sharedAccount] = $this->sharedAccountUsers();

        $category = Category::factory()->for($owner)->create([
            'account_id' => $sharedAccount->id,
            'name' => 'Общая категория',
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($member)
            ->get(route('transactions.create'))
            ->assertOk()
            ->assertSee($category->name);
    }

    /**
     * @return array{User, User, Account, Account}
     */
    private function sharedAccountUsers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $sharedAccount = $owner->accounts()->sole();
        $memberPersonalAccount = $member->accounts()->sole();

        $sharedAccount->members()->create([
            'user_id' => $member->id,
            'role' => AccountRole::Member,
        ]);
        $member->settings()->updateOrCreate([], [
            'active_account_id' => $sharedAccount->id,
        ]);

        return [$owner, $member, $sharedAccount, $memberPersonalAccount];
    }
}
