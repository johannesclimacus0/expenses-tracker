<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\Enums\TransactionDialogStep;
use App\Models\Category;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;

final readonly class SelectTransactionCategory
{
    public function handle(
        TelegramChat $chat,
        mixed $value,
        TelegramTransactionDialogService $dialog,
        TelegramMessageService $messages,
    ): void {
        if (!$messages->ensureLinked($chat)) {
            return;
        }

        $state = $dialog->get($chat);

        if ($state === null || $state->step !== TransactionDialogStep::Category) {
            $messages->send($chat, 'Сценарий создания операции уже завершён или устарел. Отправьте /operations.');

            return;
        }

        $categoryId = filter_var($value, FILTER_VALIDATE_INT);

        if ($categoryId === false) {
            $messages->send($chat, 'Передана некорректная категория.');

            return;
        }

        if ($state->type === null) {
            $this->reset($chat, 'Тип операции потерян. Отправьте /operations ещё раз.', $dialog, $messages);

            return;
        }

        $category = Category::query()
            ->whereKey($categoryId)
            ->where('user_id', $chat->user_id)
            ->where('type', $state->type->value)
            ->first();

        if ($category === null) {
            $messages->send($chat, 'Категория не найдена или не принадлежит вашему аккаунту.');

            return;
        }

        $dialog->put($chat, $state->withCategory($category->id, $category->name));
        $messages->send($chat,
            "Выбрана категория: {$category->name}\n\n" .
            "Введите комментарий к операции.\n" .
            'Чтобы пропустить комментарий, отправьте /skip.'
        );
    }

    private function reset(
        TelegramChat $chat,
        string $message,
        TelegramTransactionDialogService $dialog,
        TelegramMessageService $messages,
    ): void {
        $dialog->forget($chat);
        $messages->send($chat, $message);
    }
}
