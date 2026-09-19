import { reactive, ref } from 'vue';

// Ported unchanged from the legacy public/legacy/tabliczka.html (function names,
// scoring rule and localStorage key kept identical so old high scores/mistakes
// carry over automatically - same origin, same key).
const STORE_KEY = 'tabliczka.v1';

function loadMem() {
    try {
        const raw = localStorage.getItem(STORE_KEY);
        if (raw) return JSON.parse(raw);
    } catch {
        //
    }
    return { best: 0, wrong: {} };
}

function saveMem(mem) {
    try {
        localStorage.setItem(STORE_KEY, JSON.stringify(mem));
    } catch {
        //
    }
}

export function txt(c) {
    return c.x + (c.op === 'mul' ? ' × ' : ' : ') + c.y;
}

export function val(c) {
    return c.op === 'mul' ? c.x * c.y : c.x / c.y;
}

export function key(c) {
    return `${c.op}|${c.x}|${c.y}`;
}

export function fromKey(k) {
    const [op, x, y] = k.split('|');
    return { op, x: Number(x), y: Number(y) };
}

export function useMultiplicationGame(onAttempt) {
    const mem = reactive(loadMem());

    const screen = ref('setup'); // 'setup' | 'game' | 'result'

    const S = reactive({
        tabs: [2, 3],
        mode: 'mix',
        max: 10,
        count: 20,
        limit: 10,
        i: 0,
        score: 0,
        streak: 0,
        queue: [],
        cur: null,
        times: [],
        wrong: {},
        hardOnly: null,
        recent: [],
        last: null,
        recentMax: 0,
    });

    const answer = ref('');
    const feedback = ref({ text: '', cls: '' });
    const err = ref('');
    const tbarPct = ref(100);

    let t0 = 0;
    let timer = null;

    function build() {
        const t = S.tabs[Math.floor(Math.random() * S.tabs.length)];
        const o = 1 + Math.floor(Math.random() * S.max);
        const op = S.mode === 'mix' ? (Math.random() < 0.5 ? 'mul' : 'div') : S.mode;
        if (op === 'mul') {
            return Math.random() < 0.5 ? { op: 'mul', x: t, y: o } : { op: 'mul', x: o, y: t };
        }
        return { op: 'div', x: t * o, y: t };
    }

    // Losuj nowe pytanie unikajac ostatnio zadanych (S.recent). Przy malej liczbie
    // kombinacji nie da sie uniknac powtorzen - wtedy oddajemy ostatniego kandydata.
    function make() {
        let best = null;
        for (let i = 0; i < 16; i++) {
            const c = build();
            best = c;
            if (S.recent.indexOf(key(c)) === -1) return c;
        }
        return best;
    }

    function pick() {
        if (S.hardOnly && S.hardOnly.length) {
            let h;
            for (let i = 0; i < 8; i++) {
                h = S.hardOnly[Math.floor(Math.random() * S.hardOnly.length)];
                if (key(h) !== S.last) break;
            }
            return h;
        }
        if (S.queue.length && Math.random() < 0.45) {
            let idx = Math.floor(Math.random() * S.queue.length);
            if (S.queue.length > 1 && key(S.queue[idx]) === S.last) idx = (idx + 1) % S.queue.length;
            return S.queue.splice(idx, 1)[0];
        }
        return make();
    }

    function stopTimer() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    function next() {
        stopTimer();
        if (S.i >= S.count) {
            finish();
            return;
        }
        S.cur = pick();
        S.i++;
        const k = key(S.cur);
        S.last = k;
        S.recent.push(k);
        while (S.recent.length > S.recentMax) S.recent.shift();
        answer.value = '';
        err.value = '';
        feedback.value = { text: '', cls: '' };
        t0 = Date.now();
        if (S.limit > 0) {
            tbarPct.value = 100;
            let left = S.limit * 1000;
            timer = setInterval(() => {
                left -= 100;
                tbarPct.value = Math.max(0, left / (S.limit * 1000)) * 100;
                if (left <= 0) {
                    stopTimer();
                    miss(true);
                }
            }, 100);
        } else {
            tbarPct.value = 100;
        }
    }

    function miss(timeUp) {
        const k = key(S.cur);
        S.wrong[k] = (S.wrong[k] || 0) + 1;
        mem.wrong[k] = (mem.wrong[k] || 0) + 1;
        saveMem(mem);
        S.queue.push(S.cur);
        S.queue.push(S.cur);
        S.streak = 0;
        feedback.value = { text: `${timeUp ? 'Czas minął. ' : 'Nie. '}Poprawnie: ${val(S.cur)}`, cls: 'no' };
        onAttempt?.(k, false);
        setTimeout(next, 1700);
    }

    function check() {
        if (!S.cur) return;
        const v = answer.value.trim();
        if (v === '' || Number.isNaN(Number(v))) {
            err.value = 'Wpisz odpowiedź';
            return;
        }
        err.value = '';
        stopTimer();
        const dt = (Date.now() - t0) / 1000;
        S.times.push(dt);
        if (Number(v) === val(S.cur)) {
            const bonus = S.limit > 0 ? Math.max(0, Math.round((S.limit - dt) * 2)) : 0;
            S.streak++;
            const pts = 10 + bonus + (S.streak >= 5 ? 10 : 0);
            S.score += pts;
            feedback.value = { text: `Dobrze, +${pts}`, cls: 'ok' };
            onAttempt?.(key(S.cur), true);
            setTimeout(next, 600);
        } else {
            miss(false);
        }
    }

    const result = reactive({ score: 0, accuracy: 0, avgTime: 0, weak: [] });

    function finish() {
        stopTimer();
        const ks = Object.keys(S.wrong);
        const wt = ks.reduce((a, k) => a + S.wrong[k], 0);
        result.score = S.score;
        result.accuracy = Math.max(0, Math.round(((S.count - wt) / S.count) * 100));
        result.avgTime = S.times.length ? S.times.reduce((a, b) => a + b, 0) / S.times.length : 0;
        if (S.score > (mem.best || 0)) {
            mem.best = S.score;
            saveMem(mem);
        }
        result.weak = ks
            .sort((a, b) => S.wrong[b] - S.wrong[a])
            .slice(0, 10)
            .map((k) => ({ c: fromKey(k), n: S.wrong[k] }));
        screen.value = 'result';
    }

    function reset(hard) {
        S.i = 0;
        S.score = 0;
        S.streak = 0;
        S.queue = [];
        S.times = [];
        S.recent = [];
        S.last = null;
        // Okno unikania powtorzen - mniejsze niz liczba mozliwych kombinacji, inaczej
        // make() nie mialoby czego zwrocic (perOp: mul ma 2 uklady, div 1, mix 3).
        const perOp = S.mode === 'mix' ? 3 : S.mode === 'mul' ? 2 : 1;
        S.recentMax = Math.max(0, Math.min(8, S.tabs.length * S.max * perOp - 2));
        if (hard) {
            const src = Object.keys(S.wrong).length ? S.wrong : mem.wrong;
            const ks = Object.keys(src)
                .sort((a, b) => src[b] - src[a])
                .slice(0, 10);
            S.hardOnly = ks.map(fromKey);
            if (!S.hardOnly.length) S.hardOnly = null;
        } else {
            S.hardOnly = null;
        }
        S.wrong = {};
        screen.value = 'game';
        next();
    }

    function start({ tabs, mode, max, count, limit }) {
        S.tabs = tabs;
        S.mode = mode;
        S.max = max;
        S.count = count;
        S.limit = limit;
        reset(false);
    }

    function press(k) {
        if (k === 'go') {
            check();
            return;
        }
        if (k === 'del') answer.value = answer.value.slice(0, -1);
        else if (answer.value.length < 4) answer.value += k;
        err.value = '';
    }

    function quit() {
        stopTimer();
        screen.value = 'setup';
    }

    function backToSetup() {
        screen.value = 'setup';
    }

    return {
        mem,
        screen,
        S,
        answer,
        feedback,
        err,
        tbarPct,
        result,
        txt,
        val,
        start,
        press,
        quit,
        backToSetup,
        again: () => reset(false),
        hardOnly: () => reset(true),
        stopTimer,
    };
}
