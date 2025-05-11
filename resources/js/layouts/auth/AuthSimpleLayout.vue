<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-gradient-to-b from-[#fdfdfc] to-[#f5f5f0] p-6 md:p-10 dark:from-[#0d0d0d] dark:to-[#1a1a1a]"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 dark:bg-[#1c1c1c] dark:ring-gray-700">
            <div class="flex flex-col items-center gap-6">
                <!-- Logo -->
                <Link :href="route('home')" class="flex flex-col items-center gap-2">
                    <AppLogoIcon class="size-9 text-[#1b1b18] dark:text-white" />
                    <span class="sr-only">{{ title }}</span>
                </Link>

                <!-- Title & Description -->
                <div class="text-center">
                    <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-white">{{ title }}</h1>
                    <p v-if="description" class="text-muted-foreground mt-1 text-sm">{{ description }}</p>
                </div>

                <!-- Form Slot -->
                <slot />
            </div>
        </div>
    </div>
</template>
