<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import dayjs from 'dayjs';
import 'dayjs/locale/ru';
import { computed, ref } from 'vue';

dayjs.locale('ru');

const props = defineProps({ date: String });
const selectedDate = ref(dayjs(props.date || dayjs()).startOf('day'));

// Исходные данные (в реальности могут быть получены из API или props)
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
    {
        id: 4,
        date: dayjs().subtract(1, 'day').format('YYYY-MM-DD'),
        worker: { id: 3, name: 'Кузнецова Е.Е.' },
        client: { id: 104, name: 'Васильев С.С.' },
        type: 'Медицинское сопровождение',
        code: 'MED-04',
        notes: 'В поликлинику',
        start: '10:30',
        end: '12:00',
        status: 'Подтверждена',
    },
    {
        id: 5,
        date: dayjs().add(2, 'day').format('YYYY-MM-DD'),
        worker: { id: 4, name: 'Николаев Д.Д.' },
        client: { id: 105, name: 'Григорьева О.О.' },
        type: 'Доставка лекарств',
        code: 'DRG-05',
        notes: 'Рецепт прилагается',
        start: '13:00',
        end: '13:30',
        status: 'Не подтверждена',
    },
    {
        id: 6,
        date: dayjs().add(2, 'day').format('YYYY-MM-DD'),
        worker: { id: 1, name: 'Иванов И.И.' },
        client: { id: 106, name: 'Козлов М.М.' },
        type: 'Посещение',
        code: 'VIS-06',
        notes: 'Проверка состояния',
        start: '15:00',
        end: '15:45',
        status: 'Подтверждена',
    },
    {
        id: 7,
        date: dayjs().add(3, 'day').format('YYYY-MM-DD'),
        worker: { id: 5, name: 'Фролова Т.Т.' },
        client: { id: 107, name: 'Ершов А.А.' },
        type: 'Покупка одежды',
        code: 'CLT-07',
        notes: 'Размеры указаны',
        start: '10:00',
        end: '11:30',
        status: 'Не подтверждена',
    },
    {
        id: 8,
        date: dayjs().add(3, 'day').format('YYYY-MM-DD'),
        worker: { id: 2, name: 'Сидорова А.А.' },
        client: { id: 108, name: 'Романова Ж.Ж.' },
        type: 'Социальная беседа',
        code: 'TLC-08',
        notes: 'По заявке',
        start: '12:00',
        end: '13:00',
        status: 'Подтверждена',
    },
    {
        id: 9,
        date: dayjs().subtract(2, 'day').format('YYYY-MM-DD'),
        worker: { id: 3, name: 'Кузнецова Е.Е.' },
        client: { id: 109, name: 'Семенов И.И.' },
        type: 'Оплата ЖКХ',
        code: 'UTL-09',
        notes: 'Переданы квитанции',
        start: '09:00',
        end: '10:00',
        status: 'Подтверждена',
    },
    {
        id: 10,
        date: dayjs().subtract(2, 'day').format('YYYY-MM-DD'),
        worker: { id: 5, name: 'Фролова Т.Т.' },
        client: { id: 110, name: 'Беляев П.П.' },
        type: 'Покупка бытовой химии',
        code: 'HMS-10',
        notes: 'Список от родственников',
        start: '11:30',
        end: '12:30',
        status: 'Не подтверждена',
    },
    {
        id: 11,
        date: dayjs().subtract(2, 'day').format('YYYY-MM-DD'),
        worker: { id: 5, name: 'Фролова Т.Т.' },
        client: { id: 110, name: 'Беляев П.П.' },
        type: 'Покупка бытовой химии',
        code: 'HMS-11',
        notes: 'Список от родственников',
        start: '11:30',
        end: '12:30',
        status: 'Не подтверждена',
    }
    // Другие записи...
]);
const workers = computed(() => {
    const map = new Map();
    for (const s of services.value) {
        if (!map.has(s.worker.id)) {
            map.set(s.worker.id, s.worker);
        }
    }
    return Array.from(map.values());
});
// Состояние
const selectedWorker = ref(null);

const showNoteModal = ref(false);
const selectedNote = ref('');

// Методы
function prevDay() {
    selectedDate.value = selectedDate.value.subtract(1, 'day');
}

function nextDay() {
    selectedDate.value = selectedDate.value.add(1, 'day');
}

function openNote(service) {
    selectedNote.value = service.note || 'Заметка отсутствует';
    showNoteModal.value = true;
}

function closeNote() {
    showNoteModal.value = false;
}

// Фильтрованные данные
const filteredServices = computed(() => {
    return services.value.filter((s) => {
        const isSameDay = dayjs(s.date).isSame(selectedDate.value, 'day');
        const matchesWorker = selectedWorker.value === null || s.worker.id === selectedWorker.value;
        return isSameDay && matchesWorker;
    });
});
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <div class="flex flex-wrap justify-between gap-4 rounded-xl border bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-2">
                    <label class="text-sm dark:text-gray-200">Дата:</label>
                    <input
                        type="date"
                        :value="selectedDate.format('YYYY-MM-DD')"
                        @input="(e) => (selectedDate = dayjs(e.target.value).startOf('day'))"
                        class="rounded border px-2 py-1 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm dark:text-gray-200">Соц. работник:</label>
                    <select v-model="selectedWorker" class="rounded border px-2 py-1 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option :value="null">Все</option>
                        <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                            {{ worker.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="prevDay" class="rounded border px-2 py-1 dark:border-gray-600 dark:text-white">←</button>
                    <span class="font-medium dark:text-white">{{ selectedDate.format('DD MMMM YYYY') }}</span>
                    <button @click="nextDay" class="rounded border px-2 py-1 dark:border-gray-600 dark:text-white">→</button>
                </div>
            </div>

            <!-- Таблица оказанных услуг -->
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
                <!-- Модальное окно -->
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

                <!-- Пагинация, если подключена -->
                <!-- <Pagination :meta="meta" /> -->
            </div>
        </div>
    </AppLayout>
</template>
