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

    const categoryModal = document.querySelector('[data-category-modal]');

    if (categoryModal) {
        const categoryName = categoryModal.querySelector('[data-category-name]');

        document.querySelectorAll('[data-category-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                categoryModal.showModal();
                categoryName?.focus();
            });
        });

        categoryModal.querySelectorAll('[data-category-modal-close]').forEach((button) => {
            button.addEventListener('click', () => categoryModal.close());
        });

        categoryModal.addEventListener('click', (event) => {
            if (event.target === categoryModal) {
                categoryModal.close();
            }
        });

        if (categoryModal.dataset.openOnLoad === 'true') {
            categoryModal.showModal();
            categoryName?.focus();
        }
    }
});
