(() => {
    'use strict';

    const form = document.getElementById('quizForm');
    const timer = document.getElementById('timer');
    const timerPill = document.getElementById('timer-pill');
    const timeInput = document.getElementById('time_taken');
    const answeredCount = document.getElementById('answered-count');

    if (!form || !timer || !timeInput) return;

    const deadlineMs = Number(form.dataset.deadlineMs);
    let remaining = Math.max(0, Math.ceil((deadlineMs - Date.now()) / 1000));
    let submitted = false;

    const renderTimer = () => {
        remaining = Math.max(0, Math.ceil((deadlineMs - Date.now()) / 1000));
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        timeInput.value = String(Math.max(0, 60 - remaining));
        timerPill?.classList.toggle('warning', remaining <= 10);
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

    form.addEventListener('change', updateAnswered);
    form.addEventListener('submit', () => {
        submitted = true;
        timeInput.value = String(Math.max(0, 60 - remaining));
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Mengira skor...';
        }
    });

    renderTimer();
    const countdown = window.setInterval(() => {
        if (submitted) {
            window.clearInterval(countdown);
            return;
        }
        renderTimer();
        if (remaining <= 0) {
            window.clearInterval(countdown);
            submitted = true;
            form.submit();
        }
    }, 1000);
})();
