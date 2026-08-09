<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;

final readonly class CancelTransactionDialog
{
    public function handle(TelegramChat $chat, TelegramTransactionDialogService $dialog, TelegramMessageService $messages): void
    {
        if (!$dialog->has($chat)) {
            $messages->send($chat, 'Нет активного создания операции.');

            return;
        }

        $dialog->forget($chat);
        $messages->send($chat, 'Создание операции отменено.');
    }
}
