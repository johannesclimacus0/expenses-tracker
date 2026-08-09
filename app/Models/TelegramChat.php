<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat as BaseTelegraphChat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $chat_id
 * @property string|null $name
 * @property int $telegraph_bot_id
 * @property int|null $user_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read TelegraphBot $bot
 * @property-read User|null $user
 *
 * @method static \DefStudio\Telegraph\Database\Factories\TelegraphChatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereTelegraphBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TelegramChat whereUserId($value)
 *
 * @mixin \Eloquent
 */
final class TelegramChat extends BaseTelegraphChat
{
    protected $table = 'telegraph_chats';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
