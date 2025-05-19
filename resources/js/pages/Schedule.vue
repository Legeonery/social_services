<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import 'dayjs/locale/ru';
import { computed, ref } from 'vue';

dayjs.locale('ru');

const goToDay = (date) => {
    router.visit(`/schedule/${dayjs(date).format('YYYY-MM-DD')}`);
};
const breadcrumbs = [{ title: 'Расписание', href: '/schedule' }];

// Даты и фильтры
const currentDate = ref(dayjs());
const selectedDate = ref(dayjs());
const today = computed(() => dayjs().format('YYYY-MM-DD'));
const services = ref([]); // Здесь ты подгружаешь данные об услугах
services.value = [
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
    },
];

// Календарь
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
            highlight: dayjs(selectedDate.value).isSame(date, 'day'),
        });
    }

    return days;
});

// Навигация
const prevMonth = () => (currentDate.value = currentDate.value.subtract(1, 'month'));
const nextMonth = () => (currentDate.value = currentDate.value.add(1, 'month'));
</script>

<template>
    <Head title="Расписание" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Календарь месяца -->
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
                        class="relative min-h-[60px] cursor-pointer rounded-lg border p-3 text-sm transition-all"
                        :class="[
                            day.empty
                                ? 'cursor-default border-none bg-transparent'
                                : 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600',
                            day.highlight ? 'ring-2 ring-red-400 dark:ring-red-500' : '',
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
        </div>
    </AppLayout>
</template>
