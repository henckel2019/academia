(() => {
    document.querySelectorAll('.table-wrap').forEach((wrapper) => {
        if (wrapper.querySelector('.empty-state')) wrapper.classList.add('is-empty');
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Tabela de registros, use as setas para rolar');
    });
    const shell = document.querySelector('[data-app-shell]');
    const openButton = shell?.querySelector('[data-sidebar-open]');
    const sidebar = shell?.querySelector('[data-sidebar]');
    function closeMenu() {
        shell.classList.remove('sidebar-is-open');
        openButton?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        if (window.innerWidth <= 900) sidebar.inert = true;
    }
    function openMenu() {
        sidebar.inert = false;
        shell.classList.add('sidebar-is-open');
        openButton.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        sidebar.querySelector('[data-sidebar-close]')?.focus();
    }
    if (shell) {
        sidebar.inert = window.innerWidth <= 900;
        openButton?.addEventListener('click', openMenu);
        shell
            .querySelectorAll('[data-sidebar-close], .nav-link')
            .forEach((button) => button.addEventListener('click', closeMenu));
        document.addEventListener('keydown', (event) => {
            if (!shell.classList.contains('sidebar-is-open')) return;
            if (event.key === 'Escape') {
                closeMenu();
                openButton.focus();
            }
            if (event.key === 'Tab') {
                const targets = [...sidebar.querySelectorAll('a, button')].filter(
                    (element) => element.offsetParent !== null
                );
                if (event.shiftKey && document.activeElement === targets[0]) {
                    event.preventDefault();
                    targets.at(-1).focus();
                } else if (!event.shiftKey && document.activeElement === targets.at(-1)) {
                    event.preventDefault();
                    targets[0].focus();
                }
            }
        });
        window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
            closeMenu();
            sidebar.inert = !event.matches;
        });
    }
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            // A cancelled confirmation must leave the form usable.
            if (event.defaultPrevented) return;
            const submitter = event.submitter;
            if (!submitter || !form.checkValidity()) return;
            submitter.setAttribute('aria-busy', 'true');
            requestAnimationFrame(() => {
                submitter.disabled = true;
            });
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[aria-busy="true"]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        });
    });
})();
