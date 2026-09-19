<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    exercise: { type: Object, required: true },
});
const emit = defineEmits(['answered']);

const answered = ref(false);
const correct = ref(false);
const gapValue = ref('');
const chosenOption = ref(null);

function normalize(s) {
    return s.trim().toLowerCase().replace(/\s+/g, ' ');
}

const gapParts = computed(() => props.exercise.prompt.split('___'));

function submitGap() {
    if (answered.value || !gapValue.value.trim()) return;
    const ok = props.exercise.accepted.some((a) => normalize(a) === normalize(gapValue.value));
    answered.value = true;
    correct.value = ok;
    emit('answered', ok);
}

function chooseOption(opt) {
    if (answered.value) return;
    chosenOption.value = opt;
    const ok = opt === props.exercise.answer;
    answered.value = true;
    correct.value = ok;
    emit('answered', ok);
}
</script>

<template>
    <div class="rounded-app border border-line bg-card p-4">
        <p v-if="exercise.type === 'gap'" class="text-[17px]">
            {{ gapParts[0] }}<input
                v-model="gapValue"
                type="text"
                :disabled="answered"
                class="mx-1 w-32 rounded-md border-2 px-2 py-0.5 text-center font-semibold focus:outline-none"
                :class="!answered ? 'border-line focus:border-blue' : correct ? 'border-green bg-green-soft text-green' : 'border-red bg-red-soft text-red'"
                @keydown.enter="submitGap"
            />{{ gapParts[1] }}
        </p>
        <template v-else>
            <p class="mb-3 text-[17px]">{{ exercise.prompt }}</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="opt in exercise.options"
                    :key="opt"
                    type="button"
                    class="h-10 rounded-[10px] border px-3.5 text-sm disabled:pointer-events-none"
                    :class="{
                        'border-line bg-card text-ink': !answered || (opt !== exercise.answer && opt !== chosenOption),
                        'border-green bg-green-soft text-green': answered && opt === exercise.answer,
                        'border-red bg-red-soft text-red': answered && opt === chosenOption && opt !== exercise.answer,
                    }"
                    :disabled="answered"
                    @click="chooseOption(opt)"
                >
                    {{ opt }}
                </button>
            </div>
        </template>

        <div v-if="exercise.type === 'gap' && !answered" class="mt-2">
            <button type="button" class="h-8 rounded-md border border-line bg-card px-3 text-xs" @click="submitGap">Sprawdź</button>
        </div>
        <p v-if="answered" class="mt-2 text-sm font-semibold" :class="correct ? 'text-green' : 'text-red'">
            {{ correct ? 'Dobrze!' : exercise.type === 'gap' ? `Poprawnie: ${exercise.accepted[0]}` : `Poprawnie: ${exercise.answer}` }}
        </p>
    </div>
</template>
