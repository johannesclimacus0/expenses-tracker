<x-layouts.app-layout title="Новый бюджет">
    <div class="mx-auto max-w-md">
        <a href="{{ route('budgets.index', ['month' => request('month')]) }}" class="text-xs text-slate-400 transition hover:text-slate-900">
            Бюджеты
        </a>

        <h1 class="mb-5 mt-3 text-lg font-semibold tracking-tight text-slate-900">Новый бюджет</h1>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <x-budgets.budget-form
                :action="route('budgets.store')"
                :categories="$categories"
                submit-label="Добавить"
            />
        </section>
    </div>
</x-layouts.app-layout>
