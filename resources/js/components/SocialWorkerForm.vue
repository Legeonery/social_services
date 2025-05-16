<script setup>
import { ref, watchEffect } from 'vue';

const props = defineProps({
    modelValue: Object,
    isEdit: Boolean,
});

const emit = defineEmits(['submit', 'cancel']);

const form = ref({
    fullName: '',
    phone: '',
    email: '',
    status: 'Активный',
    unavailabilityPeriod: {
        from: '',
        to: '',
    },
});

watchEffect(() => {
    if (props.modelValue) {
        const [lastName = '', firstName = '', middleName = ''] = props.modelValue.fullName?.split(' ') || [];
        form.value = {
            lastName,
            firstName,
            middleName,
            phone: props.modelValue.phone || '',
            email: props.modelValue.email || '',
            status: props.modelValue.status || 'Активный',
            unavailabilityPeriod: props.modelValue.unavailabilityPeriod || { from: '', to: '' },
        };
    } else if (!props.isEdit) {
        form.value.status = 'Активный';
    }
});

// Очистка периода недоступности, если статус не требует его
watchEffect(() => {
    if (!['На больничном', 'В отпуске'].includes(form.value.status)) {
        form.value.unavailabilityPeriod = { from: '', to: '' };
    }
});

const handleSubmit = () => {
    const payload = { ...form.value };
    if (!['На больничном', 'В отпуске'].includes(payload.status)) {
        delete payload.unavailabilityPeriod;
    }
    emit('submit', payload);
};

const cancelForm = () => {
    emit('cancel');
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold dark:text-white">{{ isEdit ? 'Редактировать соц. работника' : 'Добавить соц. работника' }}</h2>
            <!-- ФИО -->
            <form @submit.prevent="handleSubmit" class="space-y-4">
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
                        <input
                            type="date"
                            v-model="form.unavailabilityPeriod.from"
                            class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            type="date"
                            v-model="form.unavailabilityPeriod.to"
                            class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-6 flex justify-end gap-2">
                    <button @click="cancelForm" class="rounded border px-4 py-2 dark:border-gray-500 dark:text-white">Отмена</button>
                    <button @click="submitForm" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        {{ isEdit ? 'Сохранить' : 'Добавить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
