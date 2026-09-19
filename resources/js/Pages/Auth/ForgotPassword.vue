<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ status: { type: String, default: null } });

const form = useForm({ email: '' });

function submit() {
    form.post('/zapomnialem-hasla', { preserveScroll: true });
}
</script>

<template>
    <AppLayout back="/logowanie">
        <template #header>
            <h1 class="text-xl font-semibold">Zapomniałem hasła</h1>
            <p class="text-sm text-ink-soft">Działa tylko dla kont z podanym e-mailem.</p>
        </template>

        <form class="flex flex-col gap-4 rounded-app border border-line bg-card p-[18px]" @submit.prevent="submit">
            <p v-if="status" class="rounded-[10px] bg-green-soft p-3 text-sm text-green">{{ status }}</p>

            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="email">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
            >
                Wyślij link do resetu
            </button>
        </form>
    </AppLayout>
</template>
