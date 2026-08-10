@props(['account' => null, 'currencies'])

<x-forms.input
    name="name"
    label="Название"
    :value="$account?->name"
    placeholder="Например, Семейный"
    maxlength="100"
    required
    {{ $attributes }}
/>

<x-forms.select name="currency" label="Валюта" required>
    @foreach ($currencies as $currency)
        <option value="{{ $currency->value }}" @selected(old('currency', $account?->currency->value) === $currency->value)>
            {{ $currency->label() }} ({{ $currency->symbol() }})
        </option>
    @endforeach
</x-forms.select>
