<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import CourseTile from '../Components/CourseTile.vue';
import { useTrans } from '../composables/useTrans';

const { t } = useTrans();

const languages = [
    { code: 'pl', label: 'PL', enabled: true },
    { code: 'cs', label: 'CS', enabled: false },
    { code: 'sk', label: 'SK', enabled: false },
    { code: 'de', label: 'DE', enabled: false },
];
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-3xl font-bold">{{ t('home.title') }}</h1>
            <p class="mt-1 text-ink-soft">{{ t('home.subtitle') }}</p>

            <div class="mt-4 flex justify-center gap-2">
                <span
                    v-for="lang in languages"
                    :key="lang.code"
                    class="rounded-full px-3 py-1 text-xs font-medium"
                    :class="lang.enabled
                        ? 'bg-blue text-white'
                        : 'bg-blue-soft text-ink-soft opacity-60'"
                >
                    {{ lang.label }}
                </span>
            </div>
        </template>

        <section class="mb-10">
            <h2 class="mb-3 text-lg font-semibold text-ink-soft">{{ t('home.section.kids') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <CourseTile
                    href="/matematyka/tabliczka"
                    emoji="🔢"
                    :title="t('course.multiplication.title')"
                    :desc="t('course.multiplication.desc')"
                />
                <CourseTile
                    href="/angielski/alfabet"
                    emoji="🔤"
                    :title="t('course.alphabet.title')"
                    :desc="t('course.alphabet.desc')"
                />
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold text-ink-soft">{{ t('home.section.adults') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <CourseTile
                    disabled
                    emoji="📖"
                    :title="t('course.irregular_verbs.title')"
                    :desc="t('course.irregular_verbs.desc')"
                >
                    <template #badge>{{ t('home.coming_soon') }}</template>
                </CourseTile>
                <CourseTile
                    disabled
                    emoji="🔀"
                    :title="t('course.conditionals.title')"
                    :desc="t('course.conditionals.desc')"
                >
                    <template #badge>{{ t('home.coming_soon') }}</template>
                </CourseTile>
                <CourseTile
                    disabled
                    emoji="💭"
                    :title="t('course.wishes.title')"
                    :desc="t('course.wishes.desc')"
                >
                    <template #badge>{{ t('home.coming_soon') }}</template>
                </CourseTile>
            </div>
        </section>
    </AppLayout>
</template>
