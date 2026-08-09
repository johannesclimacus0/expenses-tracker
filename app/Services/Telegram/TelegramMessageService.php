<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Models\TelegramChat;

final class TelegramMessageService
{
    public function send(TelegramChat $chat, string $text): void
    {
        $chat->message($text)->send();
    }

    public function ensureLinked(TelegramChat $chat): bool
    {
        if (!$chat->exists || $chat->user === null) {
            $this->send($chat, 'Сначала привяжите Telegram в настройках сайта.');

            return false;
        }

        return true;
    }

    public function helpText(): string
    {
        return <<<'TEXT'
Добавление операций через диалог:
/operations
TEXT;
    }
}
