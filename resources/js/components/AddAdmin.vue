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
    status: '',
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
        };
    }
});

const handleSubmit = () => {
    emit('submit', form.value);
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold dark:text-white">
                {{ isEdit ? 'Редактировать администратора' : 'Добавить администратора' }}
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium dark:text-white">Фамилия *</label>
                    <input v-model="form.lastName" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white">Имя *</label>
                    <input v-model="form.firstName" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white">Отчество</label>
                    <input v-model="form.middleName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Телефон *</label>
                    <input v-model="form.phone" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white">Email *</label>
                    <input v-model="form.email" required type="email" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <!-- Статус показываем только при редактировании -->
                <div v-if="isEdit">
                    <label class="block text-sm font-medium dark:text-white">Статус</label>
                    <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                        <option>Активный</option>
                        <option>Уволенный</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="$emit('cancel')" class="rounded bg-gray-200 px-4 py-2 dark:bg-gray-600 dark:text-white">
                        Отмена
                    </button>
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        {{ isEdit ? 'Сохранить' : 'Добавить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
