@props([
    'settings',
    'currencies',
    'dashboardPeriods',
])

<form method="POST" action="{{ route('settings.update') }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
    @csrf
    @method('PATCH')

    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Интерфейс</h2>
            <p class="mt-1 text-xs text-slate-400">Отображение финансовых данных</p>
        </div>
        @if (session('status') === 'settings-updated')
            <span class="shrink-0 text-xs font-medium text-emerald-600">Сохранено</span>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Валюта<span class="text-red-500"> *</span></span>
            <select name="currency" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->value }}" @selected(old('currency', $settings->currency->value) === $currency->value)>
                        {{ $currency->label() }} — {{ $currency->symbol() }}
                    </option>
                @endforeach
            </select>
            @error('currency')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Период обзора<span class="text-red-500"> *</span></span>
            <select name="dashboard_period" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
                @foreach ($dashboardPeriods as $period)
                    <option value="{{ $period->value }}" @selected(old('dashboard_period', $settings->dashboard_period->value) === $period->value)>{{ $period->label() }}</option>
                @endforeach
            </select>
            @error('dashboard_period')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Транзакций на странице<span class="text-red-500"> *</span></span>
            <select name="transactions_per_page" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
                @foreach ([10, 20, 50] as $count)
                    <option value="{{ $count }}" @selected((int) old('transactions_per_page', $settings->transactions_per_page) === $count)>{{ $count }}</option>
                @endforeach
            </select>
            @error('transactions_per_page')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Предупреждение бюджета, %<span class="text-red-500"> *</span></span>
            <input type="number" name="budget_warning_percent" min="1" max="100" value="{{ old('budget_warning_percent', $settings->budget_warning_percent) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
            @error('budget_warning_percent')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <label class="mt-4 flex cursor-pointer items-center justify-between rounded-xl bg-stone-50 px-3.5 py-3">
        <span class="text-xs font-medium text-slate-600">Показывать копейки</span>
        <input type="hidden" name="show_cents" value="0">
        <input type="checkbox" name="show_cents" value="1" @checked((bool) old('show_cents', $settings->show_cents)) class="size-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500">
    </label>
    @error('show_cents')
        <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
    @enderror

    <div class="mt-5 flex justify-end">
        <button type="submit" class="rounded-full bg-slate-900 px-4 py-2.5 text-xs font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            Сохранить настройки
        </button>
    </div>
</form>
