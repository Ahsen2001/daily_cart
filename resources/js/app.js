import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.overflow-x-auto').forEach((container) => {
        if (container.querySelector('table')) {
            container.classList.add('dc-table-wrap', 'dc-scrollbar');
        }
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form:not([data-allow-multiple-submit])').forEach((form) => {
        form.addEventListener('submit', (event) => {
            window.setTimeout(() => {
                if (event.defaultPrevented) {
                    return;
                }

                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                });
            }, 0);
        });
    });
});
