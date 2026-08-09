@props([
    'action',
    'method' => 'POST',
    'categories',
    'transaction' => null,
    'submitLabel' => 'Сохранить',
])

@php
    $selectedType = old('type', $transaction?->type?->value ?? 'expense');
    $selectedCategory = old('category_id', $transaction?->category_id);
    $occurredAt = old('occurred_at', $transaction?->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if (!in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif
    <x-transactions.type-picker :selected="$selectedType" />

    <x-forms.input
        name="amount"
        label="Сумма"
        type="number"
        :value="$transaction?->amount"
        min="0.01"
        step="0.01"
        inputmode="decimal"
        placeholder="0.00"
        required
    />

    <x-forms.select name="category_id" label="Категория">
            <option value="">Без категории</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                    {{ $category->name }} · {{ $category->type->value === 'expense' ? 'расход' : 'доход' }}
                </option>
            @endforeach
    </x-forms.select>

    <x-forms.input
        name="occurred_at"
        label="Дата и время"
        type="datetime-local"
        :value="$occurredAt"
        required
    />
    <x-forms.textarea
        name="description"
        label="Описание"
        :value="$transaction?->description"
        rows="3"
        maxlength="255"
        placeholder="Например, продукты на неделю"
    />

    <div class="flex gap-2 pt-1">
        <a href="{{ route('transactions.index') }}"
            class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
        >
        Отмена
        </a>
        <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
            {{ $submitLabel }}
        </button>
    </div>
</form>
