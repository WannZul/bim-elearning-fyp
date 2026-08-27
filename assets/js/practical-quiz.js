(() => {
    'use strict';

    const config = window.BIM_PRACTICAL || {};
    const messages = window.BIM_I18N || {};
    const formatMessage = (key, params = {}) => Object.entries(params).reduce(
        (message, [name, value]) => message.split(`:${name}`).join(String(value)), messages[key] || '',
    );
    const byId = (id) => document.getElementById(id);
    const nodes = {
        video: byId('practical_video'), canvas: byId('practical_canvas'), start: byId('start-practical'),
        stop: byId('stop-practical-camera'), placeholder: byId('practical-placeholder'), placeholderTarget: byId('placeholder-target'),
        cameraStatus: byId('practical-camera-status'), statusDot: byId('camera-status-dot'), detected: byId('practical-detected'),
        feedback: byId('practical-feedback'), stability: byId('practical-stability'), progressBar: byId('practical-confidence'),
        progressFill: byId('practical-confidence-fill'), targetSymbol: byId('practical-target-symbol'), targetTitle: byId('practical-target-title'),
        targetList: byId('practical-target-list'), skip: byId('skip-practical-target'), progress: byId('practical-progress'),
        confirmedCount: byId('practical-confirmed'), timer: byId('practical-timer'), timerPill: byId('practical-timer-pill'),
        announcer: byId('practical-announcer'), form: byId('practical-form'), confirmations: byId('practical-confirmations'),
    };

    const configurationReady = () => {
        try {
            if (config.targets?.length !== 5 || new Set(config.targets.map((target) => String(target.symbol))).size !== 5) return false;
            const approved = window.BIMGestureRecognizer.approvedTargets(config.category);
            return config.targets.every((target) => approved.includes(String(target.symbol)))
                && typeof config.startUrl === 'string' && config.startUrl !== '';
        } catch (error) {
            return false;
        }
    };
    const ready = Object.values(nodes).every(Boolean) && window.BIMGestureRecognizer?.isSupported() && configurationReady();
    if (!ready) {
        const message = messages['practical.js.load_failed'] || '';
        if (nodes.feedback) nodes.feedback.textContent = message;
        if (nodes.cameraStatus) nodes.cameraStatus.textContent = message;
        if (nodes.start) nodes.start.disabled = true;
        if (nodes.skip) nodes.skip.disabled = true;
        if (nodes.announcer) nodes.announcer.textContent = message;
        return;
    }

    let currentIndex = 0;
    const confirmedTargets = [];
    let submitting = false;
    let transitionPending = false;
    let challengeStarted = false;
    let deadline = null;
    let timerInterval = null;
    let lastAnnouncement = '';
    const current = () => config.targets[currentIndex];
    const announce = (message) => {
        if (!message || message === lastAnnouncement) return;
        lastAnnouncement = message;
        nodes.announcer.textContent = '';
        window.requestAnimationFrame(() => { nodes.announcer.textContent = message; });
    };
    const setProgressBar = (value) => {
        const normalized = Math.min(100, Math.max(0, Math.round(value || 0)));
        nodes.progressFill.style.width = `${normalized}%`;
        nodes.progressBar.setAttribute('aria-valuenow', String(normalized));
    };
    const markTarget = (index, state) => {
        const item = nodes.targetList.querySelector(`[data-target-index="${index}"]`);
        if (!item) return;
        item.classList.remove('is-confirmed', 'is-skipped');
        if (state) item.classList.add(`is-${state}`);
        const status = item.querySelector('[data-target-status]');
        if (status) status.textContent = messages[`practical.status_${state || 'pending'}`] || '';
        item.removeAttribute('aria-current');
    };
    const renderCurrent = () => {
        const target = current();
        nodes.targetSymbol.textContent = target.symbol;
        nodes.targetTitle.textContent = target.title;
        nodes.placeholderTarget.textContent = target.symbol;
        nodes.progress.textContent = formatMessage('practical.progress', { current: currentIndex + 1, total: config.targets.length });
        nodes.confirmedCount.textContent = formatMessage('practical.confirmed_count', { count: confirmedTargets.length, total: config.targets.length });
        nodes.targetList.querySelectorAll('li').forEach((item, index) => {
            if (index === currentIndex) item.setAttribute('aria-current', 'step'); else item.removeAttribute('aria-current');
        });
        nodes.detected.textContent = '—';
        nodes.stability.textContent = messages['ai.js.stability_zero'] || '';
        setProgressBar(0);
        recognizer.setTarget(config.category, target.symbol);
    };
    const submitResult = (message) => {
        if (submitting || !challengeStarted) return;
        submitting = true;
        window.clearInterval(timerInterval);
        transitionPending = false;
        nodes.skip.disabled = true;
        nodes.start.disabled = true;
        nodes.stop.disabled = true;
        nodes.feedback.textContent = messages['practical.submitting'] || '';
        if (message) announce(message);
        nodes.confirmations.value = JSON.stringify(confirmedTargets);
        recognizer.stop({ notify: false });
        nodes.form.requestSubmit();
    };
    const advance = () => {
        transitionPending = false;
        if (submitting) return;
        if (currentIndex >= config.targets.length - 1) {
            submitResult(messages['practical.submitting']);
            return;
        }
        currentIndex += 1;
        renderCurrent();
        nodes.feedback.textContent = messages['practical.waiting'] || '';
        const target = current();
        announce(formatMessage('practical.next_target', {
            current: currentIndex + 1,
            total: config.targets.length,
            title: target.title,
            target: target.symbol,
        }));
    };
    const updateTimer = () => {
        if (deadline === null) return;
        const milliseconds = Math.max(0, deadline - Date.now());
        const seconds = Math.ceil(milliseconds / 1000);
        nodes.timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        nodes.timerPill.classList.toggle('warning', seconds <= 15);
        if (milliseconds <= 0) submitResult(messages['practical.timeout']);
    };
    const startTimer = (remainingMilliseconds) => {
        deadline = Date.now() + Math.max(0, Number(remainingMilliseconds) || 0);
        window.clearInterval(timerInterval);
        timerInterval = window.setInterval(updateTimer, 250);
        updateTimer();
    };
    const activateAttempt = async () => {
        const body = new FormData();
        body.set('csrf_token', nodes.form.elements.csrf_token.value);
        body.set('attempt_token', nodes.form.elements.attempt_token.value);
        const response = await fetch(config.startUrl, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json().catch(() => null);
        if (!response.ok || payload?.ok !== true || !(Number(payload.remainingMilliseconds) > 0)) {
            throw new Error('The practical attempt could not be activated.');
        }
        return Number(payload.remainingMilliseconds);
    };

    const recognizer = new window.BIMGestureRecognizer.GestureRecognizer({
        video: nodes.video,
        canvas: nodes.canvas,
        category: config.category,
        target: current().symbol,
        onCameraStatus: ({ state }) => {
            const text = {
                requesting: messages['ai.js.requesting'], active: messages['ai.js.active'], failed: messages['ai.js.access_failed'], stopped: messages['ai.js.stopped'],
            }[state] || '';
            if (text) nodes.cameraStatus.textContent = text;
            nodes.statusDot.classList.toggle('live', state === 'active');
            if (state === 'active') {
                nodes.placeholder.classList.add('is-hidden');
                nodes.start.disabled = true;
                nodes.stop.disabled = false;
                nodes.skip.disabled = !challengeStarted;
                nodes.feedback.textContent = challengeStarted ? messages['ai.js.show_hand'] : messages['practical.activating'];
                announce(text);
            } else if (state === 'failed' || state === 'stopped') {
                nodes.placeholder.classList.remove('is-hidden');
                nodes.start.disabled = false;
                nodes.stop.disabled = true;
                nodes.skip.disabled = true;
                nodes.feedback.textContent = state === 'failed' ? messages['ai.js.allow_camera'] : messages['practical.waiting'];
                announce(text);
            }
        },
        onConfirmation: ({ target }) => {
            if (!challengeStarted || submitting || transitionPending || target !== current().symbol || confirmedTargets.includes(target)) return;
            transitionPending = true;
            confirmedTargets.push(target);
            markTarget(currentIndex, 'confirmed');
            nodes.confirmedCount.textContent = formatMessage('practical.confirmed_count', { count: confirmedTargets.length, total: config.targets.length });
            const message = formatMessage('practical.confirmed', { target });
            nodes.feedback.textContent = message;
            announce(message);
            window.setTimeout(advance, 650);
        },
        onUpdate: (update) => {
            if (!challengeStarted || submitting || transitionPending) return;
            if (update.state === 'confirmed') return;
            if (update.state === 'no-hand' || update.state === 'unrecognized') {
                nodes.detected.textContent = '—';
                nodes.feedback.textContent = update.state === 'no-hand' ? messages['ai.js.show_hand'] : messages['ai.js.unrecognized'];
                nodes.stability.textContent = messages['ai.js.stability_zero'] || '';
                setProgressBar(0);
                return;
            }
            nodes.detected.textContent = update.name || '—';
            nodes.stability.textContent = update.samples < 6
                ? formatMessage('ai.js.stabilizing', { count: update.samples || 0 })
                : formatMessage('ai.js.match', { confidence: update.confidence || 0, stability: update.stability || 0 });
            setProgressBar(update.progress);
            nodes.feedback.textContent = update.state === 'holding'
                ? formatMessage('ai.js.keep', { target: current().symbol, progress: update.holdProgress })
                : update.state === 'mismatch'
                    ? formatMessage('ai.js.mismatch', { detected: update.name, target: current().symbol })
                    : messages['ai.js.show_hand'];
        },
    });

    nodes.start.addEventListener('click', async () => {
        nodes.start.disabled = true;
        let cameraStarted = false;
        try {
            await recognizer.start();
            cameraStarted = true;
            if (!challengeStarted) {
                nodes.feedback.textContent = messages['practical.activating'] || '';
                const remainingMilliseconds = await activateAttempt();
                challengeStarted = true;
                startTimer(remainingMilliseconds);
            }
            nodes.skip.disabled = false;
            nodes.feedback.textContent = messages['ai.js.show_hand'] || '';
            nodes.skip.focus();
        } catch (error) {
            console.error('Practical start error:', error);
            recognizer.stop({ notify: false });
            nodes.placeholder.classList.remove('is-hidden');
            nodes.statusDot.classList.remove('live');
            nodes.start.disabled = false;
            nodes.stop.disabled = true;
            nodes.skip.disabled = true;
            const message = cameraStarted && !challengeStarted
                ? messages['practical.js.session_start_failed']
                : messages['practical.js.start_failed'];
            nodes.feedback.textContent = message || '';
            announce(message);
            nodes.start.focus();
        }
    });
    nodes.stop.addEventListener('click', () => { recognizer.stop(); nodes.start.focus(); });
    nodes.skip.addEventListener('click', () => {
        if (!challengeStarted || !recognizer.running || submitting || transitionPending) return;
        transitionPending = true;
        const target = current().symbol;
        markTarget(currentIndex, 'skipped');
        const message = formatMessage('practical.skipped', { target });
        nodes.feedback.textContent = message;
        announce(message);
        window.setTimeout(advance, 350);
    });
    nodes.form.addEventListener('submit', (event) => {
        if (!challengeStarted) {
            event.preventDefault();
            return;
        }
        nodes.confirmations.value = JSON.stringify(confirmedTargets);
    });
    window.addEventListener('pagehide', (event) => {
        window.clearInterval(timerInterval);
        if (event.persisted) recognizer.stop(); else void recognizer.destroy();
    });
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted || !challengeStarted || submitting || deadline === null) return;
        startTimer(Math.max(0, deadline - Date.now()));
    });
})();
