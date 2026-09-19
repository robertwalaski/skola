<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    currentGroup: { type: Object, default: null },
});

const page = usePage();

const form = useForm({ join_code: '' });

function submit() {
    form.post('/dolacz', { preserveScroll: true });
}
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Dołącz do klasy</h1>
            <p class="text-sm text-ink-soft">Kod dostajesz od nauczyciela.</p>
        </template>

        <p v-if="page.props.status" class="mb-4 rounded-[10px] bg-green-soft p-3 text-center text-sm text-green">
            {{ page.props.status }}
        </p>

        <p v-if="currentGroup?.name" class="mb-4 text-center text-sm text-ink-soft">
            Jesteś obecnie w klasie <b>{{ currentGroup.name }}</b>. Dołączenie do innej zastąpi tę.
        </p>

        <form class="flex flex-col gap-4 rounded-app border border-line bg-card p-[18px]" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="join_code">Kod klasy</label>
                <input
                    id="join_code"
                    v-model="form.join_code"
                    type="text"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 text-center text-lg font-semibold uppercase tracking-widest focus:border-blue focus:outline-none"
                    maxlength="8"
                />
                <p v-if="form.errors.join_code" class="mt-1 text-sm text-red">{{ form.errors.join_code }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
            >
                Dołącz
            </button>
        </form>
    </AppLayout>
</template>
