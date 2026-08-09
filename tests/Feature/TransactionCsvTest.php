<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransactionCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_import_or_export_transactions(): void
    {
        $this->get(route('transactions.export'))
            ->assertRedirect(route('login'));

        $this->post(route('transactions.import'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_export_only_their_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
            'amount' => '1250.50',
            'description' => 'Моя покупка',
            'occurred_at' => '2026-08-01 12:30:00',
        ]);
        Transaction::factory()->create([
            'description' => 'Чужая операция',
        ]);

        $response = $this->actingAs($user)
            ->get(route('transactions.export'))
            ->assertOk()
            ->assertDownload('transactions-' . now()->format('Y-m-d') . '.csv');

        $csv = $response->streamedContent();
        $decodedCsv = mb_convert_encoding(substr($csv, 2), 'UTF-8', 'UTF-16LE');

        $this->assertStringStartsWith("\xFF\xFE", $csv);
        $this->assertStringStartsWith("sep=;\r\noccurred_at;type;amount;category;description", $decodedCsv);
        $this->assertStringContainsString('"2026-08-01 12:30:00";expense;1250.50;Продукты;"Моя покупка"', $decodedCsv);
        $this->assertStringNotContainsString('Чужая операция', $decodedCsv);
    }

    public function test_user_can_import_transactions_with_and_without_categories(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            implode("\n", [
                'occurred_at,type,amount,category,description',
                '2026-08-01 12:30:00,expense,1250.50,Продукты,Покупка',
                '2026-08-02 09:00:00,доход,5000,,Зарплата',
            ]),
        );

        $this->actingAs($user)
            ->post(route('transactions.import'), ['csv' => $file])
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status', 'Импортировано транзакций: 2');

        $transactions = $user->transactions()->orderBy('occurred_at')->get();

        $this->assertCount(2, $transactions);
        $this->assertSame(TransactionType::Expense, $transactions[0]->type);
        $this->assertSame('1250.50', $transactions[0]->amount);
        $this->assertSame($category->id, $transactions[0]->category_id);
        $this->assertSame('Покупка', $transactions[0]->description);
        $this->assertSame(TransactionType::Income, $transactions[1]->type);
        $this->assertNull($transactions[1]->category_id);
    }

    public function test_excel_semicolon_csv_and_comma_decimal_can_be_imported(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            implode("\n", [
                'sep=;',
                'occurred_at;type;amount;category;description',
                '2026-08-01;расход;"1 250,75";;Покупка',
            ]),
        );

        $this->actingAs($user)
            ->post(route('transactions.import'), ['csv' => $file])
            ->assertRedirect(route('transactions.index'))
            ->assertSessionDoesntHaveErrors();

        $transaction = $user->transactions()->sole();

        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame('1250.75', $transaction->amount);
    }

    public function test_utf16le_excel_csv_can_be_imported(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);
        $csv = implode("\r\n", [
            'sep=;',
            'occurred_at;type;amount;category;description',
            '2026-08-01;расход;100;Продукты;Хлеб',
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            "\xFF\xFE" . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8'),
        );

        $this->actingAs($user)
            ->post(route('transactions.import'), ['csv' => $file])
            ->assertRedirect(route('transactions.index'))
            ->assertSessionDoesntHaveErrors();

        $transaction = $user->transactions()->sole();

        $this->assertSame($category->id, $transaction->category_id);
        $this->assertSame('Хлеб', $transaction->description);
    }

    public function test_import_is_atomic_when_any_row_is_invalid(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            implode("\n", [
                'occurred_at,type,amount,category,description',
                '2026-08-01,income,1000,,Корректная строка',
                '2026-08-02,expense,500,Чужая категория,Ошибка',
            ]),
        );

        $this->actingAs($user)
            ->from(route('transactions.index'))
            ->post(route('transactions.import'), ['csv' => $file])
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHasErrors('csv');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_import_rejects_an_unexpected_header(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            "date,type,amount,category,description\n2026-08-01,income,100,,,",
        );

        $this->actingAs($user)
            ->post(route('transactions.import'), ['csv' => $file])
            ->assertSessionHasErrors('csv');

        $this->assertDatabaseCount('transactions', 0);
    }
}
