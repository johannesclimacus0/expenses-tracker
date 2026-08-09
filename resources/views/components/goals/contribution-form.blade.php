@props(['goal'])

<form method="POST" action="{{ route('goals.contributions.store', $goal) }}" class="space-y-4">
    @csrf

    <x-forms.select name="type" label="Операция" required>
        <option value="deposit" @selected(old('type', 'deposit') === 'deposit')>Пополнение</option>
        <option value="withdrawal" @selected(old('type') === 'withdrawal')>Снятие</option>
    </x-forms.select>
    <x-forms.input name="amount" label="Сумма" type="number" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" required />
    <x-forms.input name="contributed_at" label="Дата и время" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required />
    <x-forms.textarea name="note" label="Заметка" rows="2" maxlength="255" />

    <button type="submit" class="h-10 w-full rounded-xl bg-slate-900 px-4 text-xs font-medium text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">Сохранить</button>
</form>
