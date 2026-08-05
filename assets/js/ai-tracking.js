(() => {
    'use strict';

    const video = document.getElementById('input_video');
    const canvas = document.getElementById('output_canvas');
    const context = canvas?.getContext('2d');
    const startButton = document.getElementById('start-camera');
    const stopButton = document.getElementById('stop-camera');
    const placeholder = document.getElementById('camera-placeholder');
    const statusText = document.getElementById('ai-status');
    const statusDot = document.getElementById('camera-status-dot');
    const gestureResult = document.getElementById('gesture-result');
    const feedbackMessage = document.getElementById('feedback-message');
    const confidenceLabel = document.getElementById('confidence-label');
    const confidenceFill = document.getElementById('confidence-fill');
    const targetSymbol = document.getElementById('target-symbol');
    const targetTitle = document.getElementById('target-title');
    const practiceState = document.getElementById('practice-state');

    if (!video || !canvas || !context || typeof Hands === 'undefined' || typeof fp === 'undefined') {
        if (statusText) statusText.textContent = 'Komponen AI gagal dimuatkan';
        if (feedbackMessage) feedbackMessage.textContent = 'Semak sambungan internet dan muat semula halaman';
        return;
    }

    const { Finger, FingerCurl, FingerDirection, GestureDescription } = fp;
    const fingers = [Finger.Thumb, Finger.Index, Finger.Middle, Finger.Ring, Finger.Pinky];
    const makeNumberGesture = (name, raisedFingers) => {
        const gesture = new GestureDescription(name);
        fingers.forEach((finger) => {
            if (raisedFingers.includes(finger)) {
                gesture.addCurl(finger, FingerCurl.NoCurl, 1.0);
                if (finger === Finger.Thumb) {
                    gesture.addDirection(finger, FingerDirection.HorizontalLeft, 0.7);
                    gesture.addDirection(finger, FingerDirection.HorizontalRight, 0.7);
                    gesture.addDirection(finger, FingerDirection.DiagonalUpLeft, 0.35);
                    gesture.addDirection(finger, FingerDirection.DiagonalUpRight, 0.35);
                } else {
                    gesture.addDirection(finger, FingerDirection.VerticalUp, 0.7);
                    gesture.addDirection(finger, FingerDirection.DiagonalUpLeft, 0.35);
                    gesture.addDirection(finger, FingerDirection.DiagonalUpRight, 0.35);
                }
            } else {
                gesture.addCurl(finger, FingerCurl.FullCurl, 1.0);
                gesture.addCurl(finger, FingerCurl.HalfCurl, 0.7);
            }
        });
        return gesture;
    };

    const gestureA = new GestureDescription('A');
    [Finger.Index, Finger.Middle, Finger.Ring, Finger.Pinky].forEach((finger) => {
        gestureA.addCurl(finger, FingerCurl.FullCurl, 1.0);
        gestureA.addCurl(finger, FingerCurl.HalfCurl, 0.7);
    });
    gestureA.addCurl(Finger.Thumb, FingerCurl.HalfCurl, 1.0);
    gestureA.addCurl(Finger.Thumb, FingerCurl.NoCurl, 0.5);

    const estimator = new fp.GestureEstimator([
        gestureA,
        makeNumberGesture('1', [Finger.Index]),
        makeNumberGesture('2', [Finger.Index, Finger.Middle]),
        makeNumberGesture('3', [Finger.Index, Finger.Middle, Finger.Ring]),
        makeNumberGesture('4', [Finger.Index, Finger.Middle, Finger.Ring, Finger.Pinky]),
        makeNumberGesture('5', fingers),
    ]);

    let camera = null;
    let running = false;
    let target = targetSymbol?.textContent.trim() || 'A';
    let recentGestures = [];
    let stableSince = null;
    let completedTarget = false;
    const requiredHoldMs = 1800;

    const setStatus = (message, isLive = false) => {
        statusText.textContent = message;
        statusDot.classList.toggle('live', isLive);
    };

    const resetFeedback = (message = 'Tunjukkan satu tangan dalam bingkai') => {
        recentGestures = [];
        stableSince = null;
        completedTarget = false;
        practiceState.classList.remove('teal');
        gestureResult.textContent = '—';
        feedbackMessage.textContent = message;
        confidenceLabel.textContent = 'Kestabilan 0%';
        confidenceFill.style.width = '0%';
    };

    const updateTarget = (nextTarget) => {
        target = nextTarget;
        completedTarget = false;
        stableSince = null;
        targetSymbol.textContent = target;
        targetTitle.textContent = target === 'A' ? 'Huruf A' : `Nombor ${target}`;
        practiceState.innerHTML = '<i class="bi bi-hourglass-split"></i> Menunggu isyarat';
        document.querySelectorAll('[data-target]').forEach((button) => {
            const isActive = button.dataset.target === target;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
        resetFeedback(running ? `Bentuk isyarat ${target} dan tahan` : 'Mulakan kamera untuk berlatih');
        const url = new URL(window.location.href);
        url.searchParams.set('target', target);
        window.history.replaceState({}, '', url);
    };

    const fingerAngle = (a, b, c) => {
        const ab = [a.x - b.x, a.y - b.y, a.z - b.z];
        const cb = [c.x - b.x, c.y - b.y, c.z - b.z];
        const dot = ab[0] * cb[0] + ab[1] * cb[1] + ab[2] * cb[2];
        const lengthAB = Math.hypot(...ab);
        const lengthCB = Math.hypot(...cb);
        if (!lengthAB || !lengthCB) return 0;
        return Math.acos(Math.min(1, Math.max(-1, dot / (lengthAB * lengthCB)))) * (180 / Math.PI);
    };

    const distance = (a, b) => Math.hypot(a.x - b.x, a.y - b.y, a.z - b.z);

    const extendedFingerPattern = (landmarks) => {
        const wrist = landmarks[0];
        const fingerIndexes = [
            [5, 6, 8],
            [9, 10, 12],
            [13, 14, 16],
            [17, 18, 20],
        ];
        const raised = fingerIndexes.map(([mcp, pip, tip]) => (
            fingerAngle(landmarks[mcp], landmarks[pip], landmarks[tip]) > 150
            && distance(landmarks[tip], wrist) > distance(landmarks[pip], wrist) * 1.08
        ));
        const thumbRaised = fingerAngle(landmarks[2], landmarks[3], landmarks[4]) > 145
            && distance(landmarks[4], wrist) > distance(landmarks[3], wrist) * 1.15
            && distance(landmarks[4], landmarks[5]) > distance(landmarks[3], landmarks[5]) * 1.08;
        const palmWidth = distance(landmarks[5], landmarks[17]);
        const thumbNearPalm = distance(landmarks[4], landmarks[5]) < palmWidth * 0.95;

        return { raised, thumbRaised, thumbNearPalm };
    };

    const expectedGestureFromPattern = ({ raised, thumbRaised, thumbNearPalm }) => {
        const pattern = raised.map((value) => value ? '1' : '0').join('');
        if (pattern === '0000' && !thumbRaised && thumbNearPalm) return 'A';
        if (pattern === '1000') return '1';
        if (pattern === '1100') return '2';
        if (pattern === '1110') return '3';
        if (pattern === '1111') return thumbRaised ? '5' : '4';
        return null;
    };

    const classifyGesture = (landmarks) => {
        const expectedName = expectedGestureFromPattern(extendedFingerPattern(landmarks));
        if (!expectedName) return null;

        // MediaPipe returns objects; Fingerpose 0.1.0 requires coordinate triples.
        const fingerposeLandmarks = landmarks.map(({ x, y, z }) => [x, y, z]);
        const estimate = estimator.estimate(fingerposeLandmarks, 5.5);
        const matchingGesture = estimate.gestures
            .filter((gesture) => gesture.name === expectedName)
            .sort((a, b) => b.score - a.score)[0];

        return matchingGesture && matchingGesture.score >= 5.5 ? matchingGesture : null;
    };

    const onResults = (results) => {
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        context.save();
        context.clearRect(0, 0, canvas.width, canvas.height);

        const landmarks = results.multiHandLandmarks?.[0];
        if (!landmarks) {
            context.restore();
            resetFeedback();
            return;
        }

        drawConnectors(context, landmarks, HAND_CONNECTIONS, { color: '#10b5a8', lineWidth: 4 });
        drawLandmarks(context, landmarks, { color: '#ffffff', fillColor: '#f2735b', lineWidth: 2, radius: 3 });
        context.restore();

        const match = classifyGesture(landmarks);
        if (!match) {
            resetFeedback('Bentuk belum dikenali — laraskan kedudukan tangan');
            return;
        }

        recentGestures.push(match.name);
        if (recentGestures.length > 10) recentGestures.shift();
        const occurrences = recentGestures.filter((name) => name === match.name).length;
        const stability = Math.round((occurrences / recentGestures.length) * 100);

        const hasEnoughSamples = recentGestures.length >= 6;
        const fingerposeConfidence = Math.min(100, Math.round((match.score / 10) * 100));

        gestureResult.textContent = match.name;
        confidenceLabel.textContent = hasEnoughSamples
            ? `Padanan ${fingerposeConfidence}% · stabil ${stability}%`
            : `Menstabilkan ${recentGestures.length}/6`;
        confidenceFill.style.width = `${hasEnoughSamples ? Math.min(fingerposeConfidence, stability) : (recentGestures.length / 6) * 100}%`;

        if (match.name === target && hasEnoughSamples && stability >= 70) {
            stableSince ??= performance.now();
            const heldMs = performance.now() - stableSince;
            const holdProgress = Math.min(100, Math.round((heldMs / requiredHoldMs) * 100));
            feedbackMessage.textContent = `Bagus! Kekalkan isyarat ${target}... ${holdProgress}%`;
            practiceState.innerHTML = '<i class="bi bi-stars"></i> Hampir berjaya';
            if (heldMs >= requiredHoldMs && !completedTarget) {
                completedTarget = true;
                feedbackMessage.textContent = `Hebat! Isyarat ${target} disahkan.`;
                practiceState.innerHTML = '<i class="bi bi-check-circle-fill"></i> Berjaya';
                practiceState.classList.add('teal');
            }
        } else {
            stableSince = null;
            feedbackMessage.textContent = match.name === target ? 'Stabilkan tangan anda seketika' : `Dikesan ${match.name} — sasaran anda ialah ${target}`;
            practiceState.innerHTML = '<i class="bi bi-hand-index-thumb"></i> Sedang mencuba';
        }
    };

    const hands = new Hands({ locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/${file}` });
    hands.setOptions({ maxNumHands: 1, modelComplexity: 1, minDetectionConfidence: 0.72, minTrackingConfidence: 0.65 });
    hands.onResults(onResults);

    const startCamera = async () => {
        if (running) return;
        setStatus('Meminta kebenaran kamera...');
        startButton.disabled = true;
        try {
            camera = new Camera(video, {
                onFrame: async () => { if (running) await hands.send({ image: video }); },
                width: 640,
                height: 480,
            });
            running = true;
            await camera.start();
            placeholder.classList.add('is-hidden');
            stopButton.disabled = false;
            setStatus('Kamera aktif · menunggu tangan', true);
            feedbackMessage.textContent = `Bentuk isyarat ${target} dan tahan`;
            practiceState.innerHTML = '<i class="bi bi-hand-index-thumb"></i> Sedang mencuba';
        } catch (error) {
            running = false;
            startButton.disabled = false;
            setStatus('Akses kamera tidak berjaya');
            feedbackMessage.textContent = 'Benarkan kamera dalam tetapan pelayar dan cuba semula';
            console.error('Camera error:', error);
        }
    };

    const stopCamera = () => {
        running = false;
        camera?.stop();
        video.srcObject?.getTracks().forEach((track) => track.stop());
        camera = null;
        context.clearRect(0, 0, canvas.width, canvas.height);
        placeholder.classList.remove('is-hidden');
        startButton.disabled = false;
        stopButton.disabled = true;
        setStatus('Kamera dihentikan');
        practiceState.innerHTML = '<i class="bi bi-hourglass-split"></i> Belum bermula';
        resetFeedback('Menunggu kamera');
    };

    startButton.addEventListener('click', startCamera);
    stopButton.addEventListener('click', stopCamera);
    document.querySelectorAll('[data-target]').forEach((button) => button.addEventListener('click', () => updateTarget(button.dataset.target)));
    window.addEventListener('pagehide', stopCamera);
})();
