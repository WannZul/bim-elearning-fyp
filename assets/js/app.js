(() => {
    'use strict';

    const messages = window.BIM_I18N || {};
    const accessibility = window.BIMAccessibility;
    const accessibilityEvent = 'bim:accessibility-change';

    const skipLink = document.querySelector('.skip-link');
    const mainContent = document.getElementById('main-content');
    skipLink?.addEventListener('click', (event) => {
        if (!mainContent) return;
        event.preventDefault();
        mainContent.focus({ preventScroll: true });
        mainContent.scrollIntoView({ block: 'start' });
    });

    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.primary-nav');
    const setNavOpen = (isOpen, returnFocus = false) => {
        if (!navToggle || !nav) return;
        navToggle.setAttribute('aria-expanded', String(isOpen));
        navToggle.setAttribute('aria-label', messages[isOpen ? 'nav.close' : 'nav.open'] || '');
        nav.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('nav-open', isOpen);
        if (!isOpen && returnFocus) navToggle.focus();
    };

    if (navToggle && nav) {
        navToggle.addEventListener('click', () => {
            const shouldOpen = navToggle.getAttribute('aria-expanded') !== 'true';
            if (shouldOpen && accessibilityPanel && !accessibilityPanel.hidden) setAccessibilityPanelOpen(false);
            setNavOpen(shouldOpen);
        });
        nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setNavOpen(false)));
        document.addEventListener('click', (event) => {
            if (navToggle.getAttribute('aria-expanded') === 'true' && !nav.contains(event.target) && !navToggle.contains(event.target)) {
                setNavOpen(false);
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && navToggle.getAttribute('aria-expanded') === 'true') {
                event.preventDefault();
                setNavOpen(false, true);
            }
        });
        window.addEventListener('resize', () => {
            if (window.matchMedia('(min-width: 901px)').matches) setNavOpen(false);
        });
    }

    const header = document.querySelector('.app-header');
    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    const accessibilityWidget = document.getElementById('accessibility-widget');
    const accessibilityTrigger = document.getElementById('accessibility-trigger');
    const accessibilityPanel = document.getElementById('accessibility-panel');
    const accessibilityTitle = document.getElementById('accessibility-title');
    const accessibilityClose = document.getElementById('accessibility-close');
    const accessibilityCloseAction = document.getElementById('accessibility-close-action');
    const accessibilityReset = document.getElementById('accessibility-reset');
    const accessibilityHighContrast = document.getElementById('accessibility-high-contrast');
    const accessibilityReduceMotion = document.getElementById('accessibility-reduce-motion');
    const accessibilityStatus = document.getElementById('accessibility-status');
    const textSizeInputs = Array.from(document.querySelectorAll('input[name="accessibility-text-size"]'));

    const announceAccessibility = (message) => {
        if (!accessibilityStatus || !message) return;
        accessibilityStatus.textContent = '';
        window.requestAnimationFrame(() => { accessibilityStatus.textContent = message; });
    };

    const renderAccessibilityPreferences = (preferences) => {
        if (!preferences) return;
        textSizeInputs.forEach((input) => { input.checked = input.value === preferences.textSize; });
        if (accessibilityHighContrast) accessibilityHighContrast.checked = preferences.highContrast;
        if (accessibilityReduceMotion) accessibilityReduceMotion.checked = preferences.reduceMotion;
    };

    const setAccessibilityPanelOpen = (isOpen, returnFocus = false) => {
        if (!accessibilityTrigger || !accessibilityPanel) return;
        accessibilityTrigger.setAttribute('aria-expanded', String(isOpen));
        accessibilityTrigger.setAttribute('aria-label', messages[isOpen ? 'accessibility.close' : 'accessibility.open'] || '');
        const triggerText = accessibilityTrigger.querySelector('span');
        if (triggerText) triggerText.textContent = messages[isOpen ? 'accessibility.close' : 'accessibility.open'] || '';
        accessibilityPanel.hidden = !isOpen;
        if (isOpen) accessibilityTitle?.focus();
        else if (returnFocus) accessibilityTrigger.focus();
    };

    const currentPanelPreferences = () => ({
        textSize: textSizeInputs.find((input) => input.checked)?.value || 'default',
        highContrast: Boolean(accessibilityHighContrast?.checked),
        reduceMotion: Boolean(accessibilityReduceMotion?.checked),
    });

    const updateAccessibility = (preferences, announcement) => {
        if (!accessibility) return;
        const saveResult = accessibility.save(preferences);
        const applied = accessibility.apply(saveResult.preferences);
        renderAccessibilityPreferences(applied);
        window.dispatchEvent(new CustomEvent(accessibilityEvent, { detail: applied }));
        announceAccessibility(saveResult.persisted ? announcement : messages['accessibility.save_failed'] || '');
    };

    if (accessibility && accessibilityWidget && accessibilityTrigger && accessibilityPanel) {
        renderAccessibilityPreferences(accessibility.load());
        accessibilityTrigger.addEventListener('click', () => {
            const shouldOpen = accessibilityTrigger.getAttribute('aria-expanded') !== 'true';
            if (shouldOpen) setNavOpen(false);
            setAccessibilityPanelOpen(shouldOpen, true);
        });
        [accessibilityClose, accessibilityCloseAction].forEach((button) => {
            button?.addEventListener('click', () => setAccessibilityPanelOpen(false, true));
        });
        [...textSizeInputs, accessibilityHighContrast, accessibilityReduceMotion].forEach((control) => {
            control?.addEventListener('change', () => updateAccessibility(currentPanelPreferences(), messages['accessibility.saved'] || ''));
        });
        accessibilityReset?.addEventListener('click', () => {
            updateAccessibility({ ...accessibility.defaults }, messages['accessibility.reset_confirmation'] || '');
        });
        document.addEventListener('click', (event) => {
            if (accessibilityPanel.hidden || accessibilityWidget.contains(event.target)) return;
            const activeElement = document.activeElement;
            const shouldReturnFocus = activeElement === document.body || accessibilityPanel.contains(activeElement);
            setAccessibilityPanelOpen(false, shouldReturnFocus);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !accessibilityPanel.hidden) {
                event.preventDefault();
                setAccessibilityPanelOpen(false, true);
            }
        });
        window.addEventListener('storage', (event) => {
            if (event.key !== accessibility.key && event.key !== null) return;
            const preferences = accessibility.apply(accessibility.load());
            renderAccessibilityPreferences(preferences);
            window.dispatchEvent(new CustomEvent(accessibilityEvent, { detail: preferences }));
        });
        window.addEventListener(accessibilityEvent, (event) => {
            const preferences = accessibility.apply(event.detail || accessibility.load());
            renderAccessibilityPreferences(preferences);
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-label', messages[shouldShow ? 'common.hide_password' : 'common.show_password'] || '');
            button.innerHTML = `<i class="bi bi-eye${shouldShow ? '-slash' : ''}" aria-hidden="true"></i>`;
        });
    });

    document.querySelectorAll('.toast-notice').forEach((toast) => {
        const close = () => {
            toast.classList.add('is-hidden');
            window.setTimeout(() => { toast.hidden = true; }, 260);
        };
        toast.querySelector('button')?.addEventListener('click', close);
        window.setTimeout(close, 6000);
    });

    const revealItems = Array.from(document.querySelectorAll('[data-reveal]'));
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let revealObserver = null;
    const effectiveReducedMotion = () => document.documentElement.dataset.reduceMotion === 'true' || motionPreference.matches;
    const updateRevealBehavior = () => {
        revealObserver?.disconnect();
        revealObserver = null;
        if (effectiveReducedMotion() || !('IntersectionObserver' in window)) {
            revealItems.forEach((item) => item.classList.add('is-visible'));
            return;
        }
        revealObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver?.unobserve(entry.target);
            }
        }), { threshold: 0.12 });
        revealItems.filter((item) => !item.classList.contains('is-visible')).forEach((item) => revealObserver.observe(item));
    };
    updateRevealBehavior();
    window.addEventListener(accessibilityEvent, updateRevealBehavior);
    if (typeof motionPreference.addEventListener === 'function') motionPreference.addEventListener('change', updateRevealBehavior);
    else motionPreference.addListener(updateRevealBehavior);

    document.querySelectorAll('.locale-form').forEach((localeForm) => {
        localeForm.addEventListener('submit', () => {
            const returnInput = localeForm.querySelector('input[name="return_to"]');
            if (returnInput) returnInput.value = `${window.location.pathname}${window.location.search}${window.location.hash}`;
        });
    });

    document.querySelectorAll('.locale-form input[name="attempt_token"]').forEach((attemptInput) => {
        const localeForm = attemptInput.form;
        localeForm?.addEventListener('submit', () => {
            localeForm.querySelectorAll('input[data-quiz-answer]').forEach((input) => input.remove());
            document.querySelectorAll('#quizForm input[type="radio"]:checked').forEach((answer) => {
                const questionId = answer.name.match(/^q\[([^\]]+)\]$/)?.[1];
                if (!questionId) return;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `quiz_answers[${questionId}]`;
                hidden.value = answer.value;
                hidden.dataset.quizAnswer = 'true';
                localeForm.appendChild(hidden);
            });
        });
    });

    document.querySelectorAll('[data-current-year]').forEach((node) => { node.textContent = new Date().getFullYear(); });
    document.querySelectorAll('[data-submit-loading]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${messages['common.processing'] || ''}`;
            button.classList.add('is-loading');
            button.setAttribute('aria-disabled', 'true');
        });
    });

    const formAlert = document.querySelector('.form-alert[role="alert"][tabindex="-1"]');
    if (formAlert) window.requestAnimationFrame(() => formAlert.focus());
})();
