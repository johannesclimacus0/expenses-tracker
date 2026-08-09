<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\DTOs\Telegram\TransactionDialogState;
use App\Enums\TransactionType;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;

final readonly class SelectTransactionType
{
    public function handle(
        TelegramChat $chat,
        string $value,
        TelegramTransactionDialogService $dialog,
        TelegramMessageService $messages
    ): void {
        if (!$messages->ensureLinked($chat)) {
            return;
        }

        $type = TransactionType::tryFrom($value);

        if ($type === null) {
            $messages->send($chat, 'Не удалось определить тип операции.');

            return;
        }

        $dialog->put($chat, TransactionDialogState::awaitingAmount($type));
        $label = $type === TransactionType::Income ? 'дохода' : 'расхода';

        $messages->send($chat,
            "Введите сумму {$label}.\n\n" .
            'Для отмены /cancel.',
        );
    }
}
