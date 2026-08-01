@props([
    'open' => false,
])

<dialog data-category-modal data-open-on-load="{{ $open ? 'true' : 'false' }}" class="m-auto w-full max-w-sm rounded-2xl bg-white p-0 text-slate-900 shadow-xl backdrop:bg-slate-900/30 backdrop:backdrop-blur-sm"
>
    <div class="p-5 sm:p-6">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h2 id="category-modal-title" class="text-sm font-semibold text-slate-900">Новая категория</h2>
            </div>
            <button type="button" data-category-modal-close class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                Закрыть
            </button>
        </div>

        <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
            @csrf

            <x-forms.input
                name="name"
                label="Название"
                placeholder="Например, продукты"
                maxlength="50"
                required
                data-category-name
            />

            <x-categories.type-picker />

            <x-buttons.primary-button type="submit">
                Создать категорию
            </x-buttons.primary-button>
        </form>
    </div>
</dialog>
