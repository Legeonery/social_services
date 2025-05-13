<script setup lang="ts">
import { emit, reactive, watch, watchEffect } from 'vue';

const props = defineProps<{ modelValue: any; isEdit: boolean }>();
const emit = defineEmits(['submit', 'cancel']);

const form = reactive({
    lastName: '',
    firstName: '',
    middleName: '',
    phone: '',
    email: '',
    status: 'Активный',
    unavailabilityPeriod: {
        from: '',
        to: '',
    },
});

// При передаче modelValue (редактирование)
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            Object.assign(form, {
                ...form,
                ...newVal,
                // гарантируем, что поле существует
                unavailabilityPeriod: newVal.unavailabilityPeriod || { from: '', to: '' },
            });
        }
    },
    { immediate: true },
);

// Установка статуса "Активный" при создании
watchEffect(() => {
    if (!props.isEdit) {
        form.status = 'Активный';
    }
});

// Очищаем период недоступности, если статус не требует его
watch(
    () => form.status,
    (newStatus) => {
        if (!['На больничном', 'В отпуске'].includes(newStatus)) {
            form.unavailabilityPeriod.from = '';
            form.unavailabilityPeriod.to = '';
        }
    },
);

function submitForm() {
    // Можно опционально убрать unavailabilityPeriod, если не нужен
    const payload = {
        ...form,
    };

    if (!['На больничном', 'В отпуске'].includes(payload.status)) {
        delete payload.unavailabilityPeriod;
    }

    emit('submit', payload);
}

function cancelForm() {
    emit('cancel');
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold dark:text-white">{{ isEdit ? 'Редактировать соц. работника' : 'Добавить соц. работника' }}</h2>
            <!-- ФИО -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Фамилия</label>
                <input v-model="form.lastName" class="mt-1 w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Имя</label>
                <input v-model="form.firstName" class="mt-1 w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Отчество</label>
                <input v-model="form.middleName" class="mt-1 w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>

            <!-- Контакты -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Телефон</label>
                <input v-model="form.phone" class="mt-1 w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Email</label>
                <input v-model="form.email" class="mt-1 w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>

            <!-- Статус (только при редактировании) -->
            <div v-if="isEdit" class="mb-4">
                <label class="block text-sm font-medium dark:text-white">Статус</label>
                <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                    <option value="Активный">Активный</option>
                    <option value="В отпуске">В отпуске</option>
                    <option value="На больничном">На больничном</option>
                    <option value="Уволенный">Уволенный</option>
                </select>
            </div>
            <div v-if="['В отпуске', 'На больничном'].includes(form.status)">
                <label class="block text-sm font-medium dark:text-white">Период недоступности</label>
                <div class="flex gap-2">
                    <input type="date" v-model="form.unavailabilityPeriod.from" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                    <input type="date" v-model="form.unavailabilityPeriod.to" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>
            </div>
            <!-- Кнопки -->
            <div class="mt-6 flex justify-end gap-2">
                <button @click="cancelForm" class="rounded border px-4 py-2 dark:border-gray-500 dark:text-white">Отмена</button>
                <button @click="submitForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    {{ isEdit ? 'Сохранить' : 'Добавить' }}
                </button>
            </div>
        </div>
    </div>
</template>
