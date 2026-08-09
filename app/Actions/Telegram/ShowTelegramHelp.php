<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;

final readonly class ShowTelegramHelp
{
    public function handle(TelegramChat $chat, TelegramMessageService $messages): void
    {
        $messages->send($chat, $messages->helpText());
    }
}
