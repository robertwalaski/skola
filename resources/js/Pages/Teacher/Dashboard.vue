<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    isTeacher: { type: Boolean, required: true },
    groups: { type: Array, default: () => [] },
});

const page = usePage();

const groupForm = useForm({ name: '' });

function createGroup() {
    groupForm.post('/nauczyciel/klasy', { preserveScroll: true, onSuccess: () => (groupForm.name = '') });
}

function resetPin(groupId, studentId) {
    if (!confirm('Zresetować PIN tego ucznia?')) return;
    useForm({}).post(`/nauczyciel/klasy/${groupId}/uczniowie/${studentId}/reset-pin`, { preserveScroll: true });
}

function formatDate(dateString) {
    if (!dateString) return 'nigdy';
    return new Date(dateString).toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' });
}
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Panel nauczyciela</h1>
        </template>

        <p v-if="page.props.status" class="mb-4 rounded-[10px] bg-green-soft p-3 text-center text-sm text-green">
            {{ page.props.status }}
        </p>

        <section v-if="!isTeacher" class="rounded-app border border-line bg-card p-[18px] text-center">
            <p class="mb-4 text-sm text-ink-soft">Załóż klasę, żeby zobaczyć postępy swoich uczniów i wydać im kod dołączenia.</p>
            <form class="flex flex-col gap-3" @submit.prevent="createGroup">
                <input
                    v-model="groupForm.name"
                    type="text"
                    placeholder="Nazwa klasy, np. 4B"
                    class="w-full rounded-lg border-2 border-line px-3 py-2 text-center focus:border-blue focus:outline-none"
                />
                <p v-if="groupForm.errors.name" class="text-sm text-red">{{ groupForm.errors.name }}</p>
                <button
                    type="submit"
                    :disabled="groupForm.processing"
                    class="h-12 rounded-[10px] bg-blue font-semibold text-white hover:bg-[#1E508A] disabled:opacity-60"
                >
                    Zostań nauczycielem i utwórz klasę
                </button>
            </form>
        </section>

        <template v-else>
            <section v-for="group in groups" :key="group.id" class="mb-6 rounded-app border border-line bg-card p-[18px]">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ group.name }}</h2>
                    <span class="rounded-full bg-blue-soft px-3 py-1 text-sm font-semibold text-blue">Kod: {{ group.join_code }}</span>
                </div>

                <p v-if="!group.students.length" class="text-sm text-ink-soft">Nikt jeszcze nie dołączył.</p>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left text-ink-soft">
                            <th class="py-1.5">Uczeń</th>
                            <th class="py-1.5">Punkty</th>
                            <th class="py-1.5">Ostatnio</th>
                            <th class="py-1.5"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in group.students" :key="s.id" class="border-b border-line last:border-0">
                            <td class="py-2 font-medium">{{ s.nick }}</td>
                            <td class="py-2">{{ s.points }}</td>
                            <td class="py-2 text-ink-soft">{{ formatDate(s.last_practiced) }}</td>
                            <td class="py-2 text-right">
                                <button type="button" class="text-xs text-red underline" @click="resetPin(group.id, s.id)">Reset PIN</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <form class="rounded-app border border-dashed border-line bg-card/50 p-[18px]" @submit.prevent="createGroup">
                <p class="mb-2 text-sm text-ink-soft">Dodaj kolejną klasę</p>
                <div class="flex gap-2">
                    <input
                        v-model="groupForm.name"
                        type="text"
                        placeholder="Nazwa klasy"
                        class="flex-1 rounded-lg border-2 border-line px-3 py-2 focus:border-blue focus:outline-none"
                    />
                    <button type="submit" :disabled="groupForm.processing" class="rounded-[10px] bg-blue px-4 font-semibold text-white hover:bg-[#1E508A]">
                        Utwórz
                    </button>
                </div>
            </form>
        </template>
    </AppLayout>
</template>
