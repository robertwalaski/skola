<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout back="/">
        <template #header>
            <h1 class="text-xl font-semibold">Polityka prywatności</h1>
        </template>

        <div class="flex flex-col gap-4 rounded-app border border-line bg-card p-[18px] text-[15px] leading-relaxed">
            <p>
                Konto zakłada się przez <b>nick i PIN</b> - nie prosimy o imię, nazwisko, datę urodzenia ani żaden inny
                dokument tożsamości. Dla dzieci to jedyny sposób założenia konta.
            </p>
            <p>
                Osoby dorosłe mogą <b>opcjonalnie</b> podać e-mail i hasło, żeby móc odzyskać konto samodzielnie. E-mail
                nigdy nie jest wymagany i nie jest widoczny dla innych uczniów.
            </p>
            <p>
                Przechowujemy: nick, hasz PINu (nigdy sam PIN), wyniki ćwiczeń (do rankingu) i - jeśli podano - e-mail
                oraz hasz hasła. Nick i wynik mogą być widoczne w rankingu; nic więcej nie jest publiczne.
            </p>
            <p>Dane nie są sprzedawane ani przekazywane firmom trzecim. Konto i jego dane można usunąć na prośbę.</p>
        </div>
    </AppLayout>
</template>
