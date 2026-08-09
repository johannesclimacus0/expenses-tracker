<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Telegram\Transactions\CancelTransactionDialog;
use App\Actions\Telegram\Transactions\SelectTransactionCategory;
use App\Actions\Telegram\Transactions\SelectTransactionType;
use App\Actions\Telegram\Transactions\SubmitTransactionMessage;
use App\Enums\TransactionDialogStep;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\TelegramChat;
use App\Models\User;
use App\Services\Telegram\TelegramTransactionDialogService;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TelegramTransactionDialogTest extends TestCase
{
    use RefreshDatabase;

    public function test_dialog_creates_transaction_and_clears_state(): void
    {
        Telegraph::fake();

        [$chat, $category] = $this->createChatWithCategory();

        $this->runAction(SelectTransactionType::class, [
            'chat' => $chat,
            'value' => TransactionType::Expense->value,
        ]);
        $this->runAction(SubmitTransactionMessage::class, [
            'chat' => $chat,
            'input' => '450,50',
        ]);

        $dialog = app(TelegramTransactionDialogService::class);
        $this->assertSame(TransactionDialogStep::Category, $dialog->get($chat)?->step);

        $this->runAction(SelectTransactionCategory::class, [
            'chat' => $chat,
            'value' => $category->id,
        ]);
        $this->runAction(SubmitTransactionMessage::class, [
            'chat' => $chat,
            'input' => 'молоко',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $chat->user_id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense->value,
            'amount' => '450.50',
            'description' => 'молоко',
        ]);
        $this->assertNull($dialog->get($chat));
    }

    public function test_dialog_can_be_cancelled(): void
    {
        Telegraph::fake();

        [$chat] = $this->createChatWithCategory();

        $this->runAction(SelectTransactionType::class, [
            'chat' => $chat,
            'value' => TransactionType::Expense->value,
        ]);
        $this->runAction(CancelTransactionDialog::class, ['chat' => $chat]);

        $this->assertNull(app(TelegramTransactionDialogService::class)->get($chat));
        $this->assertDatabaseCount('transactions', 0);
    }

    private function runAction(string $action, array $parameters): mixed
    {
        return app()->call([app($action), 'handle'], $parameters);
    }

    private function createChatWithCategory(): array
    {
        $user = User::factory()->create();
        $bot = TelegraphBot::query()->create([
            'token' => fake()->sha256(),
            'name' => 'Test bot',
        ]);
        $chat = new TelegramChat;
        $chat->chat_id = (string) fake()->unique()->numberBetween(10000, 99999);
        $chat->name = 'Test chat';
        $chat->telegraph_bot_id = $bot->id;
        $chat->user()->associate($user);
        $chat->save();
        $category = Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        return [$chat, $category];
    }
}
