<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useTrans } from '../composables/useTrans';

defineProps({
    back: { type: String, default: null },
});

const { t } = useTrans();
const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-paper text-ink">
        <div class="mx-auto max-w-3xl px-4 pb-14 pt-8">
            <div class="mb-2 flex justify-end gap-3 text-sm">
                <template v-if="page.props.auth.user">
                    <span class="text-ink-soft">{{ page.props.auth.user.nick }} - {{ page.props.auth.user.points }} pkt</span>
                    <Link href="/wyloguj" method="post" as="button" class="text-blue">Wyloguj</Link>
                </template>
                <template v-else>
                    <Link href="/logowanie" class="text-ink-soft hover:text-ink">Zaloguj</Link>
                    <Link href="/rejestracja" class="text-blue">Zarejestruj</Link>
                </template>
            </div>

            <header class="mb-8 text-center">
                <Link v-if="back" :href="back" class="mb-3 inline-block text-sm text-ink-soft hover:text-ink">
                    &larr; {{ t('home.title') }}
                </Link>
                <slot name="header" />
            </header>

            <slot />

            <footer class="mt-12 text-center text-xs text-ink-soft">
                szkola.dev.interwal.net
            </footer>
        </div>
    </div>
</template>
