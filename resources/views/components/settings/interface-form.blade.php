@props([
    'settings',
    'currencies',
    'dashboardPeriods',
])

<x-settings.form-section
    :action="route('settings.update')"
    title="Интерфейс"
    description="Отображение финансовых данных"
    status="settings-updated"
    submit-label="Сохранить настройки"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.select name="currency" label="Валюта" required>
            @foreach ($currencies as $currency)
                <option value="{{ $currency->value }}" @selected(old('currency', $settings->currency->value) === $currency->value)>
                    {{ $currency->label() }} — {{ $currency->symbol() }}
                </option>
            @endforeach
        </x-forms.select>

        <x-forms.select name="dashboard_period" label="Период обзора" required>
            @foreach ($dashboardPeriods as $period)
                <option value="{{ $period->value }}" @selected(old('dashboard_period', $settings->dashboard_period->value) === $period->value)>
                    {{ $period->label() }}
                </option>
            @endforeach
        </x-forms.select>

        <x-forms.select name="transactions_per_page" label="Транзакций на странице" required>
            @foreach ([10, 20, 50] as $count)
                <option value="{{ $count }}" @selected((int) old('transactions_per_page', $settings->transactions_per_page) === $count)>{{ $count }}</option>
            @endforeach
        </x-forms.select>

        <x-forms.input
            name="budget_warning_percent"
            label="Предупреждение бюджета, %"
            type="number"
            :value="$settings->budget_warning_percent"
            min="1"
            max="100"
            required
        />
    </div>

    <x-forms.toggle
        name="show_cents"
        label="Показывать копейки"
        :checked="$settings->show_cents"
        class="mt-4"
    />
</x-settings.form-section>
