<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Actions\Telegram\ConnectTelegramChat;
use App\Actions\Telegram\CreateTelegramTransaction;
use App\Actions\Telegram\SendUnknownTelegramCommand;
use App\Actions\Telegram\ShowTelegramHelp;
use App\Actions\Telegram\StartBurunyu;
use App\Actions\Telegram\StopBurunyu;
use App\Actions\Telegram\Transactions\CancelTransactionDialog;
use App\Actions\Telegram\Transactions\SelectTransactionCategory;
use App\Actions\Telegram\Transactions\SelectTransactionType;
use App\Actions\Telegram\Transactions\SkipTransactionComment;
use App\Actions\Telegram\Transactions\StartTransactionDialog;
use App\Actions\Telegram\Transactions\SubmitTransactionMessage;
use App\Enums\TransactionType;
use App\Models\TelegramChat;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Stringable;

final class TelegramWebhookHandler extends WebhookHandler
{
    public function burunyu(StartBurunyu $startBurunyu): void
    {
        $this->run($startBurunyu);
    }

    public function stop_burunyu(StopBurunyu $stopBurunyu): void
    {
        $this->run($stopBurunyu);
    }

    public function operations(StartTransactionDialog $startTransactionDialog): void
    {
        $this->run($startTransactionDialog);
    }

    public function chooseTransactionType(string $type, SelectTransactionType $selectTransactionType): void
    {
        $this->run($selectTransactionType, ['value' => $type]);
    }

    public function chooseTransactionCategory(
        mixed $category_id,
        SelectTransactionCategory $selectTransactionCategory
    ): void {
        $this->run($selectTransactionCategory, ['value' => $category_id]);
    }

    public function skip(SkipTransactionComment $skipTransactionComment): void
    {
        $this->run($skipTransactionComment);
    }

    public function cancel(CancelTransactionDialog $cancelTransactionDialog): void
    {
        $this->run($cancelTransactionDialog);
    }

    public function start(string $input, ConnectTelegramChat $connectTelegramChat): void
    {
        $this->run($connectTelegramChat, ['token' => $input]);
    }

    public function income(
        string $input,
        CreateTelegramTransaction $createTelegramTransaction
    ): void {
        $this->run($createTelegramTransaction, [
            'type' => TransactionType::Income,
            'input' => $input,
        ]);
    }

    public function expense(
        string $input,
        CreateTelegramTransaction $createTelegramTransaction,
    ): void {
        $this->run($createTelegramTransaction, [
            'type' => TransactionType::Expense,
            'input' => $input,
        ]);
    }

    public function help(ShowTelegramHelp $showTelegramHelp): void
    {
        $this->run($showTelegramHelp);
    }

    protected function handleCommand(Stringable $text): void
    {
        [$command, $input] = $this->parseCommand($text);

        if (!$this->canHandle($command)) {
            $this->handleUnknownCommand($text);

            return;
        }

        App::call([$this, $command], ['input' => $input]);
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->run(App::make(SendUnknownTelegramCommand::class));
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->run(
            App::make(SubmitTransactionMessage::class),
            ['input' => (string) $text],
        );
    }

    private function run(object $action, array $parameters = []): mixed
    {
        return App::call(
            [$action, 'handle'],
            ['chat' => $this->telegramChat(), ...$parameters],
        );
    }

    private function telegramChat(): TelegramChat
    {
        /** @var TelegramChat $chat */
        $chat = $this->chat;

        return $chat;
    }
}
