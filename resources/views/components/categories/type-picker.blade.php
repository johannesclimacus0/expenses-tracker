@props([
    'selected' => 'expense',
])

@php
    $selectedType = old('type', $selected);
@endphp

<fieldset>
    <legend class="mb-1.5 text-xs font-medium text-slate-600">Тип</legend>
    <div class="grid grid-cols-2 gap-2">
        <label class="cursor-pointer">
            <input type="radio" name="type" value="expense" class="peer sr-only" required@checked($selectedType === 'expense')>
            <span class="flex h-10 items-center justify-center gap-2 rounded-xl bg-stone-50 text-xs font-medium text-slate-500 transition peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900">
                <span class="size-1.5 rounded-full bg-rose-400"></span>
                Расход
            </span>
        </label>
        <label class="cursor-pointer">
            <input type="radio" name="type" value="income" class="peer sr-only" required@checked($selectedType === 'income')>
            <span class="flex h-10 items-center justify-center gap-2 rounded-xl bg-stone-50 text-xs font-medium text-slate-500 transition peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900">
                <span class="size-1.5 rounded-full bg-emerald-400"></span>
                Доход
            </span>
        </label>
    </div>

    @error('type')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</fieldset>
