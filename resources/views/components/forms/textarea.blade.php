@props([
    'name',
    'label',
    'value' => null,
    'error' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-xs font-medium text-slate-600">
        {{ $label }}@if ($attributes->has('required'))<span class="text-red-500"> *</span>@endif
    </label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->class([
            'block w-full resize-none rounded-xl border bg-stone-50 px-3.5 py-3 text-xs text-slate-900 outline-none transition placeholder:text-slate-400',
            'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has($error ?? $name),
            'border-transparent hover:border-slate-300 focus:border-slate-500 focus:bg-white' => ! $errors->has($error ?? $name),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($error ?? $name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
