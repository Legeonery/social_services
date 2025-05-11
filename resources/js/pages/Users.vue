<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

// Пример данных (замени на API-запрос или props)
const users = ref([
    // 10 клиентов
    {
        id: 1,
        fullName: 'Иван Иванов',
        phone: '+7 123 456-78-90',
        email: 'ivanov@example.com',
        status: 'Активный',
        type: 'бюджетный',
        socialWorker: 'Петров П.П.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 2,
        fullName: 'Сергей Смирнов',
        phone: '+7 987 654-32-10',
        email: 'smirnov@example.com',
        status: 'Неактивный',
        type: 'платный',
        socialWorker: 'Петров П.П.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 3,
        fullName: 'Мария Кузнецова',
        phone: '+7 900 111-22-33',
        email: 'maria@example.com',
        status: 'Активный',
        type: 'бюджетный',
        socialWorker: 'Сидорова И.И.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 4,
        fullName: 'Анна Белова',
        phone: '+7 901 222-33-44',
        email: 'anna@example.com',
        status: 'Активный',
        type: 'платный',
        socialWorker: 'Сидорова И.И.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 5,
        fullName: 'Дмитрий Орлов',
        phone: '+7 902 333-44-55',
        email: 'orlov@example.com',
        status: 'Неактивный',
        type: 'бюджетный',
        socialWorker: 'Петров П.П.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 6,
        fullName: 'Наталья Егорова',
        phone: '+7 903 444-55-66',
        email: 'natalya@example.com',
        status: 'Активный',
        type: 'платный',
        socialWorker: 'Сидорова И.И.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 7,
        fullName: 'Алексей Козлов',
        phone: '+7 904 555-66-77',
        email: 'kozlova@example.com',
        status: 'Неактивный',
        type: 'бюджетный',
        socialWorker: 'Петров П.П.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 8,
        fullName: 'Оксана Лебедева',
        phone: '+7 905 666-77-88',
        email: 'oksana@example.com',
        status: 'Активный',
        type: 'платный',
        socialWorker: 'Сидорова И.И.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 9,
        fullName: 'Павел Морозов',
        phone: '+7 906 777-88-99',
        email: 'pavel@example.com',
        status: 'Активный',
        type: 'бюджетный',
        socialWorker: 'Петров П.П.',
        avatar: '👤',
        tab: 'clients',
    },
    {
        id: 10,
        fullName: 'Татьяна Васильева',
        phone: '+7 907 888-99-00',
        email: 'tanya@example.com',
        status: 'Неактивный',
        type: 'платный',
        socialWorker: 'Сидорова И.И.',
        avatar: '👤',
        tab: 'clients',
    },

    // 10 соц. работников
    {
        id: 11,
        fullName: 'Ольга Сидорова',
        phone: '+7 999 111-22-33',
        email: 'olga@example.com',
        status: 'В отпуске',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Иван Иванов', 'Сергей Смирнов'],
    },
    {
        id: 12,
        fullName: 'Инна Захарова',
        phone: '+7 999 000-11-22',
        email: 'inna@example.com',
        status: 'Активный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Мария Кузнецова'],
    },
    {
        id: 13,
        fullName: 'Юлия Новикова',
        phone: '+7 999 123-45-67',
        email: 'yulia@example.com',
        status: 'На больничном',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Анна Белова'],
    },
    {
        id: 14,
        fullName: 'Игорь Петров',
        phone: '+7 999 222-33-44',
        email: 'igor@example.com',
        status: 'Уволенный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Дмитрий Орлов'],
    },
    {
        id: 15,
        fullName: 'Александр Миронов',
        phone: '+7 999 333-44-55',
        email: 'mironov@example.com',
        status: 'Активный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Оксана Лебедева'],
    },
    {
        id: 16,
        fullName: 'Тамара Киселева',
        phone: '+7 999 444-55-66',
        email: 'tamara@example.com',
        status: 'Активный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Павел Морозов'],
    },
    {
        id: 17,
        fullName: 'Владимир Тарасов',
        phone: '+7 999 555-66-77',
        email: 'vlad@example.com',
        status: 'На больничном',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: ['Татьяна Васильева'],
    },
    {
        id: 18,
        fullName: 'Светлана Федорова',
        phone: '+7 999 666-77-88',
        email: 'sveta@example.com',
        status: 'Уволенный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: [],
    },
    {
        id: 19,
        fullName: 'Роман Алексеев',
        phone: '+7 999 777-88-99',
        email: 'roman@example.com',
        status: 'Активный',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: [],
    },
    {
        id: 20,
        fullName: 'Елена Громова',
        phone: '+7 999 888-99-00',
        email: 'elena@example.com',
        status: 'В отпуске',
        avatar: '🧑‍⚕️',
        tab: 'social_workers',
        socialWorkerClients: [],
    },

    // 10 администраторов
    {
        id: 21,
        fullName: 'Админ Системы',
        phone: '+7 777 123-45-67',
        email: 'admin@example.com',
        status: 'Активный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 22,
        fullName: 'Михаил Логинов',
        phone: '+7 777 000-11-22',
        email: 'mikhail@example.com',
        status: 'Уволенный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 23,
        fullName: 'Светлана Иванова',
        phone: '+7 777 111-22-33',
        email: 'svet@example.com',
        status: 'Активный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 24,
        fullName: 'Андрей Колесников',
        phone: '+7 777 222-33-44',
        email: 'andrey@example.com',
        status: 'Уволенный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 25,
        fullName: 'Тимур Галимов',
        phone: '+7 777 333-44-55',
        email: 'timur@example.com',
        status: 'Активный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 26,
        fullName: 'Ирина Шестакова',
        phone: '+7 777 444-55-66',
        email: 'irina@example.com',
        status: 'Уволенный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 27,
        fullName: 'Рустам Власов',
        phone: '+7 777 555-66-77',
        email: 'rustam@example.com',
        status: 'Активный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 28,
        fullName: 'Марина Ларина',
        phone: '+7 777 666-77-88',
        email: 'marina@example.com',
        status: 'Уволенный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 29,
        fullName: 'Георгий Соловьев',
        phone: '+7 777 777-88-99',
        email: 'george@example.com',
        status: 'Активный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
    {
        id: 30,
        fullName: 'Валентина Соколова',
        phone: '+7 777 888-99-00',
        email: 'valentina@example.com',
        status: 'Уволенный',
        socialWorker: '-',
        avatar: '🛠️',
        tab: 'admins',
    },
]);

const filteredUsers = computed(() => {
    let result = users.value
        .filter((u) => u.tab === activeTab.value)
        .filter((u) => u.fullName.toLowerCase().includes(search.value.toLowerCase()))
        .filter((u) => !statusFilter.value || u.status === statusFilter.value);

    if (sortBy.value === 'status') {
        result = result.slice().sort((a, b) => {
            if (sortDirection.value === 'asc') {
                return a.status.localeCompare(b.status, 'ru');
            } else {
                return b.status.localeCompare(a.status, 'ru');
            }
        });
    }

    return result;
});

function toggleSortBy(field: string) {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDirection.value = 'asc';
    }
}

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

function openInfoPanel(user: any) {
    selectedUser.value = user;
    showInfoPanel.value = true;
}

function closeInfoPanel() {
    selectedUser.value = null;
    showInfoPanel.value = false;
}
</script>

<template>
    <Head title="Пользователи" />
    <AppLayout :breadcrumbs="[{ title: 'Пользователи', href: '/users' }]">
        <div class="space-y-4 p-4">
            <!-- Верхняя панель -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <input v-model="search" class="rounded border p-2 dark:bg-gray-800 dark:text-white" placeholder="Поиск по ФИО..." />
                <button class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">+ Добавить пользователя</button>
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
                                <span title="Открыть подробности">{{ user.avatar }}</span>
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
                                    <button title="Редактировать" class="text-blue-500 hover:scale-110">✏️</button>
                                    <button title="Удалить" class="text-red-500 hover:scale-110">🗑️</button>
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
                            <td class="cursor-pointer p-2" @click="openInfoPanel(user)">
                                {{ user.avatar }}
                            </td>
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
                                    <button title="Редактировать">✏️</button>
                                    <button title="Удалить">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedUsers.length === 0">
                            <td colspan="6" class="p-4 text-center text-gray-400">Нет данных</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Таблица админов -->
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
                                    <button title="Редактировать" class="text-blue-500 hover:scale-110">✏️</button>
                                    <button title="Удалить" class="text-red-500 hover:scale-110">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedUsers.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-400">Нет данных</td>
                        </tr>
                    </tbody>
                </table>
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
