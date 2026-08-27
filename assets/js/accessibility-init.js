(() => {
    'use strict';

    const key = 'bim_accessibility_v1';
    const defaults = Object.freeze({
        textSize: 'default',
        highContrast: false,
        reduceMotion: false,
    });
    const allowedTextSizes = new Set(['default', 'large', 'extra-large']);

    const normalize = (value = {}) => {
        const candidate = value && typeof value === 'object' ? value : {};
        return {
            textSize: allowedTextSizes.has(candidate.textSize) ? candidate.textSize : defaults.textSize,
            highContrast: candidate.highContrast === true,
            reduceMotion: candidate.reduceMotion === true,
        };
    };

    const load = () => {
        try {
            const stored = window.localStorage.getItem(key);
            return stored ? normalize(JSON.parse(stored)) : { ...defaults };
        } catch (error) {
            console.warn('Unable to load accessibility preferences.', error);
            return { ...defaults };
        }
    };

    const apply = (preferences) => {
        try {
            const normalized = normalize(preferences);
            const root = document.documentElement;
            root.dataset.textSize = normalized.textSize;
            if (normalized.highContrast) root.dataset.contrast = 'high';
            else delete root.dataset.contrast;
            if (normalized.reduceMotion) root.dataset.reduceMotion = 'true';
            else delete root.dataset.reduceMotion;
            return normalized;
        } catch (error) {
            console.warn('Unable to apply accessibility preferences.', error);
            return { ...defaults };
        }
    };

    const save = (preferences) => {
        try {
            const normalized = normalize(preferences);
            window.localStorage.setItem(key, JSON.stringify(normalized));
            return { preferences: normalized, persisted: true };
        } catch (error) {
            console.warn('Unable to save accessibility preferences.', error);
            return { preferences: normalize(preferences), persisted: false };
        }
    };

    window.BIMAccessibility = Object.freeze({
        version: 1,
        key,
        defaults,
        load,
        save,
        apply,
    });

    apply(load());
})();
