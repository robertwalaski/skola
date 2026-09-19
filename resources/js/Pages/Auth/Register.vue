<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

function guestPoints() {
    try {
        const raw = localStorage.getItem('tabliczka.v1');
        if (!raw) return 0;
        const mem = JSON.parse(raw);
        return Math.max(0, Number(mem.best) || 0);
    } catch {
        return 0;
    }
}

const form = useForm({
    nick: '',
    pin: '',
    pin_confirmation: '',
    is_adult: false,
    email: '',
    password: '',
    password_confirmation: '',
    guest_points: guestPoints(),
});

function submit() {
    form.post('/rejestracja', { preserveScroll: true });
}
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Załóż konto</h1>
            <p class="text-sm text-ink-soft">Nick i PIN wystarczą, żeby zbierać punkty.</p>
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
                <p v-if="form.errors.nick" class="mt-1 text-sm text-red">{{ form.errors.nick }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-sm text-ink-soft" for="pin">PIN (4-6 cyfr)</label>
                    <input
                        id="pin"
                        v-model="form.pin"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="new-password"
                        class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-ink-soft" for="pin_confirmation">Powtórz PIN</label>
                    <input
                        id="pin_confirmation"
                        v-model="form.pin_confirmation"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="new-password"
                        class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                    />
                </div>
            </div>
            <p v-if="form.errors.pin" class="text-sm text-red">{{ form.errors.pin }}</p>

            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.is_adult" type="checkbox" class="h-4 w-4" />
                Jestem dorosły/a (opcjonalnie e-mail + hasło do odzyskiwania konta)
            </label>

            <template v-if="form.is_adult">
                <div>
                    <label class="mb-1 block text-sm text-ink-soft" for="email">E-mail (opcjonalnie)</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red">{{ form.errors.email }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm text-ink-soft" for="password">Hasło</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            class="w-full rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                        />
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
                </div>
                <p v-if="form.errors.password" class="text-sm text-red">{{ form.errors.password }}</p>
            </template>

            <button
                type="submit"
                :disabled="form.processing"
                class="h-[52px] w-full rounded-[10px] bg-blue text-lg font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
            >
                Zarejestruj się
            </button>

            <p class="text-center text-sm text-ink-soft">
                Masz już konto? <Link href="/logowanie" class="text-blue">Zaloguj się</Link>
            </p>
            <p class="text-center text-xs text-ink-soft">
                <Link href="/prywatnosc" class="underline">Polityka prywatności</Link>
            </p>
        </form>
    </AppLayout>
</template>
