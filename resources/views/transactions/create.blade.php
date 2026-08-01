<x-layouts.app-layout title="Новая транзакция">
    <div class="mx-auto max-w-md">
        <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Транзакции
        </a>

        <header class="mb-5 mt-3">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Новая транзакция</h1>
            <p class="mt-1 text-xs leading-5 text-slate-500">Добавьте доход или расход.</p>
        </header>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <x-transactions.transaction-form
                :action="route('transactions.store')"
                :categories="$categories"
                submit-label="Добавить"
            />
        </section>
    </div>
</x-layouts.app-layout>
