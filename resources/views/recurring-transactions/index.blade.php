<x-layouts.app-layout title="Регулярные транзакции">
    <div class="mx-auto max-w-4xl">
        <header class="mb-5 flex items-center gap-4">
            <div>
                <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Транзакции</a>
                <h1 class="mt-1 text-lg font-semibold tracking-tight text-slate-900">Регулярные</h1>
            </div>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('recurring-transactions.create') }}" title="Новая регулярная транзакция" class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">
                <span>+</span>
            </a>
        </header>

        <x-auth.status :message="session('status')" />

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
            @forelse ($recurringTransactions as $recurringTransaction)
                <x-recurring-transactions.recurring-transaction-row :recurring-transaction="$recurringTransaction" />
            @empty
                <div class="px-5 py-16 text-center">
                    <p class="text-sm font-medium text-slate-700">Регулярных транзакций пока нет</p>
                    <a href="{{ route('recurring-transactions.create') }}" class="mt-3 inline-block text-xs text-slate-400 transition hover:text-slate-900">
                        Создать первую
                    </a>
                </div>
            @endforelse

            <x-pagination.simple :paginator="$recurringTransactions" />
        </section>
    </div>
</x-layouts.app-layout>
