(() => {
    'use strict';

    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.primary-nav');

    if (navToggle && nav) {
        navToggle.addEventListener('click', () => {
            const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!isOpen));
            nav.classList.toggle('is-open', !isOpen);
            document.body.classList.toggle('nav-open', !isOpen);
        });

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navToggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
                document.body.classList.remove('nav-open');
            });
        });
    }

    const header = document.querySelector('.app-header');
    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan');
            button.innerHTML = `<i class="bi bi-eye${shouldShow ? '-slash' : ''}"></i>`;
        });
    });

    document.querySelectorAll('.toast-notice').forEach((toast) => {
        const close = () => toast.classList.add('is-hidden');
        toast.querySelector('button')?.addEventListener('click', close);
        window.setTimeout(close, 6000);
    });

    const revealItems = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && revealItems.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('[data-current-year]').forEach((node) => {
        node.textContent = new Date().getFullYear();
    });

    document.querySelectorAll('[data-submit-loading]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...';
            button.classList.add('is-loading');
        });
    });
})();
