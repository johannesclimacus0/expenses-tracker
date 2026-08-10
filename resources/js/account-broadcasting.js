const createTransactionToast = (transaction) => {
    document.querySelector('[data-transaction-broadcast-toast]')?.remove();

    const toast = document.createElement('div');
    toast.dataset.transactionBroadcastToast = '';
    toast.className = 'fixed right-4 bottom-4 z-50 flex max-w-sm items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl shadow-slate-900/10 sm:right-6 sm:bottom-6';
    toast.setAttribute('role', 'status');

    const marker = document.createElement('span');
    marker.className = 'mt-1 size-2 shrink-0 rounded-full bg-emerald-500';

    const content = document.createElement('div');
    content.className = 'min-w-0 flex-1';

    const title = document.createElement('p');
    title.className = 'text-xs font-semibold text-slate-900';
    title.textContent = 'В текущем счете новая транзакция';

    const description = document.createElement('p');
    description.className = 'mt-1 truncate text-xs text-slate-500';
    description.textContent = transaction.description || 'Без описания';

    const refresh = document.createElement('button');
    refresh.type = 'button';
    refresh.className = 'mt-3 text-xs font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4 transition hover:decoration-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900';
    refresh.textContent = 'Обновить данные';
    refresh.addEventListener('click', () => window.location.reload());

    content.append(title, description, refresh);
    toast.append(marker, content);
    document.body.append(toast);

    window.setTimeout(() => toast.remove(), 10000);
};

document.addEventListener('DOMContentLoaded', () => {
    const accountElement = document.querySelector('[data-active-account-uuid]');
    const accountUuid = accountElement?.dataset.activeAccountUuid;

    if (!accountUuid || !window.Echo) {
        return;
    }

    window.Echo.private(`accounts.${accountUuid}`)
        .listen('.transaction.created', (transaction) => {
            createTransactionToast(transaction);

            const refreshablePages = [
                '/dashboard',
                '/transactions',
                '/budgets',
            ];

            if (refreshablePages.includes(window.location.pathname)) {
                window.setTimeout(() => window.location.reload(),3000);
            }
        });
});
