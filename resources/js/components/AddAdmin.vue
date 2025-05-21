<script setup>
import axios from 'axios';
import { ref, watchEffect } from 'vue';

const props = defineProps({
    modelValue: Object,
    isEdit: Boolean,
});

const emit = defineEmits(['submit', 'cancel']);

// Объявляем ref для хранения сообщения об ошибке
const errorMessage = ref(null);

const form = ref({
    lastName: '',
    firstName: '',
    middleName: '',
    phone: '',
    email: '',
    status: 'Активный',
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

const validateForm = () => {
    if (!form.value.lastName.trim()) {
        errorMessage.value = 'Фамилия обязательна для заполнения';
        return false;
    }
    if (!form.value.firstName.trim()) {
        errorMessage.value = 'Имя обязательно для заполнения';
        return false;
    }
    if (!form.value.phone.trim()) {
        errorMessage.value = 'Телефон обязателен для заполнения';
        return false;
    }
    if (!form.value.email.trim()) {
        errorMessage.value = 'Email обязателен для заполнения';
        return false;
    }

    // Проверка формата email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.email)) {
        errorMessage.value = 'Введите корректный email';
        return false;
    }

    errorMessage.value = null;
    return true;
};

const handleSubmit = async () => {
    // Сначала валидируем форму
    if (!validateForm()) {
        return;
    }

    try {
        let response;
        if (props.isEdit && props.modelValue?.id) {
            response = await axios.put(`/users/admins/${props.modelValue.id}`, form.value);
        } else {
            response = await axios.post('/users/admins', form.value);
        }

        if (response.data.success) {
            emit('submit', response.data.user);
            emit('cancel');
        } else {
            errorMessage.value = response.data.message || 'Произошла ошибка при сохранении';
        }
    } catch (error) {
        if (error.response) {
            // Обработка ошибок валидации с сервера
            if (error.response.data.errors) {
                const errors = Object.values(error.response.data.errors).flat();
                errorMessage.value = errors.join(', ');
            } else {
                errorMessage.value = error.response.data.message || 'Ошибка сервера';
            }
        } else {
            errorMessage.value = 'Не удалось подключиться к серверу';
        }
    }
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
                <!-- Поля формы остаются такими же, как в предыдущем примере -->
                <div>
                    <label class="block text-sm font-medium dark:text-white"> Фамилия <span class="text-red-500">*</span> </label>
                    <input v-model="form.lastName" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white"> Имя <span class="text-red-500">*</span> </label>
                    <input v-model="form.firstName" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Отчество</label>
                    <input v-model="form.middleName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white"> Телефон <span class="text-red-500">*</span> </label>
                    <input v-model="form.phone" @input="formatPhone" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white"> Email <span class="text-red-500">*</span> </label>
                    <input v-model="form.email" required type="email" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div v-if="isEdit">
                    <label class="block text-sm font-medium dark:text-white">Статус</label>
                    <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                        <option>Активный</option>
                        <option>Уволенный</option>
                    </select>
                </div>

                <div v-if="errorMessage" class="text-sm text-red-500">
                    {{ errorMessage }}
                </div>

                <div v-if="errorMessage" class="text-sm text-red-500">
                    {{ errorMessage }}
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="cancel" class="rounded bg-gray-200 px-4 py-2 dark:bg-gray-600 dark:text-white">Отмена</button>
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        {{ isEdit ? 'Сохранить' : 'Добавить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
