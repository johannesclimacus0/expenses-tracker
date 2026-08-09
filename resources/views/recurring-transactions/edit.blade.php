<x-layouts.app-layout title="Изменить регулярную транзакцию">
    <div class="mx-auto max-w-md">
        <a href="{{ route('recurring-transactions.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Регулярные
        </a>

        <header class="mb-5 mt-3">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Изменить регулярную транзакцию</h1>
        </header>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <x-recurring-transactions.recurring-transaction-form
                :action="route('recurring-transactions.update', $recurringTransaction)"
                method="PATCH"
                :categories="$categories"
                :recurring-transaction="$recurringTransaction"
                submit-label="Сохранить"
            />
        </section>
    </div>
</x-layouts.app-layout>
