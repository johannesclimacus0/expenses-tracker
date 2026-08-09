<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Models\TelegramChat;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TelegramLinkService
{
    private const int TOKEN_TTL_MINUTES = 15;

    public function issue(User $user): string
    {
        $token = Str::upper(Str::random(12));

        Cache::put(
            $this->cacheKey($token),
            $user->getKey(),
            now()->addMinutes(self::TOKEN_TTL_MINUTES),
        );

        return $token;
    }

    public function connect(string $token, TelegramChat $chat): ?User
    {
        $userId = Cache::pull($this->cacheKey($token));

        if (!is_int($userId)) {
            return null;
        }

        return DB::transaction(function () use ($userId, $chat): ?User {
            $user = User::query()->lockForUpdate()->find($userId);

            if ($user === null) {
                return null;
            }

            $existingChats = TelegramChat::query()->where('user_id', $user->getKey());

            if ($chat->exists) {
                $existingChats->whereKeyNot($chat->getKey());
            }

            $existingChats->update(['user_id' => null]);

            $chat->user()->associate($user);
            $chat->save();

            return $user;
        });
    }

    public function disconnect(User $user): void
    {
        $user->telegramChat()->update(['user_id' => null]);
    }

    private function cacheKey(string $token): string
    {
        return 'telegram-link:' . hash('sha256', Str::upper(trim($token)));
    }
}
