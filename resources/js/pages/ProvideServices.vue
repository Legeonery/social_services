<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import 'dayjs/locale/ru';
import { computed, ref } from 'vue';

dayjs.locale('ru');

const currentDate = ref(dayjs());
const selectedDate = ref(dayjs());
const showAddForm = ref(false);
const today = computed(() => dayjs().format('YYYY-MM-DD'));
const goToDay = (date) => {
    const formatted = dayjs(date).format('YYYY-MM-DD');
    window.location.href = `/provide-services/${formatted}`;
};
// Пример услуг
const services = ref([
    // ... твои предустановленные данные
]);

const startOfMonth = computed(() => currentDate.value.startOf('month'));
const daysInMonth = computed(() => currentDate.value.daysInMonth());
const calendarDays = computed(() => {
    const start = startOfMonth.value;
    const days = [];
    const startWeekday = start.day() === 0 ? 6 : start.day() - 1;

    for (let i = 0; i < startWeekday; i++) {
        days.push({ empty: true });
    }

    for (let i = 0; i < daysInMonth.value; i++) {
        const date = start.clone().add(i, 'day');
        const servicesForDate = services.value.filter((s) => dayjs(s.date).isSame(date, 'day'));
        days.push({
            date,
            isToday: date.format('YYYY-MM-DD') === today.value,
            services: servicesForDate.slice(0, 2),
            extraCount: Math.max(0, servicesForDate.length - 2),
        });
    }

    return days;
});

const prevMonth = () => (currentDate.value = currentDate.value.subtract(1, 'month'));
const nextMonth = () => (currentDate.value = currentDate.value.add(1, 'month'));

// Форма добавления
const newService = ref({
    client: '',
    type: '',
    code: '',
    notes: '',
    start: '',
    end: '',
    date: dayjs().format('YYYY-MM-DD'),
});

const resetForm = () => {
    newService.value = {
        client: '',
        type: '',
        code: '',
        notes: '',
        start: '',
        end: '',
        date: dayjs().format('YYYY-MM-DD'),
    };
};

const openForm = () => {
    selectedDate.value = dayjs();
    newService.value.date = selectedDate.value.format('YYYY-MM-DD');
    showAddForm.value = true;
};

const submitForm = () => {
    const newId = services.value.length + 1;
    services.value.push({
        id: newId,
        date: newService.value.date,
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
</script>

<template>
    <Head title="Добавить услуги" />

    <AppLayout :breadcrumbs="[{ title: 'Добавить услуги', href: '/provide-services' }]">
        <div class="flex flex-col gap-6 p-4">
            <!-- Кнопка сверху -->
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold">Добавить услуги</h1>
                <button class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700" @click="openForm">+ Добавить услугу</button>
            </div>

            <!-- Календарь -->
            <div class="rounded-xl border bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <button @click="prevMonth" class="text-lg font-bold">←</button>
                    <h2 class="text-xl font-semibold dark:text-white">
                        {{ currentDate.format('MMMM YYYY').charAt(0).toUpperCase() + currentDate.format('MMMM YYYY').slice(1) }}
                    </h2>
                    <button @click="nextMonth" class="text-lg font-bold">→</button>
                </div>

                <div class="mb-2 grid grid-cols-7 gap-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <div>Пн</div>
                    <div>Вт</div>
                    <div>Ср</div>
                    <div>Чт</div>
                    <div>Пт</div>
                    <div>Сб</div>
                    <div>Вс</div>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    <div
                        v-for="(day, index) in calendarDays"
                        :key="index"
                        class="relative min-h-[60px] rounded-lg border p-3 text-sm"
                        :class="[
                            day.empty
                                ? 'border-none bg-transparent'
                                : 'cursor-pointer bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600',
                        ]"
                        @click="!day.empty && goToDay(day.date)"
                    >
                        <template v-if="!day.empty">
                            <div class="text-base font-semibold dark:text-white">{{ day.date.date() }}</div>
                            <div v-for="service in day.services" :key="service.id" class="truncate text-xs text-gray-700 dark:text-gray-300">
                                {{ service.code }} — {{ service.worker.name }}
                            </div>
                            <div v-if="day.extraCount > 0" class="mt-1 text-xs font-semibold text-red-500">+{{ day.extraCount }} ещё</div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Модалка -->
            <div v-if="showAddForm" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Новая услуга на {{ newService.date }}</h3>

                    <div class="space-y-3">
                        <input v-model="newService.client" type="text" placeholder="Клиент" class="w-full rounded border p-2" />
                        <input v-model="newService.type" type="text" placeholder="Тип услуги" class="w-full rounded border p-2" />
                        <input v-model="newService.code" type="text" placeholder="Код" class="w-full rounded border p-2" />
                        <textarea v-model="newService.notes" placeholder="Примечания" class="w-full rounded border p-2"></textarea>
                        <div class="flex gap-2">
                            <input v-model="newService.start" type="time" class="w-1/2 rounded border p-2" />
                            <input v-model="newService.end" type="time" class="w-1/2 rounded border p-2" />
                        </div>
                        <input v-model="newService.date" type="date" class="w-full rounded border p-2" />
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
        </div>
    </AppLayout>
</template>
