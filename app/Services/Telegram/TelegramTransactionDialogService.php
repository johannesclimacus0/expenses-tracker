<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\DTOs\Telegram\TransactionDialogState;
use App\Models\TelegramChat;
use Illuminate\Support\Facades\Cache;

final class TelegramTransactionDialogService
{
    private const int TTL_MINUTES = 30;

    public function get(TelegramChat $chat): ?TransactionDialogState
    {
        $state = Cache::get($this->key($chat));

        return is_array($state) ? TransactionDialogState::fromArray($state) : null;
    }

    public function put(TelegramChat $chat, TransactionDialogState $state): void
    {
        Cache::put(
            $this->key($chat),
            $state->toArray(),
            now()->addMinutes(self::TTL_MINUTES),
        );
    }

    public function has(TelegramChat $chat): bool
    {
        return Cache::has($this->key($chat));
    }

    public function forget(TelegramChat $chat): void
    {
        Cache::forget($this->key($chat));
    }

    private function key(TelegramChat $chat): string
    {
        return "telegram:transaction-dialog:{$chat->chat_id}";
    }
}
