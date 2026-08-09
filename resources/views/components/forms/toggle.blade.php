@props([
    'name',
    'label',
    'description' => null,
    'checked' => false,
    'error' => null,
])

<div {{ $attributes }}>
    <label class="flex cursor-pointer items-center justify-between rounded-xl bg-stone-50 px-3.5 py-3">
        <span>
            <span class="block text-xs font-medium text-slate-700">{{ $label }}</span>
            @if ($description)
                <span class="mt-0.5 block text-xs text-slate-400">{{ $description }}</span>
            @endif
        </span>
        <input type="hidden" name="{{ $name }}" value="0">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="1"
            @checked((bool) old($name, $checked))
            class="size-4 rounded border-slate-300 bg-white text-slate-900 focus:ring-slate-500 focus:ring-offset-stone-50"
        >
    </label>

    @error($error ?? $name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
