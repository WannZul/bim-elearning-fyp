(() => {
    'use strict';

    const form = document.getElementById('quizForm');
    const timer = document.getElementById('timer');
    const timerPill = document.getElementById('timer-pill');
    const answeredCount = document.getElementById('answered-count');

    if (!form || !timer) return;

    const initialRemainingMs = Math.max(0, Number(form.dataset.remainingMs || 60000));
    const deadline = performance.now() + initialRemainingMs;
    let remainingSeconds = Math.ceil(initialRemainingMs / 1000);
    let submitted = false;

    const renderTimer = () => {
        const remainingMs = Math.max(0, deadline - performance.now());
        remainingSeconds = Math.ceil(remainingMs / 1000);
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        timerPill?.classList.toggle('warning', remainingSeconds <= 10);
    };

    const updateAnswered = () => {
        let count = 0;
        document.querySelectorAll('[data-question-card]').forEach((card) => {
            const hasAnswer = Boolean(card.querySelector('input[type="radio"]:checked'));
            card.classList.toggle('is-answered', hasAnswer);
            if (hasAnswer) count += 1;
        });
        if (answeredCount) answeredCount.textContent = String(count);
    };

    const lockSubmitButton = () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyemak jawapan...';
    };

    form.addEventListener('change', updateAnswered);
    form.addEventListener('submit', () => {
        submitted = true;
        lockSubmitButton();
    });

    renderTimer();
    const countdown = window.setInterval(() => {
        if (submitted) {
            window.clearInterval(countdown);
            return;
        }

        renderTimer();
        if (remainingSeconds <= 1) {
            window.clearInterval(countdown);
            submitted = true;
            lockSubmitButton();
            form.submit();
        }
    }, 250);
})();
