@props(['transactions'])

<section>
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-800">Последние транзакции</h2>
        <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Все</a>
    </div>
    <div class="rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
        @forelse ($transactions as $transaction)
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 last:border-b-0">
                <span @class([
                    'h-7 w-0.5 shrink-0 rounded-full',
                    'bg-rose-400' => $transaction->type->value === 'expense',
                    'bg-emerald-400' => $transaction->type->value === 'income',
                ])></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-medium text-slate-700">
                        {{ $transaction->description ?: ($transaction->category?->name ?? 'Без категории') }}
                    </p>
                    <time class="mt-1 block text-xs text-slate-400">{{ $transaction->occurred_at->format('d.m.Y') }}</time>
                </div>
                <span @class([
                    'shrink-0 text-xs font-semibold tabular-nums',
                    'text-rose-600' => $transaction->type->value === 'expense',
                    'text-emerald-600' => $transaction->type->value === 'income',
                ])>
                    <x-money :amount="$transaction->amount" :sign="$transaction->type->value === 'expense' ? '−' : '+'" />
                </span>
            </div>
        @empty
            <p class="px-4 py-8 text-center text-xs text-slate-400">Пока пусто</p>
        @endforelse
    </div>
</section>
