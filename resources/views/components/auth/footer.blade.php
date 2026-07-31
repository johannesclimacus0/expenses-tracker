@props([
    'text',
    'link',
    'linkText',
])

<div class="mt-6 rounded-xl bg-stone-50 px-3 py-2.5">
    <p class="text-center text-xs text-slate-500">
        {{$text}}
        <a href="{{$link}}" class="font-medium text-slate-900 underline decoration-slate-700 decoration-1 underline-offset-4 hover:decoration-slate-900">
            {{$linkText}}
        </a>
    </p>
</div>
