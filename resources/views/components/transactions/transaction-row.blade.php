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

    <x-list.actions
        :edit-route="route('transactions.edit', $transaction)"
        :delete-route="route('transactions.destroy', $transaction)"
        confirmation="Удалить транзакцию?"
    />
</article>
