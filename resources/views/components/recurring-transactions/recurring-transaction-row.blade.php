@props(['recurringTransaction'])

<article class="group border-b border-slate-100 px-4 py-4 last:border-b-0 sm:px-5">
    <div class="flex items-center gap-3">
        <span @class([
            'h-8 w-0.5 shrink-0 rounded-full',
            'bg-rose-400' => $recurringTransaction->type->value === 'expense',
            'bg-emerald-400' => $recurringTransaction->type->value === 'income',
        ])></span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <p class="truncate text-xs font-medium text-slate-900">
                    {{ $recurringTransaction->description ?: ($recurringTransaction->category?->name ?? 'Без категории') }}
                </p>
                <span @class([
                    'rounded-full px-2 py-0.5 text-[10px] font-medium',
                    'bg-emerald-50 text-emerald-700' => $recurringTransaction->is_active,
                    'bg-stone-100 text-slate-400' => ! $recurringTransaction->is_active,
                ])>
                    {{ $recurringTransaction->is_active ? 'Активна' : 'Остановлена' }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-400">
                {{ $recurringTransaction->period->label() }}
                @if ($recurringTransaction->category)
                    · {{ $recurringTransaction->category->name }}
                @endif
            </p>
        </div>

        <div class="shrink-0 text-right">
            <p @class([
                'text-xs font-semibold tabular-nums',
                'text-rose-600' => $recurringTransaction->type->value === 'expense',
                'text-emerald-600' => $recurringTransaction->type->value === 'income',
            ])>
                <x-money :amount="$recurringTransaction->amount" :sign="$recurringTransaction->type->value === 'expense' ? '−' : '+'" />
            </p>
            <time datetime="{{ $recurringTransaction->next_run_at->toISOString() }}" class="mt-1 block text-[10px] text-slate-400">
                {{ $recurringTransaction->next_run_at->format('d.m.Y, H:i') }}
            </time>
        </div>

        <x-list.actions
            :edit-route="route('recurring-transactions.edit', $recurringTransaction)"
            :delete-route="route('recurring-transactions.destroy', $recurringTransaction)"
            confirmation="Удалить регулярную транзакцию?"
        />
    </div>
</article>
