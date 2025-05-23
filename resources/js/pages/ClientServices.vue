<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
</script>
<template>
    <Head title="Оказанные услуги" />

    <AppLayout :breadcrumbs="[{ title: 'Оказанные услуги', href: '/client-services' }]">
        <div class="p-6 text-lg">Оказанные услуги (для клиента)</div>
    </AppLayout>
</template>
