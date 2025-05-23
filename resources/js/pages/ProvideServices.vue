<!-- resources/js/Pages/ProvideServices.vue -->
<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
</script>
<template>
    <Head title="Добавить услуги" />

    <AppLayout :breadcrumbs="[{ title: 'Добавить услуги', href: '/provide-services' }]">
        <div class="p-6 text-lg">Добавить услуги (для соц. работника)</div>
    </AppLayout>
</template>