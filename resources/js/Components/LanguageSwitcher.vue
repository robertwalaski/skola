<script setup>
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

const languages = [
    { code: 'pl', label: 'PL' },
    { code: 'cs', label: 'CS' },
    { code: 'sk', label: 'SK' },
    { code: 'de', label: 'DE' },
];

function switchTo(code) {
    if (code === page.props.locale) return;
    router.post('/jezyk', { locale: code }, { preserveScroll: true });
}
</script>

<template>
    <div class="flex gap-1.5">
        <button
            v-for="lang in languages"
            :key="lang.code"
            type="button"
            class="rounded-full px-3 py-1 text-xs font-medium"
            :class="lang.code === page.props.locale ? 'bg-blue text-white' : 'bg-blue-soft text-ink-soft hover:opacity-80'"
            @click="switchTo(lang.code)"
        >
            {{ lang.label }}
        </button>
    </div>
</template>
