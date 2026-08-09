<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\Enums\TransactionDialogStep;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;

final readonly class SkipTransactionComment
{
    public function handle(
        TelegramChat $chat,
        TelegramTransactionDialogService $dialog,
        CompleteTransactionDialog $completeTransaction,
        TelegramMessageService $messages,
    ): void {
        $state = $dialog->get($chat);

        if ($state === null || $state->step !== TransactionDialogStep::Comment) {
            $messages->send($chat, 'Сейчас нечего пропускать.');

            return;
        }
        $completeTransaction->handle(
            $chat,
            $state,
            null
        );
    }
}
