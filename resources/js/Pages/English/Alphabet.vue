<script setup>
import { computed, reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import alphabetData from '../../../content/english/alphabet.json';
import { useSpeech } from '../../composables/useSpeech.js';
import { MODES, batchDone, emptyProgress, isUnlocked, loadProgress, modesDone, saveProgress } from '../../games/alphabetProgress.js';

const LETTERS = alphabetData.letters;
const BATCHES = alphabetData.batches;
const MODE_LABELS = { flash: 'Fiszki', missing: 'Brakująca literka', listen: 'Usłysz i wybierz' };

const { speakSequence, warning } = useSpeech();

const progress = reactive(loadProgress(BATCHES.length));

function shuffle(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

// ---------- setup screen ----------
const selectedBatch = ref(null);
const selectedMode = ref(null);
const canStart = computed(() => selectedBatch.value !== null && selectedMode.value !== null);

const batchList = computed(() =>
    BATCHES.map((b, i) => {
        const unlocked = isUnlocked(progress, i);
        const done = batchDone(progress, i);
        let subtitle;
        if (!unlocked) subtitle = 'zablokowane';
        else if (done) subtitle = `${b.to - b.from} liter`;
        else subtitle = `${modesDone(progress, i)}/${MODES.length} trybów`;
        return { ...b, index: i, unlocked, done, subtitle };
    }),
);

function selectBatch(i) {
    if (!isUnlocked(progress, i)) return;
    selectedBatch.value = i;
}

function resetProgress() {
    progress.splice(0, progress.length, ...emptyProgress(BATCHES.length));
    saveProgress(progress);
    selectedBatch.value = null;
}

// ---------- round state ----------
const screen = ref('setup'); // 'setup' | 'game'
const gameArea = ref(null); // 'flash' | 'missing' | 'listen' | 'done'

const round = reactive({
    mode: null,
    batchIndex: null,
    items: [],
    idx: 0,
    score: 0,
    streak: 0,
    locked: false,
});

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
    startRound(selectedBatch.value, selectedMode.value);
}

function startRound(batchIndex, mode) {
    const b = BATCHES[batchIndex];
    const indices = [];
    for (let i = b.from; i < b.to; i++) indices.push(i);
    round.mode = mode;
    round.batchIndex = batchIndex;
    round.items = mode === 'flash' ? indices : shuffle(indices);
    round.idx = 0;
    round.score = 0;
    round.streak = 0;
    round.locked = false;
    screen.value = 'game';
    gameArea.value = mode;
    if (mode === 'missing') renderMissing();
    else if (mode === 'listen') renderListen();
}

function backToMenu() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    screen.value = 'setup';
    gameArea.value = null;
}

function advance() {
    if (round.idx < round.items.length - 1) {
        round.idx++;
        if (round.mode === 'missing') renderMissing();
        else if (round.mode === 'listen') renderListen();
    } else {
        finishRound();
    }
}

const done = reactive({ score: 0, total: 0, unlockMsg: '' });

function finishRound() {
    if (typeof window !== 'undefined' && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    const total = round.items.length;
    done.score = round.score;
    done.total = total;
    let unlockMsg = '';
    const wasDone = batchDone(progress, round.batchIndex);
    if (!progress[round.batchIndex][round.mode]) {
        progress[round.batchIndex][round.mode] = true;
        saveProgress(progress);
    }
    if (!wasDone && batchDone(progress, round.batchIndex) && round.batchIndex < BATCHES.length - 1) {
        unlockMsg = `Odblokowano nowy zestaw liter: ${BATCHES[round.batchIndex + 1].name}!`;
    }
    done.unlockMsg = unlockMsg;
    gameArea.value = 'done';
}

// ---------- FLASHCARDS ----------
const flashCurrent = computed(() => LETTERS[round.items[round.idx]]);
const flashLetterPair = computed(() => flashCurrent.value.letter + flashCurrent.value.letter.toLowerCase());
const flIsFirst = computed(() => round.idx === 0);
const flNextLabel = computed(() => (round.idx === round.items.length - 1 ? 'Zakończ ✓' : 'Dalej →'));

function flListen() {
    speakSequence([flashCurrent.value.letter, flashCurrent.value.word]);
}
function flPrev() {
    if (round.idx > 0) round.idx--;
}
function flNext() {
    if (round.idx < round.items.length - 1) {
        round.idx++;
    } else {
        round.score = round.items.length; // completing flashcards counts as full completion
        finishRound();
    }
}

// ---------- MISSING LETTER ----------
const missing = reactive({ seq: [], options: [], fb: { text: '', cls: '' }, answeredIndex: null, correctIndex: null });

function renderMissing() {
    missing.fb = { text: '', cls: '' };
    missing.answeredIndex = null;
    const targetIdx = round.items[round.idx];
    const target = LETTERS[targetIdx];

    let seq;
    if (targetIdx === 0) seq = [{ blank: true }, { letter: LETTERS[1].letter }, { letter: LETTERS[2].letter }];
    else if (targetIdx === 25) seq = [{ letter: LETTERS[23].letter }, { letter: LETTERS[24].letter }, { blank: true }];
    else seq = [{ letter: LETTERS[targetIdx - 1].letter }, { blank: true }, { letter: LETTERS[targetIdx + 1].letter }];
    missing.seq = seq.map((s) => (s.blank ? { blank: true, letter: target.letter, revealed: false } : { blank: false, letter: s.letter }));
    missing.correctIndex = targetIdx;

    const pool = [];
    for (let i = 0; i < LETTERS.length; i++) if (i !== targetIdx) pool.push(i);
    const chosen = shuffle(pool).slice(0, 3);
    missing.options = shuffle([targetIdx, ...chosen]).map((oi) => ({ index: oi, ...LETTERS[oi] }));
}

function handleMissingAnswer(oi) {
    if (round.locked) return;
    round.locked = true;
    missing.answeredIndex = oi;
    if (oi === missing.correctIndex) {
        round.score++;
        round.streak++;
        missing.fb = { text: 'Świetnie! 🎉', cls: 'ok' };
        const blank = missing.seq.find((s) => s.blank);
        if (blank) blank.revealed = true;
        speakSequence(
            missing.seq.map((s) => s.letter),
            () => {
                setTimeout(() => {
                    round.locked = false;
                    advance();
                }, 500);
            },
        );
    } else {
        round.streak = 0;
        missing.fb = { text: 'Spróbuj następnym razem!', cls: 'no' };
        setTimeout(() => {
            round.locked = false;
            advance();
        }, 1400);
    }
}

// ---------- LISTEN & CHOOSE ----------
const listen = reactive({ options: [], fb: { text: '', cls: '' }, answeredIndex: null, correctIndex: null });

function playListenAudio(target) {
    speakSequence([target.letter, target.word]);
}

function renderListen() {
    listen.fb = { text: '', cls: '' };
    listen.answeredIndex = null;
    const targetIdx = round.items[round.idx];
    const target = LETTERS[targetIdx];
    listen.correctIndex = targetIdx;
    playListenAudio(target);

    const b = BATCHES[round.batchIndex];
    const batchIndices = [];
    for (let i = b.from; i < b.to; i++) batchIndices.push(i);
    listen.options = shuffle(batchIndices).map((oi) => ({ index: oi, ...LETTERS[oi] }));
}

function replayListen() {
    playListenAudio(LETTERS[round.items[round.idx]]);
}

function handleListenAnswer(oi) {
    if (round.locked) return;
    round.locked = true;
    listen.answeredIndex = oi;
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
            <h1 class="text-xl font-semibold">Angielski alfabet</h1>
            <p class="text-sm text-ink-soft">Naucz się liter i słówek po angielsku.</p>
        </template>

        <section v-if="screen === 'setup'" class="rounded-app border border-line bg-card p-[18px]">
            <p class="mb-2 text-sm text-ink-soft">Wybierz zestaw liter</p>
            <div class="mb-5 grid grid-cols-2 gap-2.5" role="group" aria-label="Zestaw liter">
                <button
                    v-for="b in batchList"
                    :key="b.index"
                    type="button"
                    :disabled="!b.unlocked"
                    class="flex flex-col items-center gap-1 rounded-app border-2 p-3.5 disabled:cursor-not-allowed disabled:opacity-50"
                    :class="selectedBatch === b.index ? 'border-blue bg-blue-soft' : 'border-line bg-card'"
                    @click="selectBatch(b.index)"
                >
                    <b class="text-lg">{{ b.name }}<span v-if="b.done" class="ml-1 font-bold text-green">✓</span></b>
                    <span class="text-xs text-ink-soft">{{ b.subtitle }}<template v-if="!b.unlocked"> 🔒</template></span>
                </button>
            </div>

            <p class="mb-2 text-sm text-ink-soft">Wybierz tryb gry</p>
            <div class="mb-5 flex gap-2">
                <button
                    v-for="m in ['flash', 'missing', 'listen']"
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
            <p class="mt-3.5 text-center text-sm">
                <a href="#" class="text-ink-soft" @click.prevent="resetProgress">Wyczyść postęp</a>
            </p>
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
                    <p class="mb-2 text-[88px] font-bold leading-none text-blue">{{ flashLetterPair }}</p>
                    <p class="my-1.5 text-[64px] leading-none">{{ flashCurrent.emoji }}</p>
                    <p class="mb-4.5 mt-1.5 text-2xl font-semibold">{{ flashCurrent.word }}</p>
                    <button type="button" class="rounded-[10px] border border-gold bg-gold px-4.5 py-0 font-semibold text-white" style="height: 44px" @click="flListen">
                        🔊 Odsłuchaj
                    </button>
                    <div class="mt-4.5 flex gap-2.5">
                        <button type="button" class="h-11 flex-1 rounded-[10px] border border-line bg-card disabled:cursor-not-allowed disabled:opacity-50" :disabled="flIsFirst" @click="flPrev">
                            ← Wstecz
                        </button>
                        <button type="button" class="h-[52px] flex-1 rounded-[10px] bg-blue font-semibold text-white hover:bg-[#1E508A]" @click="flNext">
                            {{ flNextLabel }}
                        </button>
                    </div>
                </div>

                <!-- MISSING LETTER MODE -->
                <div v-else-if="gameArea === 'missing'">
                    <div class="mb-5.5 mt-2.5 flex justify-center gap-3">
                        <div
                            v-for="(s, idx) in missing.seq"
                            :key="idx"
                            class="flex h-[76px] w-16 items-center justify-center rounded-xl border-2 text-4xl font-bold"
                            :class="s.blank && !s.revealed ? 'border-dashed border-blue bg-blue-soft text-blue' : 'border-line bg-card'"
                        >
                            {{ s.blank && !s.revealed ? '?' : s.letter }}
                        </div>
                    </div>
                    <p
                        class="mb-4 min-h-[34px] rounded-[10px] p-2 text-center text-[17px] font-semibold"
                        :class="missing.fb.cls === 'ok' ? 'bg-green-soft text-green' : missing.fb.cls === 'no' ? 'bg-red-soft text-red' : ''"
                    >
                        {{ missing.fb.text }}
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="o in missing.options"
                            :key="o.index"
                            type="button"
                            class="flex flex-col items-center gap-1 rounded-xl border p-3.5 disabled:pointer-events-none"
                            :class="{
                                'border-line bg-card': missing.answeredIndex === null || (o.index !== missing.correctIndex && o.index !== missing.answeredIndex),
                                'border-green bg-green-soft': missing.answeredIndex !== null && o.index === missing.correctIndex,
                                'border-red bg-red-soft': missing.answeredIndex !== null && o.index === missing.answeredIndex && o.index !== missing.correctIndex,
                            }"
                            :disabled="missing.answeredIndex !== null"
                            @click="handleMissingAnswer(o.index)"
                        >
                            <span class="text-[34px] leading-none">{{ o.emoji }}</span>
                            <span class="text-xl font-bold">{{ o.letter }}</span>
                        </button>
                    </div>
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
                    <div class="grid gap-2.5" style="grid-template-columns: repeat(auto-fill, minmax(84px, 1fr))">
                        <button
                            v-for="o in listen.options"
                            :key="o.index"
                            type="button"
                            class="flex flex-col items-center gap-1 rounded-xl border p-3.5 disabled:pointer-events-none"
                            :class="{
                                'border-line bg-card': listen.answeredIndex === null || (o.index !== listen.correctIndex && o.index !== listen.answeredIndex),
                                'border-green bg-green-soft': listen.answeredIndex !== null && o.index === listen.correctIndex,
                                'border-red bg-red-soft': listen.answeredIndex !== null && o.index === listen.answeredIndex && o.index !== listen.correctIndex,
                            }"
                            :disabled="listen.answeredIndex !== null"
                            @click="handleListenAnswer(o.index)"
                        >
                            <span class="text-[34px] leading-none">{{ o.emoji }}</span>
                            <span class="text-xl font-bold">{{ o.letter }}</span>
                        </button>
                    </div>
                </div>

                <!-- ROUND DONE -->
                <div v-else-if="gameArea === 'done'" class="py-6 text-center">
                    <p class="m-0 text-4xl">🎉</p>
                    <p class="mt-2 text-xl font-semibold">Ukończono rundę!</p>
                    <p class="my-2 text-[44px] font-bold text-blue">{{ done.score }}/{{ done.total }}</p>
                    <p v-if="done.unlockMsg" class="text-sm text-ink-soft">{{ done.unlockMsg }}</p>
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
