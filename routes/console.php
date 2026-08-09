<?php

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Console\Command\Command;

Schedule::command('transactions:process-recurring')->everyMinute()->withoutOverlapping();

Artisan::command('telegraph:commands {bot : ID Telegram-бота в базе данных}', function (int $bot) {
    $telegraphBot = TelegraphBot::query()->find($bot);

    if ($telegraphBot === null) {
        $this->error("Telegram-бот с ID {$bot} не найден.");

        return Command::FAILURE;
    }

    $telegraphBot->registerCommands([
        'operations' => 'Меню операций',
        'burunyu' => 'nyaghaA',
    ])->send();

    $this->info("Команды Telegram-бота {$bot} зарегистрированы.");

    return Command::SUCCESS;
});
