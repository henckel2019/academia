(() => {
    const toggle = document.querySelector('.site-menu-button');
    const menu = document.querySelector('.site-mobile-nav');
    function closeMenu() {
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menu');
        document.body.style.overflow = '';
    }
    toggle.addEventListener('click', () => {
        const open = menu.hidden;
        menu.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        document.body.style.overflow = open ? 'hidden' : '';
    });
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
        if (menu.hidden) return;
        if (event.key === 'Escape') {
            closeMenu();
            toggle.focus();
        }
        if (event.key === 'Tab') {
            const lastLink = menu.querySelector('a:last-child');
            if (!event.shiftKey && document.activeElement === lastLink) {
                event.preventDefault();
                toggle.focus();
            } else if (event.shiftKey && document.activeElement === toggle) {
                event.preventDefault();
                lastLink.focus();
            }
        }
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) closeMenu();
    });
})();
