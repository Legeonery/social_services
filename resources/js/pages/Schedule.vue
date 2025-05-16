<script setup>
// ОБЯЗАТЕЛЬНО NPM INSTALL DAYJS
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';

const breadcrumbs = [
  { title: 'Расписание', href: '/schedule' },
];

const currentDate = ref(dayjs());
const selectedDate = ref(dayjs());
const services = ref([]); // Предполагаем, что данные приходят через props или запрос
const selectedWorker = ref(null);

// Генерация дней календаря (упрощённо)
const startOfMonth = computed(() => currentDate.value.startOf('month'));
const daysInMonth = computed(() => currentDate.value.daysInMonth());
const calendarDays = computed(() => {
  const start = startOfMonth.value;
  return Array.from({ length: daysInMonth.value }, (_, i) => {
    const date = start.add(i, 'day');
    return {
      date,
      hasServices: services.value.some(s => dayjs(s.date).isSame(date, 'day'))
    };
  });
});

// Переключение месяца
const prevMonth = () => currentDate.value = currentDate.value.subtract(1, 'month');
const nextMonth = () => currentDate.value = currentDate.value.add(1, 'month');

// Переключение дней
const prevDay = () => selectedDate.value = selectedDate.value.subtract(1, 'day');
const nextDay = () => selectedDate.value = selectedDate.value.add(1, 'day');

// Фильтрация
const filteredServices = computed(() => {
  return services.value
    .filter(s => dayjs(s.date).isSame(selectedDate.value, 'day'))
    .filter(s => !selectedWorker.value || s.worker.id === selectedWorker.value);
});
</script>

<template>
  <Head title="Schedule" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Календарь -->
      <div class="border rounded-xl p-4">
        <div class="flex justify-between items-center mb-4">
          <button @click="prevMonth">←</button>
          <h2 class="text-lg font-semibold">{{ currentDate.format('MMMM YYYY') }}</h2>
          <button @click="nextMonth">→</button>
        </div>
        <div class="grid grid-cols-7 gap-2">
          <div
            v-for="day in calendarDays"
            :key="day.date.toString()"
            class="p-2 border rounded cursor-pointer hover:bg-gray-100"
            @click="selectedDate = day.date"
          >
            <div>{{ day.date.date() }}</div>
            <div v-if="day.hasServices" class="text-green-500 text-sm">●</div>
          </div>
        </div>
      </div>

      <!-- Панель фильтрации -->
      <div class="border rounded-xl p-4 flex flex-col gap-2 md:flex-row md:items-center justify-between">
        <div class="flex items-center gap-2">
          <label>Дата:</label>
          <input type="date" :value="selectedDate.format('YYYY-MM-DD')" @input="e => selectedDate = dayjs(e.target.value)" class="border rounded px-2 py-1" />
        </div>
        <div class="flex items-center gap-2">
          <label>Соц. работник:</label>
          <select v-model="selectedWorker" class="border rounded px-2 py-1">
            <option :value="null">Все</option>
            <option v-for="worker in [...new Set(services.map(s => s.worker))]" :key="worker.id" :value="worker.id">
              {{ worker.name }}
            </option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <button @click="prevDay" class="px-2 py-1 border rounded">←</button>
          <div>{{ selectedDate.format('DD MMMM YYYY') }}</div>
          <button @click="nextDay" class="px-2 py-1 border rounded">→</button>
        </div>
      </div>

      <!-- Таблица услуг -->
      <div class="border rounded-xl p-4 overflow-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">ФИО соц. работника</th>
              <th class="px-4 py-2 text-left">ФИО клиента</th>
              <th class="px-4 py-2 text-left">Услуга</th>
              <th class="px-4 py-2 text-left">Время</th>
              <th class="px-4 py-2 text-left">Статус</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="service in filteredServices" :key="service.id">
              <td class="px-4 py-2">{{ service.worker.name }}</td>
              <td class="px-4 py-2">{{ service.client.name }}</td>
              <td class="px-4 py-2">
                {{ service.type }} ({{ service.code }})
                <button class="ml-2 text-blue-600" @click="$emit('show-note', service)">ℹ</button>
              </td>
              <td class="px-4 py-2">{{ service.start }} - {{ service.end }}</td>
              <td class="px-4 py-2" :class="{'text-green-600': service.status === 'Подтверждена', 'text-red-600': service.status !== 'Подтверждена'}">
                {{ service.status }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Пагинация, если нужна -->
        <!-- <Pagination :meta="meta" /> -->
      </div>
    </div>
  </AppLayout>
</template>
