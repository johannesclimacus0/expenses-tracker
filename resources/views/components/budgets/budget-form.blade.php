@props([
    'action',
    'categories',
    'method' => 'POST',
    'budget' => null,
    'submitLabel' => 'Сохранить',
])

@php
    $selectedCategory = old('category_id', $budget?->category_id);
    $selectedMonth = old(
        'month',
        $budget?->month?->format('Y-m') ?? request('month', now()->format('Y-m')),
    );
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if (!in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    <x-forms.input
        name="amount"
        label="Сумма"
        type="number"
        :value="$budget?->amount"
        min="0.01"
        step="0.01"
        inputmode="decimal"
        placeholder="0.00"
        required
        autofocus
    />

    <x-forms.input
        name="month"
        label="Месяц"
        type="month"
        :value="$selectedMonth"
        required
    />

    <x-forms.select name="category_id" label="Категория">
            <option value="">Общий бюджет</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
    </x-forms.select>

    <div class="flex gap-2 pt-1">
        <a href="{{ route('budgets.index', ['month' => $selectedMonth]) }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Отмена
        </a>
        <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
            {{ $submitLabel }}
        </button>
    </div>
</form>
