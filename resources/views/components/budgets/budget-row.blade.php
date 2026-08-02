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

        <div class="hidden shrink-0 items-center gap-3 sm:flex">
            <a href="{{ route('budgets.edit', $budget) }}" class="text-xs text-slate-400 transition hover:text-slate-900">
                Изменить
            </a>
            <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Удалить бюджет?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600">Удалить</button>
            </form>
        </div>

        <details class="relative shrink-0 sm:hidden">
            <summary class="cursor-pointer list-none rounded-lg px-2 py-1 text-xs text-slate-400 transition hover:bg-stone-100 hover:text-slate-900">
                Ещё
            </summary>
            <div class="absolute right-0 z-10 mt-2 w-28 overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200">
                <a href="{{ route('budgets.edit', $budget) }}" class="block px-3 py-2 text-xs text-slate-600 hover:bg-stone-50">
                    Изменить
                </a>
                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Удалить бюджет?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                        Удалить
                    </button>
                </form>
            </div>
        </details>
    </div>
</article>
