// Ported unchanged from public/legacy/alfabet.html (same localStorage key, so
// existing players keep their unlocked batches - same origin, same key).
const STORE_KEY = 'abc_alfabet_progress_v2';

export const MODES = ['flash', 'missing', 'listen'];

export function emptyProgress(count) {
    return Array.from({ length: count }, () => ({ flash: false, missing: false, listen: false }));
}

export function loadProgress(count) {
    try {
        const raw = localStorage.getItem(STORE_KEY);
        if (!raw) return emptyProgress(count);
        const arr = JSON.parse(raw);
        if (!Array.isArray(arr) || arr.length !== count) return emptyProgress(count);
        return arr.map((p) => ({ flash: !!p?.flash, missing: !!p?.missing, listen: !!p?.listen }));
    } catch {
        return emptyProgress(count);
    }
}

export function saveProgress(arr) {
    try {
        localStorage.setItem(STORE_KEY, JSON.stringify(arr));
    } catch {
        //
    }
}

export function modesDone(progress, i) {
    const p = progress[i];
    return MODES.filter((m) => p[m]).length;
}

export function batchDone(progress, i) {
    return modesDone(progress, i) === MODES.length;
}

export function isUnlocked(progress, i) {
    return i === 0 || batchDone(progress, i - 1);
}
