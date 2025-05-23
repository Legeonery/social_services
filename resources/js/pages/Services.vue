<script setup lang="ts">
import AddServiceForm from '@/components/AddServiceForm.vue';
import BaseModal from '@/components/BaseModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

type ServiceType = 'main' | 'additional';
type Service = {
    id: number;
    name: string;
    code: string;
    description: string;
    price: number;
    active: boolean;
    type: ServiceType;
};

const services = ref<Service[]>([]);
const search = ref('');
const sortKey = ref<'name' | 'code'>('name');
const sortAsc = ref(true);
const showModal = ref(false);
const currentPage = ref(1);
const itemsPerPage = 14;
const activeTab = ref<ServiceType>('main');
const editingService = ref<Service | null>(null);

onMounted(fetchServices);

async function fetchServices() {
    try {
        const response = await axios.get('/services-api');
        services.value = response.data;
    } catch (e) {
        console.error('Ошибка загрузки услуг:', e);
    }
}

const filteredServices = computed(() => {
    const filtered = services.value.filter((s) => s.type === activeTab.value);
    return filtered
        .filter((s) => s.name.toLowerCase().includes(search.value.toLowerCase()) || s.code.toLowerCase().includes(search.value.toLowerCase()))
        .sort((a, b) => {
            const valA = a[sortKey.value];
            const valB = b[sortKey.value];
            return sortAsc.value ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });
});

const paginatedServices = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredServices.value.slice(start, start + itemsPerPage);
});
const totalPages = computed(() => Math.ceil(filteredServices.value.length / itemsPerPage));

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) currentPage.value = page;
}

function getMiddlePages() {
    if (totalPages.value <= 5) return Array.from({ length: totalPages.value - 2 }, (_, i) => i + 2);
    let start = Math.max(2, currentPage.value - 1);
    let end = Math.min(totalPages.value - 1, start + 2);
    if (end - start < 2) start = Math.max(2, end - 2);
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
}

async function deleteService(id: number) {
    const service = services.value.find((s) => s.id === id);
    if (!service) return;
    if (!confirm(`Удалить услугу "${service.name}"?`)) return;
    try {
        await axios.delete(`/services-api/${id}`);
        services.value = services.value.filter((s) => s.id !== id);
    } catch (e) {
        alert('Ошибка при удалении услуги');
        console.error(e);
    }
}

function editService(service: Service) {
    editingService.value = { ...service };
    showModal.value = true;
}

function handleFormSubmit(service: Service) {
    const index = services.value.findIndex((s) => s.id === service.id);
    if (index !== -1) {
        services.value[index] = service; // редактирование
    } else {
        services.value.push(service); // добавление
    }
    showModal.value = false;
    editingService.value = null;
}
</script>

<template>
    <Head title="Услуги" />

    <AppLayout :breadcrumbs="[{ title: 'Услуги', href: '/services' }]">
        <div class="flex flex-col gap-4 p-4">
            <!-- Поиск и добавление -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Поиск..."
                    class="w-full rounded border p-2 md:w-1/2 dark:bg-gray-800 dark:text-white"
                />
                <button @click="showModal = true" class="self-end rounded bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700">
                    + Добавить услугу
                </button>
            </div>

            <!-- Кнопки выбора типа -->
            <div class="flex gap-2">
                <button
                    :class="['rounded px-4 py-1', activeTab === 'main' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
                    @click="
                        activeTab = 'main';
                        currentPage = 1;
                    "
                >
                    Основные
                </button>
                <button
                    :class="[
                        'rounded px-4 py-1',
                        activeTab === 'additional' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white',
                    ]"
                    @click="
                        activeTab = 'additional';
                        currentPage = 1;
                    "
                >
                    Дополнительные
                </button>
            </div>

            <!-- Таблица -->
            <div class="overflow-auto rounded-lg border dark:border-gray-700">
                <table class="min-w-full table-auto text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th
                                @click="
                                    sortKey = 'name';
                                    sortAsc = !sortAsc;
                                "
                                class="cursor-pointer p-2 text-left"
                            >
                                Наименование
                            </th>
                            <th
                                @click="
                                    sortKey = 'code';
                                    sortAsc = !sortAsc;
                                "
                                class="cursor-pointer p-2 text-left"
                            >
                                Шифр
                            </th>
                            <th class="p-2 text-left">Описание</th>
                            <th class="p-2 text-left">Цена</th>
                            <th class="p-2 text-left">Статус</th>
                            <th class="p-2 text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="service in paginatedServices"
                            :key="service.id"
                            class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            <td class="p-2">{{ service.name }}</td>
                            <td class="p-2">{{ service.code }}</td>
                            <td class="p-2">{{ service.description }}</td>
                            <td class="p-2">{{ service.price }} ₽</td>
                            <td class="p-2">
                                <span :class="service.active ? 'text-green-600' : 'text-gray-400'">
                                    {{ service.active ? 'Активна' : 'Неактивна' }}
                                </span>
                            </td>
                            <td class="p-2">
                                <div class="flex items-center justify-center gap-3">
                                    <button
                                        @click="editService(service)"
                                        class="text-blue-500 transition hover:scale-110 hover:text-blue-700"
                                        title="Редактировать"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        @click="deleteService(service.id)"
                                        class="text-red-500 transition hover:scale-110 hover:text-red-700"
                                        title="Удалить"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedServices.length === 0">
                            <td colspan="6" class="p-4 text-center text-gray-400">Нет услуг для отображения</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="flex justify-start pt-4">
                <div class="inline-flex items-center gap-1">
                    <!-- Стрелка назад -->
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded border px-3 py-1 disabled:opacity-50 dark:border-gray-600"
                    >
                        ‹
                    </button>

                    <!-- Первая страница -->
                    <button
                        @click="goToPage(1)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === 1 ? 'bg-blue-600 text-white' : '']"
                    >
                        1
                    </button>

                    <!-- Многоточие -->
                    <span v-if="getMiddlePages()[0] > 2">...</span>

                    <!-- Средние страницы -->
                    <button
                        v-for="page in getMiddlePages()"
                        :key="page"
                        @click="goToPage(page)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === page ? 'bg-blue-600 text-white' : '']"
                    >
                        {{ page }}
                    </button>

                    <!-- Многоточие -->
                    <span v-if="getMiddlePages().slice(-1)[0] < totalPages - 1">...</span>

                    <!-- Последняя страница -->
                    <button
                        v-if="totalPages > 1"
                        @click="goToPage(totalPages)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === totalPages ? 'bg-blue-600 text-white' : '']"
                    >
                        {{ totalPages }}
                    </button>

                    <!-- Стрелка вперёд -->
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded border px-3 py-1 disabled:opacity-50 dark:border-gray-600"
                    >
                        ›
                    </button>
                </div>
            </div>

            <!-- Модальное окно -->
            <BaseModal
                :show="showModal"
                :title="editingService ? 'Редактирование услуги' : 'Добавление услуги'"
                @close="
                    showModal = false;
                    editingService = null;
                "
            >
                <AddServiceForm
                    :initial="editingService"
                    :isEdit="Boolean(editingService)"
                    @submit="handleFormSubmit"
                    @cancel="
                        showModal = false;
                        editingService = null;
                    "
                />
            </BaseModal>
        </div>
    </AppLayout>
</template>
