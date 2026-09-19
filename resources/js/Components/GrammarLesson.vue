<script setup>
import { computed, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import ExerciseCard from './ExerciseCard.vue';
import { useAttempts } from '../composables/useAttempts.js';

const props = defineProps({
    content: { type: Object, required: true },
    section: { type: String, required: true }, // 'conditionals' | 'wishes' - matches Scoring's section key
});

const { record } = useAttempts();
const page = usePage();
const results = reactive({});

// Theory text is per-language ({pl,cs,sk,de}); exercises stay in English
// (that's the language being learned) regardless of the learner's locale.
function localized(field) {
    return field[page.props.locale] || field.pl;
}

function onAnswered(id, ok) {
    results[id] = ok;
    record('english', props.section, id, ok);
}

const totalExercises = computed(() => props.content.sections.reduce((sum, s) => sum + s.exercises.length, 0));
const answeredCount = computed(() => Object.keys(results).length);
const correctCount = computed(() => Object.values(results).filter(Boolean).length);
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">{{ content.title }}</h1>
            <p class="text-sm text-ink-soft">{{ localized(content.subtitle) }}</p>
            <p v-if="answeredCount" class="mt-2 text-sm font-semibold text-blue">
                {{ correctCount }}/{{ answeredCount }} poprawnych (razem {{ totalExercises }} pytań)
            </p>
        </template>

        <section v-for="s in content.sections" :key="s.id" class="mb-8">
            <h2 class="mb-1 text-lg font-semibold">{{ localized(s.title) }}</h2>
            <p class="mb-3 text-sm text-ink-soft">{{ localized(s.theory) }}</p>
            <div class="flex flex-col gap-3">
                <ExerciseCard v-for="ex in s.exercises" :key="ex.id" :exercise="ex" @answered="(ok) => onAnswered(ex.id, ok)" />
            </div>
        </section>
    </AppLayout>
</template>
