(() => {
    'use strict';

    const messages = window.BIM_I18N || {};
    const config = window.BIM_SIGN_PRACTICE || {};
    const formatMessage = (key, params = {}) => Object.entries(params).reduce(
        (message, [name, value]) => message.split(`:${name}`).join(String(value)), messages[key] || '',
    );
    const byId = (id) => document.getElementById(id);
    const nodes = {
        video: byId('input_video'), canvas: byId('output_canvas'), start: byId('start-camera'), stop: byId('stop-camera'),
        placeholder: byId('camera-placeholder'), status: byId('ai-status'), statusDot: byId('camera-status-dot'),
        result: byId('gesture-result'), feedback: byId('feedback-message'), confidenceLabel: byId('confidence-label'),
        confidenceProgress: byId('confidence-progress'), confidenceFill: byId('confidence-fill'), targetSymbol: byId('target-symbol'),
        targetTitle: byId('target-title'), targetHeading: byId('target-heading'), targetSelector: byId('target-selector'),
        categoryCount: byId('category-count'), practiceState: byId('practice-state'), cameraAnnouncer: byId('camera-announcer'),
        detectionAnnouncer: byId('detection-announcer'),
    };
    const manifestMatchesRecognizer = () => {
        try {
            return ['alphabet', 'numbers'].every((category) => {
                const pageTargets = (config.manifest?.[category] || []).map((sign) => String(sign.symbol));
                const recognizerTargets = window.BIMGestureRecognizer.approvedTargets(category);
                return pageTargets.length === recognizerTargets.length
                    && pageTargets.every((target, index) => target === recognizerTargets[index]);
            });
        } catch (error) {
            return false;
        }
    };
    const ready = Object.values(nodes).every(Boolean) && window.BIMGestureRecognizer?.isSupported()
        && manifestMatchesRecognizer();
    if (!ready) {
        if (nodes.status) nodes.status.textContent = messages['ai.js.load_failed'] || '';
        if (nodes.feedback) nodes.feedback.textContent = messages['ai.js.reload'] || '';
        if (nodes.cameraAnnouncer) nodes.cameraAnnouncer.textContent = messages['ai.js.load_failed'] || '';
        return;
    }

    let category = config.category;
    let target = String(config.target);
    let lastCameraAnnouncement = '';
    let lastDetectionAnnouncement = '';
    const announce = (node, message, kind) => {
        if (!message || (kind === 'camera' ? lastCameraAnnouncement : lastDetectionAnnouncement) === message) return;
        if (kind === 'camera') lastCameraAnnouncement = message; else lastDetectionAnnouncement = message;
        node.textContent = '';
        window.requestAnimationFrame(() => { node.textContent = message; });
    };
    const setProgress = (value) => {
        const normalized = Math.min(100, Math.max(0, Math.round(value || 0)));
        nodes.confidenceFill.style.width = `${normalized}%`;
        nodes.confidenceProgress.setAttribute('aria-valuenow', String(normalized));
    };
    const setPracticeState = (icon, text, success = false) => {
        nodes.practiceState.replaceChildren();
        const iconNode = document.createElement('i');
        iconNode.className = `bi ${icon}`;
        iconNode.setAttribute('aria-hidden', 'true');
        nodes.practiceState.append(iconNode, document.createTextNode(` ${text}`));
        nodes.practiceState.classList.toggle('teal', success);
    };
    const signConfig = (nextCategory, nextTarget) => config.manifest[nextCategory].find((sign) => sign.symbol === String(nextTarget));

    const resetFeedback = (message) => {
        nodes.result.textContent = '—';
        nodes.feedback.textContent = message;
        nodes.confidenceLabel.textContent = messages['ai.js.stability_zero'] || '';
        setProgress(0);
        setPracticeState('bi-hourglass-split', messages['ai.js.waiting_sign'] || '');
    };

    const updateUrl = () => {
        const url = new URL(window.location.href);
        url.searchParams.set('category', category);
        url.searchParams.set('target', target);
        window.history.replaceState({}, '', url);
    };

    const renderTargets = () => {
        nodes.targetSelector.replaceChildren();
        config.manifest[category].forEach((sign) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `target-button${sign.symbol === target ? ' active' : ''}`;
            button.dataset.target = sign.symbol;
            button.setAttribute('aria-pressed', String(sign.symbol === target));
            button.textContent = sign.symbol;
            nodes.targetSelector.append(button);
        });
    };

    const renderTarget = () => {
        const sign = signConfig(category, target);
        nodes.targetSymbol.textContent = target;
        nodes.targetTitle.textContent = sign?.title || target;
        nodes.targetHeading.textContent = formatMessage('ai.target_heading', { category: config.categoryLabels[category] });
        nodes.categoryCount.textContent = config.categoryCounts[category];
        document.querySelectorAll('[data-category]').forEach((button) => {
            const active = button.dataset.category === category;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        renderTargets();
    };

    const recognizer = new window.BIMGestureRecognizer.GestureRecognizer({
        video: nodes.video,
        canvas: nodes.canvas,
        category,
        target,
        onCameraStatus: ({ state }) => {
            const statusMessages = {
                requesting: messages['ai.js.requesting'], active: messages['ai.js.active'],
                failed: messages['ai.js.access_failed'], stopped: messages['ai.js.stopped'],
            };
            const text = statusMessages[state] || '';
            if (text) {
                nodes.status.textContent = text;
                announce(nodes.cameraAnnouncer, text, 'camera');
            }
            nodes.statusDot.classList.toggle('live', state === 'active');
            if (state === 'active') {
                nodes.placeholder.classList.add('is-hidden');
                nodes.start.disabled = true;
                nodes.stop.disabled = false;
                nodes.feedback.textContent = formatMessage('ai.js.form_hold', { target });
                setPracticeState('bi-hand-index-thumb', messages['ai.js.trying'] || '');
                nodes.stop.focus();
            } else if (state === 'stopped' || state === 'failed') {
                nodes.placeholder.classList.remove('is-hidden');
                nodes.start.disabled = false;
                nodes.stop.disabled = true;
                resetFeedback(state === 'failed' ? messages['ai.js.allow_camera'] : messages['ai.js.start_to_practice']);
            }
        },
        onConfirmation: () => {
            const message = formatMessage('ai.js.confirmed', { target });
            announce(nodes.detectionAnnouncer, message, 'detection');
        },
        onUpdate: (update) => {
            if (update.state === 'confirmed') {
                nodes.result.textContent = target;
                nodes.feedback.textContent = formatMessage('ai.js.confirmed', { target });
                nodes.confidenceLabel.textContent = formatMessage('ai.js.match', { confidence: update.confidence || 100, stability: update.stability || 100 });
                setProgress(100);
                setPracticeState('bi-check-circle-fill', messages['ai.js.success'] || '', true);
                return;
            }
            if (update.state === 'no-hand' || update.state === 'unrecognized') {
                resetFeedback(update.state === 'no-hand' ? messages['ai.js.show_hand'] : messages['ai.js.unrecognized']);
                return;
            }
            nodes.result.textContent = update.name || '—';
            nodes.confidenceLabel.textContent = update.samples < 6
                ? formatMessage('ai.js.stabilizing', { count: update.samples || 0 })
                : formatMessage('ai.js.match', { confidence: update.confidence || 0, stability: update.stability || 0 });
            setProgress(update.progress);
            if (update.state === 'holding') {
                nodes.feedback.textContent = formatMessage('ai.js.keep', { target, progress: update.holdProgress });
                setPracticeState('bi-stars', messages['ai.js.almost'] || '');
            } else if (update.state === 'mismatch') {
                nodes.feedback.textContent = formatMessage('ai.js.mismatch', { detected: update.name, target });
                setPracticeState('bi-hand-index-thumb', messages['ai.js.trying'] || '');
            } else {
                nodes.feedback.textContent = messages['ai.js.steady'] || '';
                setPracticeState('bi-hand-index-thumb', messages['ai.js.trying'] || '');
            }
        },
    });

    const changeTarget = (nextTarget, nextCategory = category) => {
        nextTarget = String(nextTarget);
        if (!signConfig(nextCategory, nextTarget)) return;
        const categoryChanged = nextCategory !== category;
        const targetChanged = nextTarget !== target;
        if (!categoryChanged && !targetChanged) return;
        category = nextCategory;
        target = nextTarget;
        recognizer.setTarget(category, target);
        renderTarget();
        resetFeedback(recognizer.running ? formatMessage('ai.js.form_hold', { target }) : messages['ai.js.start_to_practice']);
        updateUrl();
        const announcement = categoryChanged
            ? formatMessage('ai.js.category_changed', { category: config.categoryLabels[category] })
            : formatMessage('ai.js.target_changed', { target });
        announce(nodes.detectionAnnouncer, announcement, 'detection');
    };

    nodes.start.addEventListener('click', async () => {
        nodes.start.disabled = true;
        try { await recognizer.start(); } catch (error) { console.error('Camera error:', error); nodes.start.focus(); }
    });
    nodes.stop.addEventListener('click', () => { recognizer.stop(); nodes.start.focus(); });
    nodes.targetSelector.addEventListener('click', (event) => {
        const button = event.target.closest('[data-target]');
        if (button) {
            changeTarget(button.dataset.target);
            nodes.targetSelector.querySelector(`[data-target="${CSS.escape(button.dataset.target)}"]`)?.focus();
        }
    });
    document.querySelectorAll('[data-category]').forEach((button) => button.addEventListener('click', () => {
        const nextCategory = button.dataset.category;
        changeTarget(config.manifest[nextCategory][0].symbol, nextCategory);
    }));
    window.addEventListener('pagehide', (event) => {
        if (event.persisted) recognizer.stop(); else void recognizer.destroy();
    });
})();
