<x-layouts.app-layout title="Категории">
    <div class="mx-auto max-w-4xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Категории</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <button type="button" data-category-modal-open title="Новая категория"
                class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
            >
            <span>+</span>
            </button>
        </header>

        <x-auth.status :message="session('status')" />

        <div class="overflow-hidden rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
            <div class="grid md:grid-cols-2 md:divide-x md:divide-slate-100">
                <x-categories.category-list
                    title="Расходы"
                    type="expense"
                    :categories="$expenseCategories"
                />

                <x-categories.category-list
                    title="Доходы"
                    type="income"
                    :categories="$incomeCategories"
                    class="border-t border-slate-100 md:border-t-0"
                />
            </div>
        </div>
    </div>

    <x-categories.create-modal :open="$errors->any()" />
</x-layouts.app-layout>
