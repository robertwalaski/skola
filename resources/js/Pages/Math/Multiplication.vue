<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Keypad from '../../Components/Keypad.vue';
import { useMultiplicationGame } from '../../games/multiplication.js';
import { useAttempts } from '../../composables/useAttempts.js';

const { record } = useAttempts();
const g = useMultiplicationGame((exerciseKey, correct) => record('math', 'multiplication', exerciseKey, correct));

const tables = Array.from({ length: 11 }, (_, i) => i + 2); // 2..12
const selected = reactive(new Set([2, 3]));
function toggleTable(n) {
    if (selected.has(n)) selected.delete(n);
    else selected.add(n);
}

const mode = ref('mix');
const maxn = ref(10);
const qcount = ref(20);
const timed = ref(true);
const tlimit = ref(10);
const setupErr = ref('');

function onStart() {
    const tabs = Array.from(selected);
    if (!tabs.length) {
        setupErr.value = 'Wybierz co najmniej jedną tabliczkę';
        return;
    }
    setupErr.value = '';
    g.start({ tabs, mode: mode.value, max: maxn.value, count: qcount.value, limit: timed.value ? tlimit.value : 0 });
}

function onKeydown(e) {
    if (g.screen.value !== 'game') return;
    if (e.key >= '0' && e.key <= '9') {
        e.preventDefault();
        g.press(e.key);
    } else if (e.key === 'Backspace') {
        e.preventDefault();
        g.press('del');
    } else if (e.key === 'Enter') {
        e.preventDefault();
        g.press('go');
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    g.stopTimer();
});
</script>

<template>
    <AppLayout back="/">
        <template v-if="g.screen.value !== 'result'" #header>
            <h1 v-if="g.screen.value === 'setup'" class="text-xl font-semibold">Tabliczka mnożenia i dzielenia</h1>
            <p v-if="g.screen.value === 'setup'" class="text-sm text-ink-soft">Wybierz tabliczki, potem licz.</p>
        </template>

        <section v-if="g.screen.value === 'setup'" class="rounded-app border border-line bg-card p-[18px]">
            <p class="mb-2 text-sm text-ink-soft">Tabliczki</p>
            <div class="mb-5 flex flex-wrap gap-2">
                <button
                    v-for="n in tables"
                    :key="n"
                    type="button"
                    class="h-11 min-w-12 rounded-[10px] border px-3.5"
                    :class="selected.has(n) ? 'border-blue bg-blue-soft font-semibold text-blue' : 'border-line bg-card text-ink'"
                    @click="toggleTable(n)"
                >
                    {{ n }}
                </button>
            </div>

            <p class="mb-2 text-sm text-ink-soft">Działania</p>
            <div class="mb-5 flex gap-2">
                <button
                    v-for="m in [{ k: 'mul', l: 'Mnożenie' }, { k: 'div', l: 'Dzielenie' }, { k: 'mix', l: 'Mieszane' }]"
                    :key="m.k"
                    type="button"
                    class="h-11 flex-1 rounded-[10px] border px-3.5"
                    :class="mode === m.k ? 'border-blue bg-blue-soft font-semibold text-blue' : 'border-line bg-card text-ink'"
                    @click="mode = m.k"
                >
                    {{ m.l }}
                </button>
            </div>

            <div class="mb-3.5 flex items-center gap-3">
                <label class="min-w-[140px] text-sm text-ink-soft" for="maxn">Drugi czynnik do</label>
                <input id="maxn" v-model.number="maxn" type="range" min="5" max="20" step="1" class="flex-1 accent-blue" />
                <span class="min-w-[70px] text-right font-semibold">{{ maxn }}</span>
            </div>
            <div class="mb-3.5 flex items-center gap-3">
                <label class="min-w-[140px] text-sm text-ink-soft" for="qcount">Liczba pytań</label>
                <input id="qcount" v-model.number="qcount" type="range" min="10" max="60" step="5" class="flex-1 accent-blue" />
                <span class="min-w-[70px] text-right font-semibold">{{ qcount }}</span>
            </div>

            <div class="mb-3.5 flex gap-2">
                <button
                    type="button"
                    class="h-11 flex-1 rounded-[10px] border px-3.5"
                    :class="timed ? 'border-blue bg-blue-soft font-semibold text-blue' : 'border-line bg-card text-ink'"
                    @click="timed = !timed"
                >
                    {{ timed ? 'Na czas' : 'Bez czasu' }}
                </button>
            </div>
            <div v-if="timed" class="mb-3.5 flex items-center gap-3">
                <label class="min-w-[140px] text-sm text-ink-soft" for="tlimit">Czas na pytanie</label>
                <input id="tlimit" v-model.number="tlimit" type="range" min="1" max="20" step="1" class="flex-1 accent-blue" />
                <span class="min-w-[70px] text-right font-semibold">{{ tlimit }} s</span>
            </div>

            <p class="min-h-[22px] text-center text-sm text-red">{{ setupErr }}</p>
            <button type="button" class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A]" @click="onStart">
                Start
            </button>
            <p v-if="g.mem.best" class="mt-3.5 text-center text-sm text-ink-soft">Rekord: {{ g.mem.best }} pkt</p>
        </section>

        <section v-else-if="g.screen.value === 'game'" class="game">
            <div class="stats mb-4.5 grid grid-cols-3 gap-2.5">
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.S.score }}</b>
                    <span class="text-[13px] text-ink-soft">punkty</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.S.streak }}</b>
                    <span class="text-[13px] text-ink-soft">seria</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.S.i }}/{{ g.S.count }}</b>
                    <span class="text-[13px] text-ink-soft">pytanie</span>
                </div>
            </div>
            <div class="bar mb-6 h-2 overflow-hidden rounded-full border border-line bg-card">
                <div class="h-full bg-gold" :style="{ width: g.tbarPct.value + '%' }" />
            </div>
            <div class="card rounded-app border border-line bg-card p-[18px]">
                <div class="play-main min-w-0">
                    <p class="q my-2 text-center text-[clamp(34px,8vmin,56px)] font-semibold tracking-wide tabular-nums">
                        {{ g.S.cur ? g.txt(g.S.cur) : '' }}
                    </p>
                    <p class="fb mb-3.5 min-h-7 text-center text-[17px] font-semibold" :class="g.feedback.value.cls === 'ok' ? 'text-green' : g.feedback.value.cls === 'no' ? 'text-red' : ''">
                        {{ g.feedback.value.text }}
                    </p>
                    <div class="flex justify-center gap-2.5">
                        <input
                            type="text"
                            readonly
                            inputmode="none"
                            autocomplete="off"
                            aria-label="Odpowiedź"
                            :value="g.answer.value"
                            class="h-[clamp(46px,7vh,56px)] w-[150px] rounded-xl border-2 border-line bg-card text-center text-2xl font-semibold tabular-nums focus:border-blue focus:outline-none"
                        />
                    </div>
                    <p class="err mt-1.5 min-h-[22px] text-center text-sm text-red">{{ g.err.value }}</p>
                </div>
                <Keypad @press="g.press" />
            </div>
            <button type="button" class="quit mt-3.5 h-11 w-full rounded-[10px] border border-line bg-card" @click="g.quit">Przerwij</button>
        </section>

        <section v-else>
            <div class="mb-4.5 grid grid-cols-3 gap-2.5">
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.result.score }}</b>
                    <span class="text-[13px] text-ink-soft">wynik</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.result.accuracy }}%</b>
                    <span class="text-[13px] text-ink-soft">poprawne</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ g.result.avgTime.toFixed(1) }} s</b>
                    <span class="text-[13px] text-ink-soft">średni czas</span>
                </div>
            </div>
            <div class="rounded-app border border-line bg-card p-[18px]">
                <template v-if="g.result.weak.length">
                    <p class="mb-2 text-sm text-ink-soft">Najczęstsze pomyłki</p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="w in g.result.weak"
                            :key="g.txt(w.c)"
                            class="inline-block rounded-[10px] bg-red-soft px-3 py-1.5 text-[15px] font-semibold tabular-nums text-red"
                        >
                            {{ g.txt(w.c) }} = {{ g.val(w.c) }} ({{ w.n }})
                        </span>
                    </div>
                </template>
                <p v-else class="rounded-app bg-green-soft p-3.5 font-semibold text-green">
                    Bezbłędnie. Można dołożyć kolejną tabliczkę.
                </p>
            </div>
            <div class="mt-3.5 flex gap-2">
                <button type="button" class="h-11 flex-1 rounded-[10px] border border-line bg-card" @click="g.again">Jeszcze raz</button>
                <button type="button" class="h-11 flex-1 rounded-[10px] border border-line bg-card" @click="g.hardOnly">Tylko trudne</button>
                <button type="button" class="h-11 flex-1 rounded-[10px] border border-line bg-card" @click="g.backToSetup">Ustawienia</button>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
/* Ported from public/legacy/tabliczka.html - keeps the game usable on a phone
   or tablet held sideways, where a single-column layout does not fit. */
@media (max-width: 430px) {
    .stats :deep(b) {
        font-size: 22px;
    }
}
@media (max-height: 760px) {
    .stats {
        margin-bottom: 8px;
    }
    .bar {
        margin-bottom: 10px;
    }
    .card {
        padding: 14px;
    }
    :deep(.q) {
        margin: 2px 0;
    }
    :deep(.fb) {
        min-height: 20px;
        margin-bottom: 8px;
    }
    :deep(.keypad) {
        gap: 8px;
    }
    .quit {
        margin-top: 8px;
    }
}
@media (orientation: landscape) and (max-height: 560px) {
    .card {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        padding: 12px;
    }
    .play-main {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    :deep(.q) {
        margin: 0 0 6px;
        font-size: clamp(30px, 9vh, 52px);
    }
    :deep(.keypad) {
        flex: 0 0 auto;
        width: min(46%, 300px);
        max-width: none;
        margin: 0;
        gap: 7px;
        --key-h: clamp(38px, 12vh, 44px);
        --key-font: clamp(18px, 4.4vh, 24px);
        --key-del-font: clamp(16px, 3.8vh, 22px);
        --key-go-font: clamp(15px, 3.2vh, 20px);
    }
}

@media (orientation: landscape) and (min-height: 561px) and (max-height: 860px) {
    .card {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 28px;
        padding: 22px;
    }
    .play-main {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    :deep(.q) {
        margin: 0 0 14px;
    }
    :deep(.keypad) {
        flex: 0 0 auto;
        width: min(48%, 340px);
        max-width: none;
        margin: 0;
        --key-h: clamp(52px, 10vh, 78px);
    }
}
</style>
