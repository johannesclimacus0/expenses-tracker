<x-layouts.app-layout title="Транзакции">
    <div class="mx-auto max-w-5xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Транзакции</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('transactions.create') }}" title="Новая транзакция" class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">
                <span>+</span>
            </a>
        </header>

        <x-auth.status :message="session('status')" />

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid items-start gap-5 lg:grid-cols-[10.5rem_minmax(0,1fr)] lg:gap-6">
            <x-transactions.filters :categories="$categories" />

            <section class="mt-5 rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200 lg:mt-0">
                @forelse ($transactions as $transaction)
                    <x-transactions.transaction-row :transaction="$transaction" />
                @empty
                    <div class="px-5 py-14 text-center">
                        <p class="text-sm font-medium text-slate-700">Транзакций пока нет</p>
                    </div>
                @endforelse

                <x-transactions.pagination :paginator="$transactions" />
            </section>
        </div>
    </div>
</x-layouts.app-layout>
