@props(['goal', 'progress'])

<article class="border-b border-slate-100 px-4 py-4 last:border-b-0 sm:px-5">
    <div class="flex items-start gap-4">
        <a href="{{ route('goals.show', $goal) }}" class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-xs font-semibold text-slate-900">{{ $goal->name }}</h2>
                        <span @class([
                            'rounded-full px-2 py-0.5 text-[10px] font-medium',
                            'bg-emerald-50 text-emerald-700' => $goal->status->value === 'active' && ! $progress->isOverdue,
                            'bg-amber-50 text-amber-700' => $goal->status->value === 'paused' || $progress->isOverdue,
                            'bg-slate-100 text-slate-500' => $goal->status->value === 'cancelled',
                            'bg-emerald-100 text-emerald-800' => $goal->status->value === 'completed',
                        ])>
                            {{ $progress->isOverdue ? 'Срок истёк' : $goal->status->label() }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">
                        @if ($goal->deadline)
                            до {{ $goal->deadline->format('d.m.Y') }}
                        @else
                            без срока
                        @endif
                    </p>
                </div>
                <p class="shrink-0 text-xs font-semibold tabular-nums text-slate-900">
                    <x-money :amount="$progress->currentAmount" /> / <x-money :amount="$goal->target_amount" />
                </p>
            </div>

            <x-goals.progress :progress="$progress" class="mt-3" />
            <div class="mt-2 flex justify-between text-[10px] text-slate-400">
                <span>Осталось <x-money :amount="$progress->remainingAmount" /></span>
                <span>{{ $progress->percentage }}%</span>
            </div>
        </a>

        <x-list.actions
            :edit-route="route('goals.edit', $goal)"
            :delete-route="route('goals.destroy', $goal)"
            confirmation="Удалить цель и всю историю взносов?"
        />
    </div>
</article>
