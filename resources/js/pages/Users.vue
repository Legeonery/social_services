<script setup lang="ts">
import UserFormWrapper from '@/components/UserFormWrapper.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

type UserTab = 'clients' | 'social_workers' | 'admins';

const activeTab = ref<UserTab>('clients');
const search = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = 14;
const showInfoPanel = ref(false);
const selectedUser = ref<any | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');
const sortBy = ref('');

const users = ref<any[]>([]);

onMounted(async () => {
    try {
        const response = await axios.get('/users_data');
        users.value = response.data.users.map((user: any) => {
            const fullName = `${user.last_name} ${user.first_name} ${user.middle_name || ''}`.trim();

            if (user.tab === 'clients') {
                return {
                    id: user.id,
                    fullName,
                    phone: user.phone,
                    email: user.email,
                    status: user.status,
                    type: user.type ?? '',
                    socialWorker: user.social_worker_name ?? '',
                    tab: 'clients',
                };
            } else if (user.tab === 'social_workers') {
                return {
                    id: user.id,
                    fullName,
                    phone: user.phone,
                    email: user.email,
                    status: user.status,
                    tab: 'social_workers',
                };
            } else {
                return {
                    id: user.id,
                    fullName,
                    phone: user.phone,
                    email: user.email,
                    status: user.status,
                    tab: 'admins',
                };
            }
        });
    } catch (error) {
        console.error('Ошибка загрузки пользователей:', error);
    }
});

console.log(users);

const showForm = ref(false);
const isEdit = ref(false);
const selectedAdmin = ref<any | null>(null);
const selectedSocialWorker = ref<any | null>(null);
const selectedClient = ref<any | null>(null);

// Открытие формы для добавления
const openAddForm = () => {
    selectedAdmin.value = null;
    selectedSocialWorker.value = null;
    selectedClient.value = null;
    isEdit.value = false;
    showForm.value = true;
};

// Редактирование администратора
const editAdmin = (admin: any) => {
    selectedAdmin.value = admin;
    isEdit.value = true;
    showForm.value = true;
};

// Редактирование соц. работника
const editSocialWorker = (worker: any) => {
    selectedSocialWorker.value = worker;
    isEdit.value = true;
    showForm.value = true;
};
// Редактирование клиента
const editClient = (client: any) => {
    selectedClient.value = client;
    isEdit.value = true;
    showForm.value = true;
};
// Закрытие формы
const closeForm = () => {
    showForm.value = false;
    selectedAdmin.value = null;
    selectedSocialWorker.value = null;
};

const handleUserSubmit = (formData: any) => {
    const entry = {
        ...formData,
        fullName: `${formData.lastName} ${formData.firstName} ${formData.middleName || ''}`.trim(),
        tab: activeTab.value,
        status: isEdit.value ? formData.status : 'Активный',
    };

    let selectedRef;
    if (activeTab.value === 'clients') selectedRef = selectedClient;
    else if (activeTab.value === 'social_workers') selectedRef = selectedSocialWorker;
    else selectedRef = selectedAdmin;

    if (isEdit.value && selectedRef.value) {
        const index = users.value.findIndex((u) => u.id === selectedRef.value.id);
        if (index !== -1) users.value[index] = { ...entry, id: selectedRef.value.id };
    } else {
        const newId = users.value.length ? Math.max(...users.value.map((u) => u.id)) + 1 : 1;
        users.value.push({ ...entry, id: newId });
    }

    closeForm();
};
// Удаление пользователя
const deleteUser = (id: number) => {
    users.value = users.value.filter((u) => u.id !== id);
};

// Открытие панели информации
const openInfoPanel = (user: any) => {
    selectedUser.value = user;
    showInfoPanel.value = true;
};

// Закрытие инфо-панели
const closeInfoPanel = () => {
    selectedUser.value = null;
    showInfoPanel.value = false;
};

// Фильтрация и сортировка
const filteredUsers = computed(() => {
    let result = users.value
        .filter((u) => u.tab === activeTab.value)
        .filter((u) => u.fullName?.toLowerCase().includes(search.value.toLowerCase()))
        .filter((u) => !statusFilter.value || u.status === statusFilter.value);

    if (sortBy.value === 'status') {
        result = result.slice().sort((a, b) => {
            return sortDirection.value === 'asc' ? a.status.localeCompare(b.status, 'ru') : b.status.localeCompare(a.status, 'ru');
        });
    }

    return result;
});

// Сортировка по столбцу
function toggleSortBy(field: string) {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDirection.value = 'asc';
    }
}

// Пагинация
const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredUsers.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage));

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="[{ title: 'Пользователи', href: '/users' }]">
        <div class="space-y-4 p-4">
            <!-- Верхняя панель -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <input v-model="search" class="rounded border p-2 dark:bg-gray-800 dark:text-white" placeholder="Поиск по ФИО..." />
                <button v-if="activeTab === 'clients'" @click="openAddForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Добавить клиента
                </button>
                <button v-if="activeTab === 'social_workers'" @click="openAddForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Добавить соц. работника
                </button>
                <button v-if="activeTab === 'admins'" @click="openAddForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Добавить администратора
                </button>
            </div>

            <!-- Вкладки -->
            <div class="flex gap-2">
                <button
                    @click="
                        activeTab = 'clients';
                        currentPage = 1;
                    "
                    :class="[
                        activeTab === 'clients' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white',
                        'rounded px-4 py-1',
                    ]"
                >
                    Клиенты
                </button>
                <button
                    @click="
                        activeTab = 'social_workers';
                        currentPage = 1;
                    "
                    :class="[
                        activeTab === 'social_workers' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white',
                        'rounded px-4 py-1',
                    ]"
                >
                    Соц. работники
                </button>
                <button
                    @click="
                        activeTab = 'admins';
                        currentPage = 1;
                    "
                    :class="[activeTab === 'admins' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white', 'rounded px-4 py-1']"
                >
                    Администраторы
                </button>
            </div>

            <!-- Таблица -->
            <div class="overflow-x-auto rounded border dark:border-gray-700">
                <!-- Таблица клиентов -->
                <table v-if="activeTab === 'clients'" class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="p-2 text-left">ФИО</th>
                            <th class="p-2 text-left">Доп. инфо</th>
                            <th class="p-2 text-left">Телефон</th>
                            <th class="p-2 text-left">E-mail</th>
                            <th class="p-2 text-left">Соц. работник</th>
                            <th class="p-2 text-left">Тип клиента</th>
                            <th class="cursor-pointer p-2 text-left select-none" @click="toggleSortBy('status')">
                                Статус
                                <span v-if="sortBy === 'status'">
                                    {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                </span>
                            </th>
                            <th class="p-2 text-center">Управление</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in paginatedUsers"
                            :key="user.id"
                            class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            <td class="p-2">{{ user.fullName }}</td>
                            <td class="cursor-pointer p-2" @click="openInfoPanel(user)">
                                <span title="Открыть подробности">👤</span>
                            </td>
                            <td class="p-2">{{ user.phone }}</td>
                            <td class="p-2">{{ user.email }}</td>
                            <td class="p-2">{{ user.socialWorker }}</td>
                            <td class="p-2">{{ user.type }}</td>
                            <td class="p-2">
                                <span :class="user.status === 'Активный' ? 'text-green-600' : 'text-gray-400'">{{ user.status }}</span>
                            </td>
                            <td class="p-2 text-center">
                                <div class="flex justify-center gap-2">
                                    <button title="Редактировать" @click="editClient(user)" class="text-blue-500 hover:scale-110">✏️</button>
                                    <button title="Удалить" @click="deleteUser(user.id)" class="text-red-500 hover:scale-110">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedUsers.length === 0">
                            <td colspan="8" class="p-4 text-center text-gray-400">Нет данных для отображения</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Таблица соц. работников -->
                <table v-else-if="activeTab === 'social_workers'" class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="p-2 text-left">ФИО</th>
                            <th class="p-2 text-left">Клиенты</th>
                            <th class="p-2 text-left">Телефон</th>
                            <th class="p-2 text-left">Email</th>
                            <th class="cursor-pointer p-2 text-left select-none" @click="toggleSortBy('status')">
                                Статус
                                <span v-if="sortBy === 'status'">
                                    {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                </span>
                            </th>
                            <th class="p-2 text-left">Управление</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in paginatedUsers"
                            :key="user.id"
                            class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            <td class="p-2">{{ user.fullName }}</td>
                            <td class="cursor-pointer p-2" @click="openInfoPanel(user)">🧑‍⚕️</td>
                            <td class="p-2">{{ user.phone }}</td>
                            <td class="p-2">{{ user.email }}</td>
                            <td class="p-2">
                                <span
                                    :class="{
                                        'font-medium text-green-600': user.status === 'Активный',
                                        'font-medium text-yellow-500': user.status === 'В отпуске',
                                        'font-medium text-red-500': user.status === 'Уволенный',
                                        'font-medium text-blue-500': user.status === 'На больничном',
                                        'text-gray-400': !['Активный', 'Уволенный', 'В отпуске', 'На больничном'].includes(user.status),
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="p-2">
                                <div class="flex justify-center gap-2">
                                    <button title="Редактировать" @click="editSocialWorker(user)">✏️</button>
                                    <button title="Удалить" @click="deleteUser(user.id)" class="text-red-500 hover:scale-110">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedUsers.length === 0">
                            <td colspan="6" class="p-4 text-center text-gray-400">Нет данных</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Таблица админов -->
                <table v-else class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="p-2 text-left">ФИО</th>
                            <th class="p-2 text-left">Телефон</th>
                            <th class="p-2 text-left">Email</th>
                            <th class="cursor-pointer p-2 text-left select-none" @click="toggleSortBy('status')">
                                Статус
                                <span v-if="sortBy === 'status'">
                                    {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                </span>
                            </th>
                            <th class="p-2 text-center">Управление</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in paginatedUsers"
                            :key="user.id"
                            class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            <td class="p-2">{{ user.fullName }}</td>
                            <td class="p-2">{{ user.phone }}</td>
                            <td class="p-2">{{ user.email }}</td>
                            <td class="p-2">
                                <span
                                    :class="{
                                        'font-medium text-green-600': user.status === 'Активный',
                                        'font-medium text-red-500': user.status === 'Уволенный',
                                        'text-gray-400': !['Активный', 'Уволенный'].includes(user.status),
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="p-2 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="editAdmin(user)" title="Редактировать" class="text-blue-500 hover:scale-110">✏️</button>
                                    <button @click="deleteUser(user.id)" title="Удалить" class="text-red-500 hover:scale-110">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedUsers.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-400">Нет данных</td>
                        </tr>
                    </tbody>
                </table>
                <UserFormWrapper
                    v-if="showForm"
                    :active-tab="activeTab"
                    :model-value="activeTab === 'admins' ? selectedAdmin : activeTab === 'social_workers' ? selectedSocialWorker : selectedClient"
                    :is-edit="isEdit"
                    @submit="handleUserSubmit"
                    @cancel="closeForm"
                />
            </div>

            <!-- Пагинация -->
            <div class="flex justify-start pt-4">
                <div class="inline-flex items-center gap-1">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded border px-3 py-1 disabled:opacity-50 dark:border-gray-600"
                    >
                        ‹
                    </button>
                    <button
                        @click="goToPage(1)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === 1 ? 'bg-blue-600 text-white' : '']"
                    >
                        1
                    </button>
                    <span v-if="currentPage > 3">...</span>
                    <button
                        v-for="p in [currentPage - 1, currentPage, currentPage + 1]"
                        v-if="p > 1 && p < totalPages"
                        :key="p"
                        @click="goToPage(p)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === p ? 'bg-blue-600 text-white' : '']"
                    >
                        {{ p }}
                    </button>
                    <span v-if="currentPage < totalPages - 2">...</span>
                    <button
                        v-if="totalPages > 1"
                        @click="goToPage(totalPages)"
                        :class="['rounded border px-3 py-1 dark:border-gray-600', currentPage === totalPages ? 'bg-blue-600 text-white' : '']"
                    >
                        {{ totalPages }}
                    </button>
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded border px-3 py-1 disabled:opacity-50 dark:border-gray-600"
                    >
                        ›
                    </button>
                </div>
            </div>

            <!-- Боковая панель с доп. информацией -->
            <div v-if="showInfoPanel" class="fixed top-0 right-0 z-50 h-full w-1/3 overflow-y-auto bg-white p-4 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold">Информация о пользователе</h2>
                    <button @click="closeInfoPanel" class="text-gray-500 hover:text-black dark:hover:text-white">✖</button>
                </div>
                <p><strong>ФИО:</strong> {{ selectedUser?.fullName }}</p>
                <p><strong>Телефон:</strong> {{ selectedUser?.phone }}</p>
                <p><strong>Email:</strong> {{ selectedUser?.email }}</p>
                <p><strong>Статус:</strong> {{ selectedUser?.status }}</p>

                <p v-if="activeTab === 'clients'">
                    <strong>Соц. работник:</strong> {{ selectedUser?.socialWorker }}<br />
                    <strong>Тип:</strong> {{ selectedUser?.type }}
                </p>

                <div v-else-if="activeTab === 'social_workers'">
                    <strong>Клиенты:</strong>
                    <ul class="mt-2 list-disc pl-4">
                        <li v-for="c in selectedUser?.socialWorkerClients" :key="c">{{ c }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
