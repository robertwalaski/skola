<script setup>
import { Link } from '@inertiajs/vue3';
import { useTrans } from '../composables/useTrans';

defineProps({
    back: { type: String, default: null },
});

const { t } = useTrans();
</script>

<template>
    <div class="min-h-screen bg-paper text-ink">
        <div class="mx-auto max-w-3xl px-4 pb-14 pt-8">
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
