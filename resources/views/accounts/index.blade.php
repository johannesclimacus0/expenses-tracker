<x-layouts.app-layout title="Счета">
    <div class="mx-auto max-w-3xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Счета</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <button type="button" data-account-modal-open title="Новый счет"
                class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
            >
                <span>+</span>
            </button>
        </header>

        <x-auth.status :message="session('status')" />

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="divide-y divide-slate-100">
                @foreach ($accounts as $account)
                    <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-slate-700">{{ $account->name }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $account->currency->value }}
                                @if ($account->users_count > 1)
                                    · {{ $account->users_count }} чел.
                                @endif
                            </p>
                        </div>

                        @can('update', $account)
                            <a href="{{ route('accounts.edit', $account) }}" class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                Изменить
                            </a>
                        @endcan

                        @can('delete', $account)
                            <form method="POST" action="{{ route('accounts.destroy', $account) }}" onsubmit="return confirm('Удалить счет и все его данные?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                    Удалить
                                </button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <x-accounts.create-modal :currencies="$currencies" :open="$errors->any()" />
</x-layouts.app-layout>
