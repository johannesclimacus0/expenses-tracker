<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\Actions\Telegram\CreateTelegramTransaction;
use App\DTOs\Telegram\TransactionDialogState;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;

final readonly class CompleteTransactionDialog
{
    public function __construct(
        private CreateTelegramTransaction $createTransaction,
        private TelegramTransactionDialogService $dialog,
        private TelegramMessageService $messages,
    ) {}

    public function handle(TelegramChat $chat, TransactionDialogState $state, ?string $comment): void
    {
        if ($state->type === null || $state->amount === null || $state->categoryName === null) {
            $this->dialog->forget($chat);
            $this->messages->send($chat, 'Данные операции потеряны. Отправьте /operations ещё раз.');

            return;
        }

        $input = "{$state->amount} {$state->categoryName}";

        if ($comment !== null && trim($comment) !== '') {
            $input .= ' | ' . trim($comment);
        }

        $created = $this->createTransaction->handle($chat, $state->type, $input, true);

        if ($created) {
            $this->dialog->forget($chat);
        }
    }
}
