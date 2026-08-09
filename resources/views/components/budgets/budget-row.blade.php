@props([
    'item',
])

@php
    $budget = $item->budget;
@endphp

<article class="border-b border-slate-100 px-4 py-4 last:border-b-0 sm:px-5">
    <div class="flex items-start gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-baseline justify-between gap-4">
                <p class="truncate text-xs font-medium text-slate-800">
                    {{ $budget->isOverall() ? 'Общий бюджет' : $budget->category->name }}
                </p>
                <p class="shrink-0 text-xs font-semibold tabular-nums text-slate-900">
                    <x-money :amount="$budget->amount" />
                </p>
            </div>

            <x-budgets.progress :item="$item" class="mt-3" />

            <div class="mt-2 flex items-center justify-between gap-4 text-xs">
                <span class="text-slate-400">
                    <x-money :amount="$item->spent" /> · {{ $item->percentage }}%
                </span>
                <span @class([
                    'tabular-nums text-slate-400' => ! $item->warning && ! $item->exceeded,
                    'tabular-nums text-amber-600' => $item->warning,
                    'tabular-nums text-red-600' => $item->exceeded,
                ])>
                    <x-money :amount="$item->remaining" />
                </span>
            </div>
        </div>

        <x-list.actions
            :edit-route="route('budgets.edit', $budget)"
            :delete-route="route('budgets.destroy', $budget)"
            confirmation="Удалить бюджет?"
        />
    </div>
</article>
