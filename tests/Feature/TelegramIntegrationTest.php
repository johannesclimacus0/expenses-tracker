<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDialogStep;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\TelegramChat;
use App\Models\User;
use App\Services\Telegram\TelegramTransactionDialogService;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class TelegramIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'telegraph.webhook.secret' => 'testing-webhook-secret',
        ]);
    }

    public function test_user_can_get_token_and_connect_telegram_chat(): void
    {
        Telegraph::fake();
        $user = User::factory()->create();
        $bot = $this->createBot();

        $response = $this->actingAs($user)
            ->post(route('settings.telegram.token'))
            ->assertRedirect()
            ->assertSessionHas('telegram_link_token');

        $token = $response->getSession()->get('telegram_link_token');

        $this->sendWebhook($bot, '10001', "/start {$token}")
            ->assertNoContent();

        $this->assertDatabaseHas('telegraph_chats', [
            'chat_id' => '10001',
            'user_id' => $user->id,
        ]);
    }

    public function test_linked_chat_can_create_income_and_expense_by_category(): void
    {
        Telegraph::fake();
        $user = User::factory()->create();
        $bot = $this->createBot();
        $this->createLinkedChat($bot, $user, '10002');

        $incomeCategory = Category::factory()->for($user)->create([
            'name' => 'Зарплата',
            'type' => TransactionType::Income,
        ]);
        $expenseCategory = Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        $this->sendWebhook($bot, '10002', '/income 1000 Зарплата | аванс')->assertNoContent();
        $this->sendWebhook($bot, '10002', '/expense 450,50 Продукты | молоко')->assertNoContent();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'type' => TransactionType::Income->value,
            'amount' => '1000.00',
            'description' => 'аванс',
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'type' => TransactionType::Expense->value,
            'amount' => '450.50',
            'description' => 'молоко',
        ]);
    }

    public function test_unlinked_chat_cannot_create_transaction(): void
    {
        Telegraph::fake();
        $user = User::factory()->create();
        $bot = $this->createBot();
        Category::factory()->for($user)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        $this->sendWebhook($bot, '10003', '/expense 100 Продукты')->assertNoContent();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseMissing('telegraph_chats', [
            'chat_id' => '10003',
        ]);
    }

    public function test_category_must_belong_to_user_and_match_transaction_type(): void
    {
        Telegraph::fake();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $bot = $this->createBot();
        $this->createLinkedChat($bot, $user, '10004');

        Category::factory()->for($otherUser)->create([
            'name' => 'Чужая',
            'type' => TransactionType::Expense,
        ]);
        Category::factory()->for($user)->create([
            'name' => 'Зарплата',
            'type' => TransactionType::Income,
        ]);

        $this->sendWebhook($bot, '10004', '/expense 100 Чужая')->assertNoContent();
        $this->sendWebhook($bot, '10004', '/expense 100 Зарплата')->assertNoContent();

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_type_callback_starts_dialog(): void
    {
        Telegraph::fake();
        $user = User::factory()->create();
        $bot = $this->createBot();
        $this->createLinkedChat($bot, $user, '10007');

        $this->sendCallback(
            $bot,
            '10007',
            'action:chooseTransactionType;type:expense',
        )->assertNoContent();

        $chat = TelegramChat::query()->where('chat_id', '10007')->firstOrFail();
        $state = app(TelegramTransactionDialogService::class)->get($chat);

        $this->assertSame(TransactionDialogStep::Amount, $state?->step);
        $this->assertSame(TransactionType::Expense, $state?->type);
    }

    public function test_user_can_disconnect_telegram_chat(): void
    {
        $user = User::factory()->create();
        $chat = $this->createLinkedChat($this->createBot(), $user, '10005');

        $this->actingAs($user)
            ->delete(route('settings.telegram.destroy'))
            ->assertRedirect()
            ->assertSessionHas('status', 'telegram-disconnected');

        $this->assertNull($chat->refresh()->user_id);
    }

    public function test_webhook_secret_is_verified_when_configured(): void
    {
        Telegraph::fake();

        config([
            'telegraph.webhook.secret' => 'expected-secret',
        ]);

        $bot = $this->createBot();

        $this->sendWebhook($bot, '10006', '/help', 'wrong-secret')
            ->assertForbidden();

        $this->sendWebhook($bot, '10006', '/help', 'expected-secret')
            ->assertNoContent();
    }

    private function createBot(): TelegraphBot
    {
        return TelegraphBot::query()->create([
            'token' => 'test-bot-token',
            'name' => 'Test bot',
        ]);
    }

    private function createLinkedChat(TelegraphBot $bot, User $user, string $chatId): TelegramChat
    {
        $chat = new TelegramChat;
        $chat->chat_id = $chatId;
        $chat->name = 'Test chat';
        $chat->telegraph_bot_id = $bot->id;
        $chat->user()->associate($user);
        $chat->save();

        return $chat;
    }

    private function sendWebhook(TelegraphBot $bot, string $chatId, string $text, ?string $secret = null): TestResponse
    {
        $secret ??= (string) config('telegraph.webhook.secret');

        return $this
            ->withHeader('X-Telegram-Bot-Api-Secret-Token', $secret)
            ->postJson(route('telegraph.webhook', $bot), [
                'update_id' => random_int(1, 999999),
                'message' => [
                    'message_id' => random_int(1, 999999),
                    'date' => now()->timestamp,
                    'text' => $text,
                    'from' => [
                        'id' => (int) $chatId,
                        'is_bot' => false,
                        'first_name' => 'Test',
                    ],
                    'chat' => [
                        'id' => (int) $chatId,
                        'type' => 'private',
                        'first_name' => 'Test',
                    ],
                ],
            ]);
    }

    private function sendCallback(
        TelegraphBot $bot,
        string $chatId,
        string $data,
    ): TestResponse {
        return $this
            ->withHeader(
                'X-Telegram-Bot-Api-Secret-Token',
                (string) config('telegraph.webhook.secret'),
            )
            ->postJson(route('telegraph.webhook', $bot), [
                'update_id' => random_int(1, 999999),
                'callback_query' => [
                    'id' => (string) random_int(1, 999999),
                    'from' => [
                        'id' => (int) $chatId,
                        'is_bot' => false,
                        'first_name' => 'Test',
                    ],
                    'message' => [
                        'message_id' => random_int(1, 999999),
                        'date' => now()->timestamp,
                        'text' => 'Choose transaction type',
                        'chat' => [
                            'id' => (int) $chatId,
                            'type' => 'private',
                            'first_name' => 'Test',
                        ],
                    ],
                    'chat_instance' => 'test-chat-instance',
                    'data' => $data,
                ],
            ]);
    }
}
