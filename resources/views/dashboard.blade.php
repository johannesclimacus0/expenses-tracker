<x-layouts.app-layout title="Обзор">
    @php
        $month = $dashboard->month ?? now();
        $balance = $dashboard->balance ?? '0';
        $income = $dashboard->income ?? '0';
        $expenses = $dashboard->expenses ?? '0';
        $budgetUsage = $dashboard->budgetUsage ?? collect();
        $latestTransactions = $dashboard->latestTransactions ?? collect();
    @endphp

    <div class="mx-auto max-w-5xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Обзор</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('settings.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Настройки</a>
            <span class="text-xs text-slate-400">{{ $month->format('m.Y') }}</span>
        </header>

        <section class="overflow-hidden rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
            <x-dashboard.period-filter :period="$dashboard->period->value" :month="$month" />
            <x-dashboard.summary :balance="$balance" :income="$income" :expenses="$expenses" />
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <x-dashboard.budgets :usage="$budgetUsage" />
            <x-dashboard.latest-transactions :transactions="$latestTransactions" />
        </div>
    </div>
</x-layouts.app-layout>
