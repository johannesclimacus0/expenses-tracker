<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\TelegramChat;
use App\Services\Telegram\TelegramLinkService;
use App\Services\Telegram\TelegramMessageService;

final readonly class ConnectTelegramChat
{
    public function handle(
        TelegramChat $chat,
        string $token,
        TelegramLinkService $linkService,
        TelegramMessageService $messages,
    ): void {
        if (trim($token) === '') {
            $messages->send($chat, $messages->helpText());

            return;
        }

        $user = $linkService->connect($token, $chat);

        if ($user === null) {
            $messages->send($chat,
                'Код привязки неверный или уже истёк. ' .
                'Получите новый код в настройках сайта.',
            );

            return;
        }

        $messages->send(
            $chat,
            "Telegram подключён к аккаунту {$user->name}.\n\n" .
            $messages->helpText(),
        );
    }
}
