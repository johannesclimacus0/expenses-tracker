<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Actions\Transactions\CreateTransaction;
use App\DTOs\Telegram\TelegramTransactionInput;
use App\DTOs\Transactions\TransactionData;
use App\Enums\TransactionType;
use App\Models\TelegramChat;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class TelegramTransactionService
{
    public function __construct(
        private CreateTransaction $createTransaction,
    ) {}

    public function create(TelegramChat $chat, TransactionType $type, string $input): Transaction
    {
        $user = $chat->user;

        if ($user === null) {
            throw ValidationException::withMessages([
                'telegram' => 'Сначала привяжите Telegram в настройках сайта',
            ]);
        }

        $inputData = TelegramTransactionInput::fromString($input);

        $category = $user->categories()
            ->where('type', $type)
            ->where('name', $inputData->category)
            ->first();

        if ($category === null) {
            throw ValidationException::withMessages([
                'category' => "Категория «{$inputData->category}» не найдена для этого типа операции",
            ]);
        }

        return $this->createTransaction->handle(
            $user,
            new TransactionData(
                type: $type,
                amount: $inputData->amount,
                categoryId: $category->getKey(),
                description: $inputData->description,
                occurredAt: CarbonImmutable::now(),
            )
        );
    }
}
