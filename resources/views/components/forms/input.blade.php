@props([
    'name',
    'label',
    'type' => 'text',
    'error' => null,
    'value' => null,
])

<div>
    <div class="mb-1.5 flex items-center justify-between gap-4">
        <label for="{{$name}}" class="block text-xs font-medium text-slate-600">
            {{$label}}
        </label>

        @isset($action)
            {{$action}}
        @endisset
    </div>

    <input
        id="{{$name}}"
        name="{{$name}}"
        type="{{$type}}"
        @if ($type !== 'password')
            value="{{ old($name, $value) }}"
        @endif
        {{ $attributes->class([
            'block h-10 w-full rounded-xl border bg-stone-50 px-3.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400',
            'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has($error ?? $name),
            'border-transparent hover:border-slate-300 focus:border-slate-500 focus:bg-white' => !$errors->has($error ?? $name),
        ]) }}
    >

    @error($error ?? $name)
    <p class="mt-1.5 text-xs text-red-600">
        {{$message}}
    </p>
    @enderror
</div>
