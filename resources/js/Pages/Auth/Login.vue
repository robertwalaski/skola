<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const form = useForm({
    nick: '',
    pin: '',
});

function submit() {
    form.post('/logowanie', { preserveScroll: true, onFinish: () => (form.pin = '') });
}
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Zaloguj się</h1>
        </template>

        <form class="flex flex-col gap-4 rounded-app border border-line bg-card p-[18px]" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="nick">Nick</label>
                <input
                    id="nick"
                    v-model="form.nick"
                    type="text"
                    autocomplete="username"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm text-ink-soft" for="pin">PIN</label>
                <input
                    id="pin"
                    v-model="form.pin"
                    type="password"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="current-password"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                />
            </div>
            <p v-if="form.errors.nick" class="text-sm text-red">{{ form.errors.nick }}</p>

            <button
                type="submit"
                :disabled="form.processing"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
            >
                Zaloguj się
            </button>

            <p class="text-center text-sm text-ink-soft">
                Nie masz konta? <Link href="/rejestracja" class="text-blue">Zarejestruj się</Link>
            </p>
            <p class="text-center text-sm">
                <Link href="/zapomnialem-hasla" class="text-ink-soft underline">Zapomniałem hasła (tylko konto z e-mailem)</Link>
            </p>
        </form>
    </AppLayout>
</template>
