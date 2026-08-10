@php use App\Enums\AccountRole; @endphp
<x-layouts.app-layout title="Изменить счет">
    <div class="mx-auto max-w-md">
        <a href="{{ route('accounts.index') }}"
           class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
            Счета
        </a>
        <header class="mb-5 mt-3">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Изменить счет</h1>
        </header>

        <x-auth.status :message="session('status')"/>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <x-accounts.form-fields :account="$account" :currencies="$currencies" autofocus/>

                <div class="flex gap-2 pt-1">
                    <a href="{{ route('accounts.index') }}"
                       class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-stone-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-stone-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Отмена
                    </a>
                    <button type="submit"
                            class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
                        Сохранить
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <h2 class="text-sm font-semibold text-slate-900">Участники</h2>

            <div class="mt-4 divide-y divide-slate-100">
                @foreach ($account->members as $member)
                    <div class="flex items-center gap-3 py-3 first:pt-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-slate-700">{{ $member->user->name }}</p>
                            <p class="mt-1 truncate text-xs text-slate-400">{{ $member->user->email }}</p>
                        </div>

                        @if ($member->role === AccountRole::Owner)
                            <span class="text-xs text-slate-400">Владелец</span>
                        @else
                            <form method="POST" action="{{ route('accounts.members.destroy', [$account, $member]) }}"
                                  onsubmit="return confirm('Удалить участника из счета?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-slate-300 transition hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                    Удалить
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('accounts.members.store', $account) }}"
                  class="mt-4 flex items-end gap-2 border-t border-slate-100 pt-4">
                @csrf
                <div class="min-w-0 flex-1">
                    <x-forms.input name="email" label="Добавить по почте" type="email" placeholder="user@example.com"
                                   required/>
                </div>
                <button type="submit"
                        class="h-10 shrink-0 rounded-xl bg-slate-900 px-4 text-xs font-medium text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
                    Добавить
                </button>
            </form>
        </section>
    </div>
</x-layouts.app-layout>
