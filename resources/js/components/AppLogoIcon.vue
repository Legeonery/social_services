<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        :src="'/images/logo.png'"
        :class="className"
        v-bind="$attrs"
        alt="Логотип Отдела социального обслуживания населения"
        style="width: 120px; height: auto; border-radius: 10px"
    />
</template>
