<?php

namespace App\Actions\Transactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Transactions\TransactionCsvRowData;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\Transactions\TransactionCsvService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ImportTransactionsCsv
{
    public function __construct(
        private TransactionCsvService $csv,
        private ResolveCurrentAccount $accounts,
    ) {}

    public function handle(User $user, UploadedFile $file): int
    {
        $categories = $user->categories()
            ->get()
            ->keyBy(fn ($category) => $this->categoryKey($category->type, $category->name));
        $transactions = [];
        $errors = [];

        foreach ($this->csv->read($file) as $row) {
            $values = $row['values'];
            $values['type'] = $this->normalizeType($values['type']);
            $values['amount'] = str_replace(
                ["\u{00A0}", ' ', ','],
                ['', '', '.'],
                (string) $values['amount'],
            );

            $validator = Validator::make($values, [
                'occurred_at' => ['required', 'date'],
                'type' => ['required', Rule::enum(TransactionType::class)],
                'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
                'category' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Строка {$row['line']}: " . $validator->errors()->first();

                continue;
            }

            $type = TransactionType::from($values['type']);
            $category = $values['category'] !== null
                ? $categories->get($this->categoryKey($type, $values['category']))
                : null;

            if ($values['category'] !== null && $category === null) {
                $errors[] = "Строка {$row['line']}: категория «{$values['category']}» не найдена или имеет другой тип.";

                continue;
            }

            $transactions[] = new TransactionCsvRowData(
                type: $type,
                amount: $values['amount'],
                categoryId: $category?->id,
                description: $values['description'],
                occurredAt: CarbonImmutable::parse($values['occurred_at']),
            );
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['csv' => $errors]);
        }

        $account = $this->accounts->handle($user);

        return DB::transaction(function () use ($user, $transactions, $account): int {
            foreach ($transactions as $transaction) {
                $user->transactions()->create([
                    'account_id' => $account->getKey(),
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'category_id' => $transaction->categoryId,
                    'description' => $transaction->description,
                    'occurred_at' => $transaction->occurredAt,
                ]);
            }

            return count($transactions);
        });
    }

    private function normalizeType(?string $type): string
    {
        return match (mb_strtolower(trim((string) $type))) {
            'income', 'доход' => TransactionType::Income->value,
            'expense', 'расход' => TransactionType::Expense->value,
            default => (string) $type,
        };
    }

    private function categoryKey(TransactionType $type, string $name): string
    {
        return $type->value . '|' . mb_strtolower(trim($name));
    }
}
