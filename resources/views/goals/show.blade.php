<x-layouts.app-layout :title="$goal->name">
    <div class="mx-auto max-w-4xl">
        <header class="mb-5 flex items-center gap-4">
            <div>
                <a href="{{ route('goals.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Цели</a>
                <h1 class="mt-1 text-lg font-semibold tracking-tight text-slate-900">{{ $goal->name }}</h1>
            </div>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('goals.edit', $goal) }}" class="text-xs text-slate-400 transition hover:text-slate-900">Изменить</a>
        </header>

        <x-auth.status :message="session('status')" />
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div class="space-y-5">
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs text-slate-400">Накоплено</p>
                            <p class="mt-1 text-xl font-semibold text-slate-900"><x-money :amount="$progress->currentAmount" /></p>
                        </div>
                        <p class="text-xs text-slate-400">из <x-money :amount="$goal->target_amount" /></p>
                    </div>
                    <x-goals.progress :progress="$progress" class="mt-5" />
                    <div class="mt-3 flex justify-between text-xs text-slate-400">
                        <span>Осталось <x-money :amount="$progress->remainingAmount" /></span>
                        <span>{{ $progress->percentage }}%</span>
                    </div>
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs">
                        <span class="text-slate-400">{{ $goal->deadline ? 'Срок '.$goal->deadline->format('d.m.Y') : 'Без срока' }}</span>
                        <span class="font-medium text-slate-600">{{ $progress->isOverdue ? 'Срок истёк' : $goal->status->label() }}</span>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
                    @forelse ($contributions as $contribution)
                        <x-goals.contribution-row :goal="$goal" :contribution="$contribution" />
                    @empty
                        <div class="px-5 py-12 text-center text-xs text-slate-400">Операций пока нет</div>
                    @endforelse
                    <x-pagination.simple :paginator="$contributions" />
                </section>
            </div>

            @can('contribute', $goal)
                <aside class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="mb-4 text-sm font-semibold text-slate-900">Изменить прогресс</h2>
                    <x-goals.contribution-form :goal="$goal" />
                </aside>
            @endcan
        </div>
    </div>
</x-layouts.app-layout>
