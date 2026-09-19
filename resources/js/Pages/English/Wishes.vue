<script setup>
import GrammarLesson from '../../Components/GrammarLesson.vue';
import content from '../../../content/english/wishes.json';
</script>

<template>
    <GrammarLesson :content="content" section="wishes" />
</template>
