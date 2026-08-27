(() => {
    'use strict';

    const CATEGORY_TARGETS = Object.freeze({
        alphabet: Object.freeze(['A', 'B', 'I', 'L', 'O', 'U', 'V', 'W', 'Y']),
        numbers: Object.freeze(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']),
    });

    // Conservative prototype thresholds. Geometry proposes a class; Fingerpose
    // must independently validate the same category-scoped class.
    const THRESHOLDS = Object.freeze({
        fingerStraightAngle: 150,
        fingertipBeyondJointRatio: 1.08,
        thumbStraightAngle: 145,
        thumbBeyondJointRatio: 1.14,
        contactPalmRatio: 0.34,
        uSpreadMaximum: 0.42,
        vSpreadMinimum: 0.52,
        fingerposeScore: 6.0,
        stableSamples: 6,
        sampleWindow: 10,
        stableRatio: 0.70,
        holdMilliseconds: 1800,
    });

    const dependencyReady = () => typeof Hands !== 'undefined' && typeof fp !== 'undefined'
        && typeof Camera !== 'undefined' && typeof drawConnectors !== 'undefined'
        && typeof drawLandmarks !== 'undefined' && typeof HAND_CONNECTIONS !== 'undefined';

    const assertCategory = (category) => {
        if (!Object.prototype.hasOwnProperty.call(CATEGORY_TARGETS, category)) {
            throw new TypeError('A valid category is required for gesture classification.');
        }
    };

    const fingerAngle = (a, b, c) => {
        const ab = [a.x - b.x, a.y - b.y, a.z - b.z];
        const cb = [c.x - b.x, c.y - b.y, c.z - b.z];
        const denominator = Math.hypot(...ab) * Math.hypot(...cb);
        if (!denominator) return 0;
        const cosine = Math.min(1, Math.max(-1, ab.reduce((sum, value, index) => sum + value * cb[index], 0) / denominator));
        return Math.acos(cosine) * (180 / Math.PI);
    };

    const distance = (a, b) => Math.hypot(a.x - b.x, a.y - b.y, a.z - b.z);

    const geometry = (landmarks) => {
        const wrist = landmarks[0];
        const joints = [[5, 6, 8], [9, 10, 12], [13, 14, 16], [17, 18, 20]];
        const raised = joints.map(([mcp, pip, tip]) => fingerAngle(landmarks[mcp], landmarks[pip], landmarks[tip]) > THRESHOLDS.fingerStraightAngle
            && distance(landmarks[tip], wrist) > distance(landmarks[pip], wrist) * THRESHOLDS.fingertipBeyondJointRatio);
        const thumbExtended = fingerAngle(landmarks[2], landmarks[3], landmarks[4]) > THRESHOLDS.thumbStraightAngle
            && distance(landmarks[4], wrist) > distance(landmarks[3], wrist) * THRESHOLDS.thumbBeyondJointRatio;
        const palmWidth = Math.max(distance(landmarks[5], landmarks[17]), 0.0001);
        const contacts = {
            index: distance(landmarks[4], landmarks[8]) / palmWidth <= THRESHOLDS.contactPalmRatio,
            middle: distance(landmarks[4], landmarks[12]) / palmWidth <= THRESHOLDS.contactPalmRatio,
            ring: distance(landmarks[4], landmarks[16]) / palmWidth <= THRESHOLDS.contactPalmRatio,
            pinky: distance(landmarks[4], landmarks[20]) / palmWidth <= THRESHOLDS.contactPalmRatio,
        };
        return {
            raised,
            pattern: raised.map((value) => value ? '1' : '0').join(''),
            thumbExtended,
            contacts,
            indexMiddleSpread: distance(landmarks[8], landmarks[12]) / palmWidth,
        };
    };

    const expectedTarget = (category, shape) => {
        assertCategory(category);
        const { pattern, thumbExtended, contacts, indexMiddleSpread } = shape;
        if (category === 'alphabet') {
            if (pattern === '0000' && contacts.index) return 'O';
            if (pattern === '0000' && !contacts.index) return 'A';
            if (pattern === '1111' && !thumbExtended) return 'B';
            if (pattern === '0001' && !thumbExtended) return 'I';
            if (pattern === '1000' && thumbExtended) return 'L';
            if (pattern === '1100' && indexMiddleSpread <= THRESHOLDS.uSpreadMaximum) return 'U';
            if (pattern === '1100' && indexMiddleSpread >= THRESHOLDS.vSpreadMinimum) return 'V';
            if (pattern === '1110') return 'W';
            if (pattern === '0001' && thumbExtended) return 'Y';
            return null;
        }
        if (pattern === '0000' && contacts.index) return '0';
        if (pattern === '1000') return '1';
        if (pattern === '1100') return '2';
        if (pattern === '1110' && contacts.pinky) return '6';
        if (pattern === '1110' && !contacts.pinky) return '3';
        if (pattern === '1111' && !thumbExtended) return '4';
        if (pattern === '1111' && thumbExtended) return '5';
        if (pattern === '1101' && contacts.ring) return '7';
        if (pattern === '1011' && contacts.middle) return '8';
        if (pattern === '0111' && contacts.index) return '9';
        return null;
    };

    const buildGesture = (category, target, raisedPattern, thumbMode = 'folded') => {
        const { Finger, FingerCurl, FingerDirection, GestureDescription } = fp;
        const gesture = new GestureDescription(`${category}:${target}`);
        const fingers = [Finger.Index, Finger.Middle, Finger.Ring, Finger.Pinky];
        fingers.forEach((finger, index) => {
            if (raisedPattern[index] === '1') {
                gesture.addCurl(finger, FingerCurl.NoCurl, 1.0);
                gesture.addDirection(finger, FingerDirection.VerticalUp, 0.65);
                gesture.addDirection(finger, FingerDirection.DiagonalUpLeft, 0.35);
                gesture.addDirection(finger, FingerDirection.DiagonalUpRight, 0.35);
            } else {
                gesture.addCurl(finger, FingerCurl.FullCurl, 1.0);
                gesture.addCurl(finger, FingerCurl.HalfCurl, 0.75);
            }
        });
        if (thumbMode === 'extended') {
            gesture.addCurl(Finger.Thumb, FingerCurl.NoCurl, 1.0);
            gesture.addDirection(Finger.Thumb, FingerDirection.HorizontalLeft, 0.6);
            gesture.addDirection(Finger.Thumb, FingerDirection.HorizontalRight, 0.6);
            gesture.addDirection(Finger.Thumb, FingerDirection.DiagonalUpLeft, 0.3);
            gesture.addDirection(Finger.Thumb, FingerDirection.DiagonalUpRight, 0.3);
        } else {
            gesture.addCurl(Finger.Thumb, FingerCurl.FullCurl, 0.8);
            gesture.addCurl(Finger.Thumb, FingerCurl.HalfCurl, 1.0);
            if (thumbMode === 'contact') gesture.addCurl(Finger.Thumb, FingerCurl.NoCurl, 0.45);
        }
        return gesture;
    };

    const gestureDefinitions = () => ({
        alphabet: [
            buildGesture('alphabet', 'A', '0000'), buildGesture('alphabet', 'B', '1111'), buildGesture('alphabet', 'I', '0001'),
            buildGesture('alphabet', 'L', '1000', 'extended'), buildGesture('alphabet', 'O', '0000', 'contact'),
            buildGesture('alphabet', 'U', '1100'), buildGesture('alphabet', 'V', '1100'), buildGesture('alphabet', 'W', '1110'),
            buildGesture('alphabet', 'Y', '0001', 'extended'),
        ],
        numbers: [
            buildGesture('numbers', '0', '0000', 'contact'), buildGesture('numbers', '1', '1000'), buildGesture('numbers', '2', '1100'),
            buildGesture('numbers', '3', '1110'), buildGesture('numbers', '4', '1111'), buildGesture('numbers', '5', '1111', 'extended'),
            buildGesture('numbers', '6', '1110', 'contact'), buildGesture('numbers', '7', '1101', 'contact'),
            buildGesture('numbers', '8', '1011', 'contact'), buildGesture('numbers', '9', '0111', 'contact'),
        ],
    });

    class GestureRecognizer {
        constructor(options = {}) {
            if (!dependencyReady()) throw new Error('Gesture recognition dependencies are unavailable.');
            if (!(options.video instanceof HTMLVideoElement) || !(options.canvas instanceof HTMLCanvasElement)) {
                throw new TypeError('A video and canvas element are required.');
            }
            assertCategory(options.category);
            if (!CATEGORY_TARGETS[options.category].includes(String(options.target))) throw new TypeError('Target is not approved for its category.');

            this.video = options.video;
            this.canvas = options.canvas;
            this.context = this.canvas.getContext('2d');
            this.eventTarget = options.eventTarget || document;
            this.onUpdate = typeof options.onUpdate === 'function' ? options.onUpdate : () => {};
            this.onCameraStatus = typeof options.onCameraStatus === 'function' ? options.onCameraStatus : () => {};
            this.onConfirmation = typeof options.onConfirmation === 'function' ? options.onConfirmation : () => {};
            this.onAnnouncement = typeof options.onAnnouncement === 'function' ? options.onAnnouncement : () => {};
            this.category = options.category;
            this.target = String(options.target);
            this.running = false;
            this.destroyed = false;
            this.camera = null;
            this.recent = [];
            this.stableSince = null;
            this.confirmed = false;
            const definitions = gestureDefinitions();
            this.estimators = {
                alphabet: new fp.GestureEstimator(definitions.alphabet),
                numbers: new fp.GestureEstimator(definitions.numbers),
            };
            this.hands = new Hands({ locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/${file}` });
            this.hands.setOptions({ maxNumHands: 1, modelComplexity: 1, minDetectionConfidence: 0.72, minTrackingConfidence: 0.65 });
            this.hands.onResults((results) => this.handleResults(results));
        }

        static isSupported() { return dependencyReady(); }
        static approvedTargets(category) { assertCategory(category); return [...CATEGORY_TARGETS[category]]; }
        static thresholds() { return { ...THRESHOLDS }; }

        classify(landmarks, category) {
            assertCategory(category);
            const target = expectedTarget(category, geometry(landmarks));
            if (!target) return null;
            const coordinates = landmarks.map(({ x, y, z }) => [x, y, z]);
            const estimate = this.estimators[category].estimate(coordinates, THRESHOLDS.fingerposeScore);
            const classifierId = `${category}:${target}`;
            const match = estimate.gestures.filter((gesture) => gesture.name === classifierId).sort((a, b) => b.score - a.score)[0];
            if (!match || match.score < THRESHOLDS.fingerposeScore) return null;
            return { category, target, classifierId, score: match.score };
        }

        resetSequence() {
            this.recent = [];
            this.stableSince = null;
            this.confirmed = false;
        }

        setTarget(category, target) {
            assertCategory(category);
            target = String(target);
            if (!CATEGORY_TARGETS[category].includes(target)) throw new TypeError('Target is not approved for its category.');
            const changed = category !== this.category || target !== this.target;
            this.category = category;
            this.target = target;
            if (changed) this.resetSequence();
            return changed;
        }

        emitConfirmation(match) {
            if (this.confirmed) return;
            this.confirmed = true;
            const detail = Object.freeze({ category: this.category, target: this.target, classifierId: match.classifierId });
            this.onConfirmation(detail);
            this.onAnnouncement({ type: 'confirmation', ...detail });
            this.eventTarget.dispatchEvent(new CustomEvent('bim:gesture-confirmed', { detail }));
        }

        handleResults(results) {
            if (!this.running || this.destroyed) return;
            this.canvas.width = this.video.videoWidth || 640;
            this.canvas.height = this.video.videoHeight || 480;
            this.context.save();
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            const landmarks = results.multiHandLandmarks?.[0];
            if (!landmarks) {
                this.context.restore();
                if (!this.confirmed) { this.recent = []; this.stableSince = null; this.onUpdate({ state: 'no-hand', progress: 0 }); }
                return;
            }
            drawConnectors(this.context, landmarks, HAND_CONNECTIONS, { color: '#10b5a8', lineWidth: 4 });
            drawLandmarks(this.context, landmarks, { color: '#ffffff', fillColor: '#f2735b', lineWidth: 2, radius: 3 });
            this.context.restore();

            const match = this.classify(landmarks, this.category);
            if (this.confirmed) {
                this.onUpdate({ state: 'confirmed', name: this.target, progress: 100, stability: 100, score: match?.score || 0 });
                return;
            }
            if (!match) {
                this.recent = [];
                this.stableSince = null;
                this.onUpdate({ state: 'unrecognized', progress: 0 });
                return;
            }

            this.recent.push(match.target);
            if (this.recent.length > THRESHOLDS.sampleWindow) this.recent.shift();
            const occurrences = this.recent.filter((name) => name === match.target).length;
            const stability = occurrences / this.recent.length;
            const enoughSamples = this.recent.length >= THRESHOLDS.stableSamples;
            const sampleProgress = (this.recent.length / THRESHOLDS.stableSamples) * 100;
            const confidence = Math.min(100, Math.round((match.score / 10) * 100));

            if (match.target !== this.target || !enoughSamples || stability < THRESHOLDS.stableRatio) {
                this.stableSince = null;
                this.onUpdate({
                    state: match.target === this.target ? 'stabilizing' : 'mismatch', name: match.target,
                    samples: this.recent.length, stability: Math.round(stability * 100), score: match.score,
                    confidence, progress: enoughSamples ? Math.min(confidence, stability * 100) : sampleProgress,
                });
                return;
            }

            this.stableSince ??= performance.now();
            const heldMilliseconds = performance.now() - this.stableSince;
            const holdProgress = Math.min(100, Math.round((heldMilliseconds / THRESHOLDS.holdMilliseconds) * 100));
            if (heldMilliseconds >= THRESHOLDS.holdMilliseconds) {
                this.emitConfirmation(match);
                this.onUpdate({ state: 'confirmed', name: this.target, progress: 100, stability: Math.round(stability * 100), score: match.score, confidence });
                return;
            }
            this.onUpdate({ state: 'holding', name: match.target, progress: holdProgress, holdProgress, stability: Math.round(stability * 100), score: match.score, confidence });
        }

        async start() {
            if (this.destroyed) throw new Error('Recognizer has been destroyed.');
            if (this.running) return;
            this.onCameraStatus({ state: 'requesting' });
            this.camera = new Camera(this.video, {
                onFrame: async () => { if (this.running) await this.hands.send({ image: this.video }); },
                width: 640,
                height: 480,
            });
            this.running = true;
            try {
                await this.camera.start();
                this.onCameraStatus({ state: 'active' });
            } catch (error) {
                this.running = false;
                this.camera?.stop();
                this.video.srcObject?.getTracks().forEach((track) => track.stop());
                this.camera = null;
                this.onCameraStatus({ state: 'failed', error });
                throw error;
            }
        }

        stop(options = {}) {
            this.running = false;
            this.camera?.stop();
            this.video.srcObject?.getTracks().forEach((track) => track.stop());
            this.camera = null;
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.resetSequence();
            if (options.notify !== false) this.onCameraStatus({ state: 'stopped' });
        }

        async destroy() {
            if (this.destroyed) return;
            this.stop({ notify: false });
            this.destroyed = true;
            if (typeof this.hands.close === 'function') await this.hands.close();
        }
    }

    window.BIMGestureRecognizer = Object.freeze({
        GestureRecognizer,
        approvedTargets: GestureRecognizer.approvedTargets,
        isSupported: GestureRecognizer.isSupported,
        thresholds: GestureRecognizer.thresholds,
    });
})();
