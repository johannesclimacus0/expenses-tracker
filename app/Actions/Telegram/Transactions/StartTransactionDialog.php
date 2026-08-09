<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\Enums\TransactionType;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

final readonly class StartTransactionDialog
{
    public function handle(TelegramChat $chat, TelegramTransactionDialogService $dialog, TelegramMessageService $messages): void
    {
        if (!$messages->ensureLinked($chat)) {
            return;
        }

        $dialog->forget($chat);
        $keyboard = Keyboard::make()
            ->buttons([
                Button::make('Расход')
                    ->action('chooseTransactionType')
                    ->param('type', TransactionType::Expense->value),
                Button::make('Доход')
                    ->action('chooseTransactionType')
                    ->param('type', TransactionType::Income->value),
            ])
            ->chunk(2);

        $chat->message('Выберите тип операции:')->keyboard($keyboard)->send();
    }
}
