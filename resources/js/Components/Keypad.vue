<script setup>
defineEmits(['press']);

const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'del', '0', 'go'];
</script>

<template>
    <div class="keypad mx-auto mt-4 grid max-w-[300px] grid-cols-3 gap-2.5">
        <button
            v-for="k in keys"
            :key="k"
            type="button"
            class="key rounded-xl border font-semibold"
            :class="{
                'border-line bg-card text-ink active:bg-blue-soft': k !== 'del' && k !== 'go',
                'del border-red-soft bg-red-soft text-red active:bg-red active:text-white': k === 'del',
                'go border-blue bg-blue text-white hover:bg-[#1E508A] active:bg-[#1E508A]': k === 'go',
            }"
            :aria-label="k === 'del' ? 'Skasuj cyfrę' : k === 'go' ? 'Sprawdź' : undefined"
            @click="$emit('press', k)"
        >
            <span v-if="k === 'del'">&#9003;</span>
            <span v-else-if="k === 'go'">OK</span>
            <span v-else>{{ k }}</span>
        </button>
    </div>
</template>

<style scoped>
/* Sizes are CSS variables (not Tailwind clamp() utilities) so a parent page can
   override them per breakpoint - custom properties cross component boundaries
   via normal CSS inheritance, Tailwind's generated classes cannot be. */
.key {
    height: var(--key-h, clamp(46px, 7.2vh, 60px));
    font-size: var(--key-font, clamp(20px, 4.6vh, 26px));
}
.key.del {
    font-size: var(--key-del-font, clamp(18px, 4.2vh, 24px));
}
.key.go {
    font-size: var(--key-go-font, clamp(16px, 3.4vh, 20px));
}
@media (max-width: 430px) {
    .keypad {
        gap: 8px;
    }
}
</style>

