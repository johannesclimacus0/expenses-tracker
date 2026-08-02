@props([
    'transaction',
])

<article class="group flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 transition hover:bg-stone-50/80 last:border-b-0 sm:px-5">
    <span @class([
        'h-7 w-0.5 shrink-0 rounded-full',
        'bg-rose-400' => $transaction->type->value === 'expense',
        'bg-emerald-400' => $transaction->type->value === 'income',
    ])></span>

    <div class="min-w-0 flex-1">
        <div class="flex items-baseline gap-2">
            <p class="truncate text-xs font-medium text-slate-900">
                {{ $transaction->description ?: ($transaction->category?->name ?? 'Без категории') }}
            </p>
            @if ($transaction->description && $transaction->category)
                <span class="hidden truncate text-xs text-slate-400 sm:inline">{{ $transaction->category->name }}</span>
            @endif
        </div>
        <time datetime="{{ $transaction->occurred_at->toISOString() }}" class="mt-1 block text-xs text-slate-400">
            {{ $transaction->occurred_at->format('d.m.Y, H:i') }}
        </time>
    </div>

    <p @class([
        'min-w-24 shrink-0 text-right text-xs font-semibold tabular-nums',
        'text-rose-600' => $transaction->type->value === 'expense',
        'text-emerald-600' => $transaction->type->value === 'income',
    ])>
        <x-money :amount="$transaction->amount" :sign="$transaction->type->value === 'expense' ? '−' : '+'" />
    </p>

    <div class="hidden shrink-0 items-center gap-3 sm:flex">
        <a href="{{ route('transactions.edit', $transaction) }}" class="text-xs text-slate-400 transition hover:text-slate-900">Изменить</a>
        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Удалить транзакцию?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600">Удалить</button>
        </form>
    </div>

    <details class="relative shrink-0 sm:hidden">
        <summary class="cursor-pointer list-none rounded-lg px-2 py-1 text-xs text-slate-400 transition hover:bg-stone-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Ещё
        </summary>
        <div class="absolute right-0 z-10 mt-2 w-28 overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200">
            <a href="{{ route('transactions.edit', $transaction) }}" class="block px-3 py-2 text-xs text-slate-600 transition hover:bg-stone-50 hover:text-slate-900">
                Изменить
            </a>
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Удалить транзакцию?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-red-600 transition hover:bg-red-50">
                    Удалить
                </button>
            </form>
        </div>
    </details>
</article>
