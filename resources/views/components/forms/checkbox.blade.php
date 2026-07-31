@props([
    'name',
    'label',
])

<label class="flex w-fit cursor-pointer items-center gap-2.5 text-xs text-slate-500">
    <input
        name="{{$name}}"
        type="checkbox"
        value="1"
        @checked(old($name))
        class={{ $attributes->class([
            'size-4 rounded border-slate-300 bg-stone-50 text-slate-900 focus:ring-offset-2',
        ]) }}
    >
    <span>{{$label}}</span>
</label>
