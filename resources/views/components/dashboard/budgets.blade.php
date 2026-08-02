@props(['usage'])

<section>
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-800">Бюджеты</h2>
        <a href="{{ route('budgets.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Все</a>
    </div>
    <div class="rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
        @forelse ($usage as $item)
            <div class="border-b border-slate-100 px-4 py-3.5 last:border-b-0">
                <div class="flex items-center justify-between gap-4 text-xs">
                    <span class="truncate font-medium text-slate-700">
                        {{ $item->budget->isOverall() ? 'Общий бюджет' : $item->budget->category->name }}
                    </span>
                    <span @class([
                        'tabular-nums text-slate-400' => ! $item->warning && ! $item->exceeded,
                        'tabular-nums text-amber-600' => $item->warning,
                        'tabular-nums text-red-600' => $item->exceeded,
                    ])>
                        {{ $item->percentage }}%
                    </span>
                </div>
                <x-budgets.progress :item="$item" class="mt-2" />
            </div>
        @empty
            <p class="px-4 py-8 text-center text-xs text-slate-400">Пока пусто</p>
        @endforelse
    </div>
</section>
