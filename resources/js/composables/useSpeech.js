import { ref } from 'vue';

// Ported from public/legacy/alfabet.html's pickBestVoice()/speak()/speakSequence().
const PREFERRED_VOICES = [
    'Google US English',
    'Samantha',
    'Microsoft Aria Online (Natural) - English (United States)',
    'Microsoft Jenny Online (Natural) - English (United States)',
];

let bestVoice = null;

function pickBestVoice() {
    const voices = window.speechSynthesis.getVoices();
    if (!voices.length) return null;
    for (const name of PREFERRED_VOICES) {
        const v = voices.find((v) => v.name === name);
        if (v) return v;
    }
    const enUS = voices.find((v) => v.lang === 'en-US');
    if (enUS) return enUS;
    const en = voices.find((v) => v.lang && v.lang.indexOf('en') === 0);
    return en || voices[0];
}

const supported = typeof window !== 'undefined' && 'speechSynthesis' in window;
if (supported) {
    window.speechSynthesis.onvoiceschanged = () => {
        bestVoice = pickBestVoice();
    };
    bestVoice = pickBestVoice();
}

// Shared across every useSpeech() caller (speechSynthesis is a single global
// queue anyway) so a new sequence always invalidates a still-playing one.
let generation = 0;

export function useSpeech() {
    const warning = ref(supported ? '' : 'Ta przeglądarka nie obsługuje odczytu głosowego.');

    function makeUtterance(text) {
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-US';
        u.rate = 0.85;
        if (bestVoice) u.voice = bestVoice;
        return u;
    }

    // Plays each text in order, chained on the previous utterance's real onend
    // instead of a fixed setTimeout - the legacy page waited a flat 10s between
    // the letter and the word, which was both too long in most cases and (on a
    // slow voice) occasionally cut the word off.
    function speakSequence(texts, onDone) {
        if (!supported) {
            warning.value = 'Dźwięk niedostępny w tej przeglądarce.';
            if (onDone) onDone();
            return;
        }
        const gen = ++generation;
        window.speechSynthesis.cancel();

        function playAt(i) {
            if (gen !== generation) return;
            if (i >= texts.length) {
                if (onDone) onDone();
                return;
            }
            const u = makeUtterance(texts[i]);
            u.onend = () => playAt(i + 1);
            u.onerror = () => playAt(i + 1);
            window.speechSynthesis.speak(u);
        }
        playAt(0);
    }

    function speak(text) {
        speakSequence([text]);
    }

    return { speak, speakSequence, warning };
}
