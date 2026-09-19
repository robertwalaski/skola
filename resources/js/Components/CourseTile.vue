<script setup>
defineProps({
    href: { type: String, default: null },
    title: { type: String, required: true },
    desc: { type: String, required: true },
    emoji: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});
</script>

<template>
    <a
        v-if="!disabled"
        :href="href"
        class="block rounded-app border border-line bg-card p-5 shadow-sm transition hover:border-blue hover:shadow-md"
    >
        <div class="mb-1 text-2xl">{{ emoji }}</div>
        <div class="font-semibold">{{ title }}</div>
        <div class="mt-1 text-sm text-ink-soft">{{ desc }}</div>
    </a>
    <div v-else class="rounded-app border border-dashed border-line bg-card/50 p-5 opacity-60">
        <div class="mb-1 text-2xl">{{ emoji }}</div>
        <div class="font-semibold">{{ title }}</div>
        <div class="mt-1 text-sm text-ink-soft">{{ desc }}</div>
        <div class="mt-2 inline-block rounded-full bg-blue-soft px-2 py-0.5 text-xs text-blue">
            <slot name="badge" />
        </div>
    </div>
</template>
