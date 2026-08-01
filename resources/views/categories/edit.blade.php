<x-layouts.app-layout title="Изменить категорию">
    <div class="mx-auto max-w-md">
        <a href="{{ route('categories.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
            Категории
        </a>
        <header class="mb-5 mt-3">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Изменить категорию</h1>
            <p class="mt-1 text-xs leading-5 text-slate-500">Обновите название или назначение категории.</p>
        </header>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <x-forms.input
                    name="name"
                    label="Название"
                    :value="$category->name"
                    maxlength="50"
                    required
                    autofocus
                />

                <x-categories.type-picker :selected="$category->type->value" />

                <div class="flex gap-2 pt-1">
                    <a href="{{ route('categories.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Отмена
                    </a>
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
                        Сохранить
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app-layout>
