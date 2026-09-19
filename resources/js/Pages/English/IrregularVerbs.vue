<script setup>
import { computed, reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import data from '../../../content/english/irregular-verbs.json';
import { useSpeech } from '../../composables/useSpeech.js';
import { useAttempts } from '../../composables/useAttempts.js';

const VERBS = data.verbs;
const LEVELS = ['A2', 'B1', 'B2'];
const MODE_LABELS = { flash: 'Fiszki', gap: 'Uzupełnij formę', listen: 'Quiz ze słuchu' };

const { speakSequence, warning } = useSpeech();
const { record } = useAttempts();

function shuffle(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

function normalize(s) {
    return s.trim().toLowerCase();
}

function acceptableForms(s) {
    return s.split('/').map((x) => normalize(x));
}

function levelVerbIndices(level) {
    return VERBS.map((_, i) => i).filter((i) => VERBS[i].level === level);
}

// ---------- setup screen ----------
const selectedLevel = ref(null);
const selectedMode = ref(null);
const canStart = computed(() => selectedLevel.value !== null && selectedMode.value !== null);
const levelCounts = computed(() => Object.fromEntries(LEVELS.map((l) => [l, levelVerbIndices(l).length])));

// ---------- round state ----------
const screen = ref('setup'); // 'setup' | 'game'
const gameArea = ref(null); // 'flash' | 'gap' | 'listen' | 'done'

const round = reactive({ level: null, mode: null, items: [], idx: 0, score: 0, streak: 0, locked: false });

const progLabel = computed(() => {
    const total = round.items.length;
    if (gameArea.value === 'done') return `${total}/${total}`;
    return `${Math.min(round.idx + 1, total)}/${total}`;
});
const tbarPct = computed(() => {
    const total = round.items.length;
    if (gameArea.value === 'done') return 100;
    return total ? Math.round((round.idx / total) * 100) : 0;
});

function onStart() {
    if (!canStart.value) return;
    const indices = levelVerbIndices(selectedLevel.value);
    round.level = selectedLevel.value;
    round.mode = selectedMode.value;
    round.items = selectedMode.value === 'flash' ? indices : shuffle(indices);
    round.idx = 0;
    round.score = 0;
    round.streak = 0;
    round.locked = false;
    screen.value = 'game';
    gameArea.value = selectedMode.value;
    flRevealed.value = false;
    if (selectedMode.value === 'gap') renderGap();
    else if (selectedMode.value === 'listen') renderListen();
}

function backToMenu() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    screen.value = 'setup';
    gameArea.value = null;
}

function advance() {
    if (round.idx < round.items.length - 1) {
        round.idx++;
        if (round.mode === 'gap') renderGap();
        else if (round.mode === 'listen') renderListen();
    } else {
        finishRound();
    }
}

const done = reactive({ score: 0, total: 0 });

function finishRound() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    done.score = round.score;
    done.total = round.items.length;
    gameArea.value = 'done';
}

// ---------- FLASHCARDS ----------
const flashCurrent = computed(() => VERBS[round.items[round.idx]]);
const flRevealed = ref(false);
const flIsFirst = computed(() => round.idx === 0);
const flNextLabel = computed(() => (round.idx === round.items.length - 1 ? 'Zakończ ✓' : 'Dalej →'));

function flListenBtn() {
    speakSequence([flashCurrent.value.base]);
}
function flReveal() {
    flRevealed.value = true;
}
function flPrev() {
    if (round.idx > 0) {
        round.idx--;
        flRevealed.value = false;
    }
}
function flNext() {
    if (round.idx < round.items.length - 1) {
        round.idx++;
        flRevealed.value = false;
    } else {
        round.score = round.items.length;
        finishRound();
    }
}

// ---------- GAP FILL (2nd + 3rd form) ----------
const gap = reactive({ target: null, pastInput: '', partInput: '', answered: false, correct: false });

function renderGap() {
    gap.target = VERBS[round.items[round.idx]];
    gap.pastInput = '';
    gap.partInput = '';
    gap.answered = false;
    gap.correct = false;
}

function checkGap() {
    if (gap.answered || (!gap.pastInput.trim() && !gap.partInput.trim())) return;
    const okPast = acceptableForms(gap.target.past).includes(normalize(gap.pastInput));
    const okPart = acceptableForms(gap.target.participle).includes(normalize(gap.partInput));
    gap.correct = okPast && okPart;
    gap.answered = true;
    record('english', 'irregular-verbs', `gap:${gap.target.base}`, gap.correct);
    if (gap.correct) {
        round.score++;
        round.streak++;
    } else {
        round.streak = 0;
    }
    setTimeout(advance, 1800);
}

// ---------- LISTEN & CHOOSE ----------
const listen = reactive({ options: [], correctIndex: null, answeredIndex: null, fb: { text: '', cls: '' } });

function renderListen() {
    listen.fb = { text: '', cls: '' };
    listen.answeredIndex = null;
    const targetIdx = round.items[round.idx];
    const target = VERBS[targetIdx];
    listen.correctIndex = targetIdx;
    speakSequence([target.base]);

    const pool = levelVerbIndices(round.level).filter((i) => i !== targetIdx);
    const distractors = shuffle(pool).slice(0, 3);
    listen.options = shuffle([targetIdx, ...distractors]).map((i) => ({ index: i, ...VERBS[i] }));
}

function replayListen() {
    speakSequence([VERBS[round.items[round.idx]].base]);
}

function handleListenAnswer(oi) {
    if (round.locked) return;
    round.locked = true;
    listen.answeredIndex = oi;
    record('english', 'irregular-verbs', `listen:${VERBS[listen.correctIndex].base}`, oi === listen.correctIndex);
    if (oi === listen.correctIndex) {
        round.score++;
        round.streak++;
        listen.fb = { text: 'Świetnie! 🎉', cls: 'ok' };
    } else {
        round.streak = 0;
        listen.fb = { text: 'Spróbuj następnym razem!', cls: 'no' };
    }
    setTimeout(() => {
        round.locked = false;
        advance();
    }, 1400);
}
</script>

<template>
    <AppLayout back="/">
        <template v-if="screen === 'setup'" #header>
            <h1 class="text-xl font-semibold">Czasowniki nieregularne</h1>
            <p class="text-sm text-ink-soft">Fiszki, uzupełnianie form i quiz ze słuchu.</p>
        </template>

        <section v-if="screen === 'setup'" class="rounded-app border border-line bg-card p-[18px]">
            <p class="mb-2 text-sm text-ink-soft">Wybierz poziom</p>
            <div class="mb-5 flex gap-2">
                <button
                    v-for="l in LEVELS"
                    :key="l"
                    type="button"
                    class="h-14 flex-1 rounded-[10px] border text-sm font-semibold"
                    :class="selectedLevel === l ? 'border-blue bg-blue-soft text-blue' : 'border-line bg-card text-ink'"
                    @click="selectedLevel = l"
                >
                    {{ l }}
                    <span class="block text-xs font-normal text-ink-soft">{{ levelCounts[l] }} słówek</span>
                </button>
            </div>

            <p class="mb-2 text-sm text-ink-soft">Wybierz tryb gry</p>
            <div class="mb-5 flex gap-2">
                <button
                    v-for="m in ['flash', 'gap', 'listen']"
                    :key="m"
                    type="button"
                    class="h-11 flex-1 rounded-[10px] border px-3.5 text-sm"
                    :class="selectedMode === m ? 'border-blue bg-blue-soft font-semibold text-blue' : 'border-line bg-card text-ink'"
                    @click="selectedMode = m"
                >
                    {{ MODE_LABELS[m] }}
                </button>
            </div>

            <button
                type="button"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:bg-line disabled:text-ink-soft"
                :disabled="!canStart"
                @click="onStart"
            >
                Start
            </button>
            <p v-if="warning" class="mt-3.5 text-center text-sm text-ink-soft">{{ warning }}</p>
        </section>

        <section v-else>
            <div class="mb-4.5 grid grid-cols-3 gap-2.5">
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ round.score }}</b>
                    <span class="text-[13px] text-ink-soft">punkty</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ round.streak }}</b>
                    <span class="text-[13px] text-ink-soft">seria</span>
                </div>
                <div class="rounded-app border border-line bg-card px-3 py-2.5 text-center">
                    <b class="block text-2xl font-semibold leading-tight">{{ progLabel }}</b>
                    <span class="text-[13px] text-ink-soft">pytanie</span>
                </div>
            </div>
            <div class="mb-6 h-2 overflow-hidden rounded-full border border-line bg-card">
                <div class="h-full bg-gold transition-[width] duration-200" :style="{ width: tbarPct + '%' }" />
            </div>

            <div class="rounded-app border border-line bg-card p-[18px]">
                <!-- FLASHCARD MODE -->
                <div v-if="gameArea === 'flash'" class="text-center">
                    <p class="mb-2 text-5xl font-bold text-blue">{{ flashCurrent.base }}</p>
                    <button type="button" class="rounded-[10px] border border-gold bg-gold px-4.5 font-semibold text-white" style="height: 44px" @click="flListenBtn">
                        🔊 Odsłuchaj
                    </button>
                    <div v-if="!flRevealed" class="mt-4.5">
                        <button type="button" class="h-11 w-full rounded-[10px] border border-line bg-card" @click="flReveal">Pokaż formy</button>
                    </div>
                    <div v-else class="mt-4.5 rounded-app bg-paper p-4">
                        <p class="text-2xl font-semibold">{{ flashCurrent.past }} <span class="text-ink-soft">/</span> {{ flashCurrent.participle }}</p>
                        <p class="mt-1 text-ink-soft">{{ flashCurrent.meaning.pl }}</p>
                    </div>
                    <div class="mt-4.5 flex gap-2.5">
                        <button type="button" class="h-11 flex-1 rounded-[10px] border border-line bg-card disabled:cursor-not-allowed disabled:opacity-50" :disabled="flIsFirst" @click="flPrev">
                            ← Wstecz
                        </button>
                        <button type="button" class="h-[52px] flex-1 rounded-[10px] bg-blue font-semibold text-white hover:bg-[#1E508A]" @click="flNext">
                            {{ flNextLabel }}
                        </button>
                    </div>
                </div>

                <!-- GAP FILL MODE -->
                <div v-else-if="gameArea === 'gap'" class="text-center">
                    <p class="mb-1 text-4xl font-bold">{{ gap.target.base }}</p>
                    <p class="mb-4 text-sm text-ink-soft">{{ gap.target.meaning.pl }}</p>
                    <div class="mb-2 flex justify-center gap-3">
                        <div class="text-left">
                            <label class="mb-1 block text-xs text-ink-soft">Past Simple</label>
                            <input
                                v-model="gap.pastInput"
                                type="text"
                                :disabled="gap.answered"
                                class="w-36 rounded-lg border-2 px-2 py-1.5 text-center font-semibold focus:outline-none"
                                :class="!gap.answered ? 'border-line focus:border-blue' : gap.correct ? 'border-green bg-green-soft text-green' : 'border-red bg-red-soft text-red'"
                                @keydown.enter="checkGap"
                            />
                        </div>
                        <div class="text-left">
                            <label class="mb-1 block text-xs text-ink-soft">Past Participle</label>
                            <input
                                v-model="gap.partInput"
                                type="text"
                                :disabled="gap.answered"
                                class="w-36 rounded-lg border-2 px-2 py-1.5 text-center font-semibold focus:outline-none"
                                :class="!gap.answered ? 'border-line focus:border-blue' : gap.correct ? 'border-green bg-green-soft text-green' : 'border-red bg-red-soft text-red'"
                                @keydown.enter="checkGap"
                            />
                        </div>
                    </div>
                    <button v-if="!gap.answered" type="button" class="mt-3 h-10 rounded-[10px] border border-line bg-card px-4" @click="checkGap">Sprawdź</button>
                    <p v-else class="mt-3 text-sm font-semibold" :class="gap.correct ? 'text-green' : 'text-red'">
                        {{ gap.correct ? 'Dobrze!' : `Poprawnie: ${gap.target.past} / ${gap.target.participle}` }}
                    </p>
                </div>

                <!-- LISTEN & CHOOSE MODE -->
                <div v-else-if="gameArea === 'listen'">
                    <div class="mb-4 text-center">
                        <button type="button" class="rounded-[10px] border border-gold bg-gold px-4.5 font-semibold text-white" style="height: 44px" @click="replayListen">
                            🔊 Odsłuchaj ponownie
                        </button>
                    </div>
                    <p
                        class="mb-4 min-h-[34px] rounded-[10px] p-2 text-center text-[17px] font-semibold"
                        :class="listen.fb.cls === 'ok' ? 'bg-green-soft text-green' : listen.fb.cls === 'no' ? 'bg-red-soft text-red' : ''"
                    >
                        {{ listen.fb.text }}
                    </p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button
                            v-for="o in listen.options"
                            :key="o.index"
                            type="button"
                            class="rounded-xl border p-3 text-lg font-semibold disabled:pointer-events-none"
                            :class="{
                                'border-line bg-card': listen.answeredIndex === null || (o.index !== listen.correctIndex && o.index !== listen.answeredIndex),
                                'border-green bg-green-soft text-green': listen.answeredIndex !== null && o.index === listen.correctIndex,
                                'border-red bg-red-soft text-red': listen.answeredIndex !== null && o.index === listen.answeredIndex && o.index !== listen.correctIndex,
                            }"
                            :disabled="listen.answeredIndex !== null"
                            @click="handleListenAnswer(o.index)"
                        >
                            {{ o.base }}
                        </button>
                    </div>
                </div>

                <!-- ROUND DONE -->
                <div v-else-if="gameArea === 'done'" class="py-6 text-center">
                    <p class="m-0 text-4xl">🎉</p>
                    <p class="mt-2 text-xl font-semibold">Ukończono rundę!</p>
                    <p class="my-2 text-[44px] font-bold text-blue">{{ done.score }}/{{ done.total }}</p>
                    <button type="button" class="mt-3.5 h-[52px] w-full rounded-[10px] bg-blue font-semibold text-white hover:bg-[#1E508A]" @click="backToMenu">
                        Wróć do menu
                    </button>
                </div>
            </div>
            <button v-if="gameArea !== 'done'" type="button" class="mt-3.5 h-11 w-full rounded-[10px] border border-line bg-card" @click="backToMenu">
                ✕ Zakończ i wróć do menu
            </button>
        </section>
    </AppLayout>
</template>
