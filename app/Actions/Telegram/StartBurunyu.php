<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Jobs\SendRandomPhraseJob;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use Illuminate\Support\Facades\Cache;

final readonly class StartBurunyu
{
    public function handle(TelegramChat $chat, TelegramMessageService $messages): void
    {
        if (!$messages->ensureLinked($chat)) {
            return;
        }

        $chatModelId = $chat->getKey();

        if ($chatModelId === null) {
            $messages->send($chat, 'Не удалось определить Telegram-чат.');

            return;
        }

        $cacheKey = "burunyu:{$chat->chat_id}";

        if (!Cache::get($cacheKey, false)) {
            Cache::forever($cacheKey, true);
            SendRandomPhraseJob::dispatch((int) $chatModelId);
        }

        $messages->send($chat, '/stop_burunyu');
    }
}
