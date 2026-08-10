@props([
    'currencies',
    'open' => false,
])

<dialog data-account-modal data-open-on-load="{{ $open ? 'true' : 'false' }}" class="m-auto w-full max-w-sm rounded-2xl bg-white p-0 text-slate-900 shadow-xl backdrop:bg-slate-900/30 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-6">
        <div class="mb-5 flex items-start justify-between gap-4">
            <h2 class="text-sm font-semibold text-slate-900">Новый счет</h2>
            <button type="button" data-account-modal-close class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                Закрыть
            </button>
        </div>

        <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4">
            @csrf
            <x-accounts.form-fields :currencies="$currencies" autofocus data-account-name />
            <x-buttons.primary-button type="submit">Создать счет</x-buttons.primary-button>
        </form>
    </div>
</dialog>
