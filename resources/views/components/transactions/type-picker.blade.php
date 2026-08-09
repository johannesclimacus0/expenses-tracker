@props([
    'selected' => 'expense',
])

<fieldset>
    <legend class="mb-2 text-xs font-medium text-slate-600">Тип операции<span class="text-red-500"> *</span></legend>

    <div class="grid grid-cols-2 gap-2">
        <label class="cursor-pointer">
            <input type="radio" name="type" value="expense" class="peer sr-only" required @checked(old('type', $selected) === 'expense')>
            <span class="flex h-10 items-center justify-center rounded-xl bg-stone-50 text-xs font-medium text-slate-500 ring-1 ring-inset ring-transparent transition hover:bg-stone-100 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-checked:ring-rose-200 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-500">Расход</span>
        </label>
        <label class="cursor-pointer">
            <input type="radio" name="type" value="income" class="peer sr-only" required @checked(old('type', $selected) === 'income')>
            <span class="flex h-10 items-center justify-center rounded-xl bg-stone-50 text-xs font-medium text-slate-500 ring-1 ring-inset ring-transparent transition hover:bg-stone-100 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:ring-emerald-200 peer-focus-visible:ring-2 peer-focus-visible:ring-slate-500">Доход</span>
        </label>
    </div>

    @error('type')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</fieldset>
