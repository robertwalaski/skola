<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
    period: { type: String, required: true },
    global: { type: Array, required: true },
    group: { type: Object, default: null },
});
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Ranking</h1>
            <div class="mt-3 flex justify-center gap-2">
                <Link
                    href="/ranking?period=week"
                    class="rounded-full px-3 py-1 text-sm font-medium"
                    :class="period === 'week' ? 'bg-blue text-white' : 'bg-blue-soft text-ink-soft'"
                >
                    Ten tydzień
                </Link>
                <Link
                    href="/ranking?period=all"
                    class="rounded-full px-3 py-1 text-sm font-medium"
                    :class="period === 'all' ? 'bg-blue text-white' : 'bg-blue-soft text-ink-soft'"
                >
                    Cały czas
                </Link>
            </div>
        </template>

        <section v-if="group" class="mb-8">
            <h2 class="mb-3 text-lg font-semibold text-ink-soft">Klasa: {{ group.name }}</h2>
            <ol class="flex flex-col gap-2">
                <li
                    v-for="(entry, i) in group.entries"
                    :key="entry.nick"
                    class="flex items-center justify-between rounded-app border border-line bg-card px-4 py-2.5"
                >
                    <span><span class="mr-2 text-ink-soft">{{ i + 1 }}.</span>{{ entry.nick }}</span>
                    <span class="font-semibold text-blue">{{ entry.points }} pkt</span>
                </li>
                <li v-if="!group.entries.length" class="text-center text-sm text-ink-soft">Nikt jeszcze nie zdobył punktów.</li>
            </ol>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold text-ink-soft">Ranking globalny</h2>
            <ol class="flex flex-col gap-2">
                <li
                    v-for="(entry, i) in props.global"
                    :key="entry.nick"
                    class="flex items-center justify-between rounded-app border border-line bg-card px-4 py-2.5"
                >
                    <span><span class="mr-2 text-ink-soft">{{ i + 1 }}.</span>{{ entry.nick }}</span>
                    <span class="font-semibold text-blue">{{ entry.points }} pkt</span>
                </li>
                <li v-if="!props.global.length" class="text-center text-sm text-ink-soft">Ranking jest jeszcze pusty.</li>
            </ol>
        </section>
    </AppLayout>
</template>
