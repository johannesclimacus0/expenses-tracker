@props([
    'action',
    'method' => 'POST',
    'categories',
    'recurringTransaction' => null,
    'submitLabel' => 'Сохранить',
])

@php
    $selectedType = old('type', $recurringTransaction?->type?->value ?? 'expense');
    $selectedCategory = old('category_id', $recurringTransaction?->category_id);
    $selectedPeriod = old('period', $recurringTransaction?->period?->value ?? 'monthly');
    $startsAt = old('starts_at', $recurringTransaction?->starts_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $isActive = (bool) old('is_active', $recurringTransaction?->is_active ?? true);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    <x-transactions.type-picker :selected="$selectedType" />

    <x-forms.input name="amount" label="Сумма" type="number" :value="$recurringTransaction?->amount" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" required />

    <x-forms.select name="category_id" label="Категория">
            <option value="">Без категории</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                    {{ $category->name }} · {{ $category->type->value === 'expense' ? 'расход' : 'доход' }}
                </option>
            @endforeach
    </x-forms.select>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.select name="period" label="Повторение" required>
                @foreach (\App\Enums\RecurringPeriod::cases() as $period)
                    <option value="{{ $period->value }}" @selected($selectedPeriod === $period->value)>{{ $period->label() }}</option>
                @endforeach
        </x-forms.select>

        <x-forms.input name="starts_at" label="Начало" type="datetime-local" :value="$startsAt" required />
    </div>

    <x-forms.textarea name="description" label="Описание" :value="$recurringTransaction?->description" rows="3" maxlength="255" placeholder="Например, аренда квартиры" />

    <x-forms.toggle
        name="is_active"
        label="Активна"
        description="Создавать транзакции по расписанию"
        :checked="$isActive"
    />

    <div class="flex gap-2 pt-1">
        <a href="{{ route('recurring-transactions.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">Отмена</a>
        <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">{{ $submitLabel }}</button>
    </div>
</form>
