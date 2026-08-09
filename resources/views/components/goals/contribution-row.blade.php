@props(['goal', 'contribution'])

<div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 last:border-b-0 sm:px-5">
    <span @class([
        'h-7 w-0.5 shrink-0 rounded-full',
        'bg-emerald-400' => $contribution->type->value === 'deposit',
        'bg-rose-400' => $contribution->type->value === 'withdrawal',
    ])></span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-800">{{ $contribution->note ?: $contribution->type->label() }}</p>
        <time datetime="{{ $contribution->contributed_at->toISOString() }}" class="mt-1 block text-[10px] text-slate-400">{{ $contribution->contributed_at->format('d.m.Y, H:i') }}</time>
    </div>
    <p @class([
        'text-xs font-semibold tabular-nums',
        'text-emerald-600' => $contribution->type->value === 'deposit',
        'text-rose-600' => $contribution->type->value === 'withdrawal',
    ])>
        <x-money :amount="$contribution->amount" :sign="$contribution->type->value === 'deposit' ? '+' : '−'" />
    </p>
    <form method="POST" action="{{ route('goals.contributions.destroy', [$goal, $contribution]) }}" onsubmit="return confirm('Удалить операцию?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600">Удалить</button>
    </form>
</div>
