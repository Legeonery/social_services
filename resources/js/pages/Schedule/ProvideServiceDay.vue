<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import 'dayjs/locale/ru';
import { computed, ref } from 'vue';

dayjs.locale('ru');

const props = defineProps({ date: String });

const selectedDate = ref(dayjs(props.date).startOf('day'));
const showAddForm = ref(false);

// Навигация по дням
function prevDay() {
    const prev = selectedDate.value.subtract(1, 'day').format('YYYY-MM-DD');
    router.visit(`/provide-services/${prev}`);
}

function nextDay() {
    const next = selectedDate.value.add(1, 'day').format('YYYY-MM-DD');
    router.visit(`/provide-services/${next}`);
}

// Все услуги (примеры)
const services = ref([
    {
        id: 1,
        date: dayjs().format('YYYY-MM-DD'),
        worker: { id: 1, name: 'Иванов И.И.' },
        client: { id: 101, name: 'Петров П.П.' },
        type: 'Уборка',
        code: 'UBR-01',
        notes: 'Уборка жилого помещения',
        start: '09:00',
        end: '10:00',
        status: 'Подтверждена',
    },
    {
        id: 2,
        date: dayjs().format('YYYY-MM-DD'),
        worker: { id: 2, name: 'Сидорова А.А.' },
        client: { id: 102, name: 'Иванова Н.Н.' },
        type: 'Покупка продуктов',
        code: 'PRD-02',
        notes: 'Список от клиента',
        start: '11:00',
        end: '12:00',
        status: 'Не подтверждена',
    },
    {
        id: 3,
        date: dayjs().add(1, 'day').format('YYYY-MM-DD'),
        worker: { id: 1, name: 'Иванов И.И.' },
        client: { id: 103, name: 'Смирнов В.В.' },
        type: 'Прогулка',
        code: 'WLK-03',
        notes: 'В парк',
        start: '14:00',
        end: '15:30',
        status: 'Подтверждена',
    },
]);

const filteredServices = computed(() => {
    return services.value.filter((s) => dayjs(s.date).isSame(selectedDate.value, 'day'));
});

const newService = ref({
    client: '',
    type: '',
    code: '',
    notes: '',
    start: '',
    end: '',
});

const resetForm = () => {
    newService.value = {
        client: '',
        type: '',
        code: '',
        notes: '',
        start: '',
        end: '',
    };
};

const submitForm = () => {
    const newId = services.value.length + 1;
    services.value.push({
        id: newId,
        date: selectedDate.value.format('YYYY-MM-DD'),
        worker: { id: 1, name: 'Текущий соцработник' },
        client: { id: 999, name: newService.value.client },
        type: newService.value.type,
        code: newService.value.code,
        notes: newService.value.notes,
        start: newService.value.start,
        end: newService.value.end,
        status: 'Не подтверждена',
    });

    resetForm();
    showAddForm.value = false;
};
const showNoteModal = ref(false);
const selectedNote = ref('');

function openNote(service) {
    selectedNote.value = service.notes || 'Заметка отсутствует';
    showNoteModal.value = true;
}

function closeNote() {
    showNoteModal.value = false;
}
</script>

<template>
    <Head :title="`Услуги на ${selectedDate.format('DD.MM.YYYY')}`" />

    <AppLayout :breadcrumbs="[{ title: 'Таблица услуг', href: '/provide-services' }]">
        <div class="flex flex-wrap items-center justify-between gap-4 p-4">
            <h1 class="text-xl font-bold">Услуги на {{ selectedDate.format('DD.MM.YYYY') }}</h1>

            <div class="flex items-center gap-2">
                <button @click="prevDay" class="rounded border px-3 py-1 dark:border-gray-600 dark:text-white">←</button>
                <input
                    type="date"
                    :value="selectedDate.format('YYYY-MM-DD')"
                    @input="(e) => router.visit(`/provide-services/${e.target.value}`)"
                    class="rounded border px-2 py-1 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                />
                <button @click="nextDay" class="rounded border px-3 py-1 dark:border-gray-600 dark:text-white">→</button>
            </div>

            <button class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700" @click="showAddForm = true">+ Добавить услугу</button>
        </div>

        <div class="p-4">
            <div v-if="filteredServices.length === 0" class="text-gray-500">Услуг пока нет.</div>
            <div class="overflow-x-auto rounded-xl border bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr class="text-left text-gray-700 dark:text-gray-200">
                            <th class="px-4 py-2">ФИО соц. работника</th>
                            <th class="px-4 py-2">ФИО клиента</th>
                            <th class="px-4 py-2">Услуга</th>
                            <th class="px-4 py-2">Время</th>
                            <th class="px-4 py-2">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="service in filteredServices" :key="service.id" class="transition hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ service.worker.name }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ service.client.name }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">
                                {{ service.type }} ({{ service.code }})
                                <button class="ml-2 text-blue-600 dark:text-blue-400" @click="openNote(service)">ℹ</button>
                            </td>
                            <td class="px-4 py-2 text-gray-800 dark:text-white">{{ service.start }} – {{ service.end }}</td>
                            <td
                                class="px-4 py-2 font-semibold"
                                :class="{
                                    'text-green-600 dark:text-green-400': service.status === 'Подтверждена',
                                    'text-red-600 dark:text-red-400': service.status !== 'Подтверждена',
                                }"
                            >
                                {{ service.status }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Модалка заметки -->
                <div v-if="showNoteModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold dark:text-white">Заметка к услуге</h3>
                        <p class="text-sm whitespace-pre-wrap dark:text-gray-300">{{ selectedNote }}</p>
                        <button
                            @click="closeNote"
                            class="mt-6 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                        >
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка -->
        <div v-if="showAddForm" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Новая услуга на {{ selectedDate.format('DD.MM.YYYY') }}</h3>

                <div class="space-y-3">
                    <input v-model="newService.client" type="text" placeholder="Клиент" class="w-full rounded border p-2" />
                    <input v-model="newService.type" type="text" placeholder="Тип услуги" class="w-full rounded border p-2" />
                    <input v-model="newService.code" type="text" placeholder="Код" class="w-full rounded border p-2" />
                    <textarea v-model="newService.notes" placeholder="Примечания" class="w-full rounded border p-2"></textarea>
                    <div class="flex gap-2">
                        <input v-model="newService.start" type="time" class="w-1/2 rounded border p-2" />
                        <input v-model="newService.end" type="time" class="w-1/2 rounded border p-2" />
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="showAddForm = false"
                        class="rounded px-4 py-2 text-gray-700 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        Отмена
                    </button>
                    <button @click="submitForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Сохранить</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
