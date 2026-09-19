<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import CourseTile from '../Components/CourseTile.vue';
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import { useTrans } from '../composables/useTrans';

const { t } = useTrans();
const page = usePage();
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-3xl font-bold">{{ t('home.title') }}</h1>
            <p class="mt-1 text-ink-soft">{{ t('home.subtitle') }}</p>

            <div class="mt-4 flex justify-center">
                <LanguageSwitcher />
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
                    href="/angielski/czasowniki-nieregularne"
                    emoji="📖"
                    :title="t('course.irregular_verbs.title')"
                    :desc="t('course.irregular_verbs.desc')"
                />
                <CourseTile
                    href="/angielski/conditionals"
                    emoji="🔀"
                    :title="t('course.conditionals.title')"
                    :desc="t('course.conditionals.desc')"
                />
                <CourseTile
                    href="/angielski/wishes"
                    emoji="💭"
                    :title="t('course.wishes.title')"
                    :desc="t('course.wishes.desc')"
                />
            </div>
        </section>

        <p v-if="page.props.auth.user" class="mt-10 text-center text-sm text-ink-soft">
            <Link href="/dolacz" class="underline">Dołącz do klasy</Link>
            ·
            <Link href="/nauczyciel" class="underline">Panel nauczyciela</Link>
        </p>
    </AppLayout>
</template>
