<?php

declare(strict_types=1);

namespace Tests\Feature;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Telegraph as TelegraphClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TelegramCommandsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_registers_commands_for_the_requested_bot(): void
    {
        Telegraph::fake();
        $bot = TelegraphBot::query()->create([
            'token' => 'requested-bot-token',
            'name' => 'Requested bot',
        ]);

        $this->artisan('telegraph:commands', ['bot' => $bot->id])
            ->expectsOutput("Команды Telegram-бота {$bot->id} зарегистрированы.")
            ->assertSuccessful();

        Telegraph::assertSentData(TelegraphClient::ENDPOINT_REGISTER_BOT_COMMANDS, [
            'commands' => [
                [
                    'command' => 'operations',
                    'description' => 'Меню операций',
                ],
                [
                    'command' => 'burunyu',
                    'description' => 'nyaghaA',
                ],
            ],
        ]);
    }

    public function test_command_fails_when_the_requested_bot_does_not_exist(): void
    {
        Telegraph::fake();

        $this->artisan('telegraph:commands', ['bot' => 999])
            ->expectsOutput('Telegram-бот с ID 999 не найден.')
            ->assertFailed();

        Telegraph::assertNotSentData(TelegraphClient::ENDPOINT_REGISTER_BOT_COMMANDS);
    }
}
