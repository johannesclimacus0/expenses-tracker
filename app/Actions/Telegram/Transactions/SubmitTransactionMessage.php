<?php

declare(strict_types=1);

namespace App\Actions\Telegram\Transactions;

use App\DTOs\Telegram\TransactionDialogState;
use App\Enums\TransactionDialogStep;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\TelegramChat;
use App\Services\Telegram\TelegramMessageService;
use App\Services\Telegram\TelegramTransactionDialogService;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

final readonly class SubmitTransactionMessage
{
    public function handle(
        TelegramChat $chat,
        string $input,
        TelegramTransactionDialogService $dialog,
        CompleteTransactionDialog $completeTransaction,
        TelegramMessageService $messages,
    ): void {
        $state = $dialog->get($chat);

        if ($state === null) {
            $messages->send($chat, $messages->helpText());

            return;
        }

        match ($state->step) {
            TransactionDialogStep::Amount => $this->handleAmount(
                $chat,
                $state,
                trim($input),
                $dialog,
                $messages,
            ),
            TransactionDialogStep::Comment => $this->handleComment(
                $chat,
                $state,
                trim($input),
                $completeTransaction,
                $messages,
            ),
            default => $this->reset(
                $chat,
                'Сценарий создания операции устарел. Отправьте /operations ещё раз.',
                $dialog,
                $messages,
            ),
        };
    }

    private function handleAmount(
        TelegramChat $chat,
        TransactionDialogState $state,
        string $input,
        TelegramTransactionDialogService $dialog,
        TelegramMessageService $messages,
    ): void {
        $amount = str_replace([' ', ','], ['', '.'], $input);

        if (!is_numeric($amount) || (float) $amount <= 0) {
            $messages->send(
                $chat,
                "Введите корректную сумму больше нуля.\n\n" .
                'Например: 450,50',
            );

            return;
        }

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            $messages->send(
                $chat,
                'Сумма может содержать не более двух знаков после запятой.',
            );

            return;
        }

        if ($state->type === null) {
            $this->reset($chat, 'Тип операции потерян. Отправьте /operations ещё раз.', $dialog, $messages);

            return;
        }

        $dialog->put($chat, $state->withAmount($amount));
        $this->sendCategoryKeyboard($chat, $state->type, $dialog, $messages);
    }

    private function handleComment(
        TelegramChat $chat,
        TransactionDialogState $state,
        string $comment,
        CompleteTransactionDialog $completeTransaction,
        TelegramMessageService $messages,
    ): void {
        if ($comment === '') {
            $messages->send($chat, 'Введите комментарий или /skip.');

            return;
        }

        if (mb_strlen($comment) > 255) {
            $messages->send($chat, 'Комментарий не должен превышать 255 символов.');

            return;
        }

        $completeTransaction->handle($chat, $state, $comment);
    }

    private function sendCategoryKeyboard(
        TelegramChat $chat,
        TransactionType $type,
        TelegramTransactionDialogService $dialog,
        TelegramMessageService $messages
    ): void {
        $user = $chat->user;

        if ($user === null) {
            $this->reset($chat, 'Сначала привяжите Telegram в настройках сайта.', $dialog, $messages);

            return;
        }

        $categories = Category::query()
            ->where('user_id', $user->id)
            ->where('type', $type->value)
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            $this->reset(
                $chat,
                'Для этого типа операции нет категорий. ' .
                'Сначала создайте категорию на сайте.',
                $dialog,
                $messages,
            );

            return;
        }

        $buttons = $categories
            ->map(
                static fn (Category $category): Button => Button::make($category->name)
                    ->action('chooseTransactionCategory')
                    ->param('category_id', (string) $category->id),
            )
            ->all();
        $keyboard = Keyboard::make()->buttons($buttons)->chunk(2);

        $chat->message('Выберите категорию:')->keyboard($keyboard)->send();
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
