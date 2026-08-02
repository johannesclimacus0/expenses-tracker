@props([
    'period',
    'month',
])

<form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3">
    <fieldset class="mr-auto flex min-w-0 items-center gap-3">
        <legend class="sr-only">Период</legend>
        <span class="text-xs text-slate-400">Период</span>
        <div class="flex overflow-x-auto rounded-full bg-stone-100 p-1">
            @foreach (['month' => 'Месяц','quarter' => '3 месяца','year' => 'Год', 'all' => 'Всё время'] as $value => $label)
                <label class="shrink-0 cursor-pointer">
                    <input type="radio" name="period" value="{{ $value }}" class="peer sr-only" @checked(request('period', $period) === $value)>
                    <span class="block rounded-full px-3 py-1.5 text-xs text-slate-500 transition peer-checked:bg-white peer-checked:font-medium peer-checked:text-slate-900 peer-checked:shadow-sm">
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex items-center gap-2">
        <label class="flex items-center gap-2">
            <span class="text-xs text-slate-400">До</span>
            <input type="month" name="month" value="{{ request('month', $month->format('Y-m')) }}" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 outline-none transition focus:border-slate-500 focus:ring-0">
        </label>

        <button type="submit" class="rounded-full bg-slate-900 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            Показать
        </button>
    </div>
</form>
