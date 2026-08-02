@props([
    'balance',
    'income',
    'expenses',
])

<div class="grid sm:grid-cols-3 sm:divide-x sm:divide-slate-100">
    <div class="p-5">
        <p class="text-xs text-slate-400">Баланс</p>
        <p class="mt-2 text-lg font-semibold tabular-nums text-slate-900">
            <x-money :amount="$balance" />
        </p>
    </div>
    <div class="border-t border-slate-100 p-5 sm:border-t-0">
        <p class="text-xs text-slate-400">Доходы</p>
        <p class="mt-2 text-lg font-semibold tabular-nums text-emerald-600">
            <x-money :amount="$income" sign="+" />
        </p>
    </div>
    <div class="border-t border-slate-100 p-5 sm:border-t-0">
        <p class="text-xs text-slate-400">Расходы</p>
        <p class="mt-2 text-lg font-semibold tabular-nums text-rose-600">
            <x-money :amount="$expenses" sign="−" />
        </p>
    </div>
</div>
