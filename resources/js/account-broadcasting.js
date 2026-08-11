const transactionMessages = {
    created: {
        title: 'В текущем счёте новая транзакция',
        fallback: 'Без описания',
    },
    updated: {
        title: 'Транзакция в текущем счёте изменена',
        fallback: 'Данные транзакции обновлены',
    },
    deleted: {
        title: 'Транзакция в текущем счёте удалена',
        fallback: 'Список транзакций изменился',
    },
};

const createTransactionToast = (eventName, transaction) => {
    document.querySelector('[data-transaction-broadcast-toast]')?.remove();
    const message = transactionMessages[eventName];

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
    title.textContent = message.title;

    const description = document.createElement('p');
    description.className = 'mt-1 truncate text-xs text-slate-500';
    description.textContent = transaction.description || message.fallback;

    const refresh = document.createElement('button');
    refresh.type = 'button';
    refresh.className = 'mt-3 text-xs font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4 transition hover:decoration-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900';
    refresh.textContent = 'Обновить данные';
    refresh.addEventListener('click', () => window.location.reload());

    content.append(title, description, refresh);
    toast.append(marker, content);
    document.body.append(toast);

    window.setTimeout(() => toast.remove(), 15000);
};

const createConnectionToast = ({ title, description, recovered = false }) => {
    document.querySelector('[data-websocket-status-toast]')?.remove();

    const toast = document.createElement('div');
    toast.dataset.websocketStatusToast = '';
    toast.className = 'fixed top-4 right-4 z-50 flex max-w-sm items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-xl shadow-slate-900/10 sm:top-6 sm:right-6';
    toast.setAttribute('role', recovered ? 'status' : 'alert');

    const marker = document.createElement('span');
    marker.className = `mt-1 size-2 shrink-0 rounded-full ${recovered ? 'bg-emerald-500' : 'bg-amber-500'}`;

    const content = document.createElement('div');
    content.className = 'min-w-0 flex-1';

    const heading = document.createElement('p');
    heading.className = 'text-xs font-semibold text-slate-900';
    heading.textContent = title;

    const details = document.createElement('p');
    details.className = 'mt-1 text-xs text-slate-600';
    details.textContent = description;

    content.append(heading, details);

    if (recovered) {
        const refresh = document.createElement('button');
        refresh.type = 'button';
        refresh.className = 'mt-3 text-xs font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4 transition hover:decoration-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900';
        refresh.textContent = 'Обновить данные';
        refresh.addEventListener('click', () => window.location.reload());
        content.append(refresh);
    }

    toast.append(marker, content);
    document.body.append(toast);
};

document.addEventListener('DOMContentLoaded', () => {
    const accountElement = document.querySelector('[data-active-account-uuid]');
    const accountUuid = accountElement?.dataset.activeAccountUuid;

    if (!accountUuid || !window.Echo) {
        return;
    }

    window.Echo.private(`accounts.${accountUuid}`)
        .listen('.transaction.created', (transaction) => {
            createTransactionToast('created', transaction);
        })
        .listen('.transaction.updated', (transaction) => {
            createTransactionToast('updated', transaction);
        })
        .listen('.transaction.deleted', (transaction) => {
            createTransactionToast('deleted', transaction);
        });
});
