<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-hasla', { preserveScroll: true });
}
</script>

<template>
    <AppLayout back="/logowanie">
        <template #header>
            <h1 class="text-xl font-semibold">Ustaw nowe hasło</h1>
        </template>

        <form class="flex flex-col gap-4 rounded-app border border-line bg-card p-[18px]" @submit.prevent="submit">
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
            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="password">Nowe hasło</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red">{{ form.errors.password }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="password_confirmation">Powtórz hasło</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
            >
                Zapisz nowe hasło
            </button>
        </form>
    </AppLayout>
</template>
