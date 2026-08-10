@props([
    'accounts',
    'currentAccount',
])

@if ($currentAccount !== null && $accounts->isNotEmpty())
    <details class="group relative" data-active-account-uuid="{{ $currentAccount->uuid }}">
        <summary class="flex max-w-44 cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-xs font-medium text-slate-700 transition hover:border-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 sm:max-w-52 [&::-webkit-details-marker]:hidden">
            <span class="min-w-0 flex-1 truncate">{{ $currentAccount->name }}</span>
            <svg viewBox="0 0 20 20" fill="none" class="size-4 shrink-0 text-slate-400 transition group-open:rotate-180" aria-hidden="true">
                <path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </summary>

        <div class="absolute top-full right-0 z-30 mt-2 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
            <div class="space-y-0.5">
                @foreach ($accounts as $account)
                    @if ($account->is($currentAccount))
                        <div class="flex items-center gap-3 rounded-lg bg-stone-100 px-3 py-2 text-slate-900">
                            <span class="min-w-0 flex-1 truncate text-xs font-semibold">{{ $account->name }}</span>
                            <span class="size-1.5 shrink-0 rounded-full bg-emerald-500" title="Текущий счет"></span>
                        </div>
                    @else
                        <form method="POST" action="{{ route('accounts.current.update') }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="account_uuid" value="{{ $account->uuid }}">
                            <button type="submit" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-slate-600 transition hover:bg-stone-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                <span class="min-w-0 flex-1 truncate text-xs font-medium">{{ $account->name }}</span>
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
            <a href="{{ route('accounts.index') }}" class="mt-1 block border-t border-slate-100 px-3 py-2.5 text-xs font-medium text-slate-500 transition hover:text-slate-900">
                Счета
            </a>
        </div>
    </details>
@endif
