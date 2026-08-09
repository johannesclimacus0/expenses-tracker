@props([
    'action',
    'method' => 'POST',
    'goal' => null,
    'statuses' => [],
    'submitLabel' => 'Сохранить',
])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    <x-forms.input name="name" label="Название" :value="$goal?->name" maxlength="255" placeholder="Например, отпуск" required autofocus />
    <x-forms.input name="target_amount" label="Целевая сумма" type="number" :value="$goal?->target_amount" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" required />
    <x-forms.input name="deadline" label="Срок" type="date" :value="$goal?->deadline?->format('Y-m-d')" />

    @if ($goal)
        <x-forms.select name="status" label="Статус" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $goal->status->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-forms.select>
    @endif

    <div class="flex gap-2 pt-1">
        <a href="{{ $goal ? route('goals.show', $goal) : route('goals.index') }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">Отмена</a>
        <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">{{ $submitLabel }}</button>
    </div>
</form>
