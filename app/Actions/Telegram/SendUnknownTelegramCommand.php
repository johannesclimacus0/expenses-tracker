<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;

final readonly class SendUnknownTelegramCommand
{
    public function handle(TelegramChat $chat, TelegramMessageService $messages): void
    {
        $messages->send($chat, "Неизвестная команда.\n\n" . $messages->helpText());
    }
}
