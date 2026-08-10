document.addEventListener('DOMContentLoaded', () => {
    const startCooldown = (buttonSelector, countdownSelector, messageSelector) => {
        const button = document.querySelector(buttonSelector);
        const countdown = document.querySelector(countdownSelector);
        const message = document.querySelector(messageSelector);

        if (!button || !countdown) {
            return;
        }

        let seconds = Number.parseInt(button.dataset.retryAfter ?? '0', 10);

        if (!Number.isFinite(seconds) || seconds <= 0) {
            button.disabled = false;
            message?.remove();
            return;
        }

        countdown.textContent = String(seconds);

        const timer = window.setInterval(() => {
            seconds -= 1;
            countdown.textContent = String(Math.max(0, seconds));

            if (seconds <= 0) {
                window.clearInterval(timer);
                button.disabled = false;
                message?.remove();
            }
        }, 1000);
    };

    startCooldown(
        '[data-throttle-submit]',
        '[data-throttle-countdown]',
        '[data-throttle-message]',
    );

    const setupModal = (modalSelector, openSelector, closeSelector, focusSelector) => {
        const modal = document.querySelector(modalSelector);

        if (!modal) {
            return;
        }

        const focusTarget = modal.querySelector(focusSelector);

        document.querySelectorAll(openSelector).forEach((button) => {
            button.addEventListener('click', () => {
                modal.showModal();
                focusTarget?.focus();
            });
        });

        modal.querySelectorAll(closeSelector).forEach((button) => {
            button.addEventListener('click', () => modal.close());
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.close();
            }
        });

        if (modal.dataset.openOnLoad === 'true') {
            modal.showModal();
            focusTarget?.focus();
        }
    };

    setupModal('[data-category-modal]', '[data-category-modal-open]', '[data-category-modal-close]', '[data-category-name]');
    setupModal('[data-account-modal]', '[data-account-modal-open]', '[data-account-modal-close]', '[data-account-name]');
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
