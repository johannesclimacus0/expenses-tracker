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
    <fieldset>
        <legend class="mb-2 text-xs font-medium text-slate-600">Тип операции<span class="text-red-500"> *</span></legend>

        <div class="grid grid-cols-2 gap-2">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="expense" class="peer sr-only" required@checked($selectedType === 'expense')>
                <span class="flex h-10 items-center justify-center rounded-xl bg-stone-50 text-xs font-medium text-slate-500 ring-1 ring-inset ring-transparent transition hover:bg-stone-100 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-checked:ring-rose-200 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-500">
                    Расход
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="type" value="income" class="peer sr-only" required@checked($selectedType === 'income')>
                <span class="flex h-10 items-center justify-center rounded-xl bg-stone-50 text-xs font-medium text-slate-500 ring-1 ring-inset ring-transparent transition hover:bg-stone-100 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:ring-emerald-200 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-500">
                    Доход
                </span>
            </label>
        </div>
        @error('type')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>

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

    <div>
        <label for="category_id" class="mb-1.5 block text-xs font-medium text-slate-600">Категория</label>
        <select id="category_id" name="category_id" class="block h-10 w-full rounded-xl border border-transparent bg-stone-50 px-3.5 text-xs text-slate-900 outline-none transition hover:border-slate-300 focus:border-slate-500 focus:bg-white">
            <option value="">Без категории</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                    {{ $category->name }} · {{ $category->type->value === 'expense' ? 'расход' : 'доход' }}
                </option>
            @endforeach
        </select>

        @error('category_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <x-forms.input
        name="occurred_at"
        label="Дата и время"
        type="datetime-local"
        :value="$occurredAt"
        required
    />
    <div>
        <label for="description" class="mb-1.5 block text-xs font-medium text-slate-600">
            Описание
        </label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Например, продукты на неделю"
            class="block w-full resize-none rounded-xl border border-transparent bg-stone-50 px-3.5 py-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-slate-500 focus:bg-white"
        >{{ old('description', $transaction?->description) }}
        </textarea>

        @error('description')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

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
