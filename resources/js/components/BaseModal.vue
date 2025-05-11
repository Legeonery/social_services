<script setup lang="ts">
defineProps<{
    show: boolean;
    title: string;
}>();

const emit = defineEmits(['close']);
</script>
<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="emit('close')">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
                <button @click="emit('close')" class="text-gray-500 hover:text-red-500">✖</button>
            </div>
            <slot />
        </div>
    </div>
</template>
