<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Enums\TransactionType;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionService;
use Illuminate\Validation\ValidationException;

final readonly class CreateTelegramTransaction
{
    public function __construct(
        private TelegramTransactionService $transactionService,
        private TelegramMessageService $messages,
    ) {}

    public function handle(
        TelegramChat $chat,
        TransactionType $type,
        string $input,
        bool $includeDescription = false
    ): bool {
        try {
            $transaction = $this->transactionService->create($chat, $type, $input);
            $settings = $chat->user?->settings;

            if ($settings === null) {
                $this->messages->send($chat, 'Операция добавлена.');

                return true;
            }

            $amount = number_format(
                (float) $transaction->amount,
                $settings->show_cents ? 2 : 0,
                ',',
                ' ',
            ) . ' ' . $settings->currency->symbol();
            $label = $type === TransactionType::Income ? 'Доход' : 'Расход';
            $description = $includeDescription && $transaction->description
                ? "\nКомментарий: {$transaction->description}"
                : '';

            $this->messages->send(
                $chat,
                "{$label} на сумму {$amount} добавлен." . $description,
            );

            return true;
        } catch (ValidationException $exception) {
            $this->messages->send(
                $chat,
                $exception->validator->errors()->first(),
            );

            return false;
        }
    }
}
