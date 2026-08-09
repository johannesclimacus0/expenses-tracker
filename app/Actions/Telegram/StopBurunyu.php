<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use Illuminate\Support\Facades\Cache;

final readonly class StopBurunyu
{
    public function handle(TelegramChat $chat, TelegramMessageService $messages): void
    {
        Cache::forget("burunyu:{$chat->chat_id}");
        $messages->send($chat, ':(');
    }
}
