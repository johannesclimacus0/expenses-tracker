<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TelegramChat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class SendRandomPhraseJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1;

    public function __construct(
        private readonly int $chatId,
    ) {}

    public function handle(): void
    {
        $chat = TelegramChat::query()->find($this->chatId);

        if ($chat === null) {
            return;
        }

        $telegramChatId = (string) $chat->chat_id;
        $cacheKey = "burunyu:{$telegramChatId}";

        if (!Cache::get($cacheKey, false)) {
            return;
        }

        $phrases = [
            'NYEGAA',
            'NYAGAAA!!!',
            'Doridoridori',
            'BURENYUUU',
            'NYA',
            'puritti buoyy',
            'NYAAAAAAHHHH',
            'Huyn huyn huyn',
            'Burunyu',
            'China~ :3',
            'Dori Dori Dori Dori Dori',
            'NYA Nya Nya ... Nya Nya nya',
            'NYEGROOOO!',

        ];

        $phrase = $phrases[array_rand($phrases)];

        $chat
            ->message($phrase)
            ->send();

        self::dispatch($chat->getKey())
            ->delay(now()->addSeconds(1));
    }

    public function failed(Throwable $exception): void
    {
        logger()->error('SendRandomPhraseJob failed', [
            'chat_id' => $this->chatId,
            'message' => $exception->getMessage(),
            'exception' => $exception,
        ]);
    }
}
