<script setup>
import axios from 'axios';
import { ref, watchEffect, onMounted } from 'vue';

const props = defineProps({
    modelValue: Object,
    isEdit: Boolean,
});

const emit = defineEmits(['submit', 'cancel']);

const errorMessage = ref(null);
const clientTypes = ref([]);
const isClientTypesLoaded = ref(false);

const form = ref({
    lastName: '',
    firstName: '',
    middleName: '',
    phone: '',
    email: '',
    socialWorker: '',
    client_type_id: null,
    status: 'Активный',
});

// Загрузка типов клиента
onMounted(async () => {
    try {
        const response = await axios.get('/client-types');
        clientTypes.value = response.data;
        isClientTypesLoaded.value = true;
    } catch (error) {
        console.error('Ошибка загрузки типов клиента:', error);
    }
});

// Заполнение формы при редактировании
watchEffect(() => {
    if (props.modelValue && isClientTypesLoaded.value) {
        const [lastName = '', firstName = '', middleName = ''] = props.modelValue.fullName?.split(' ') || [];

        form.value = {
            lastName,
            firstName,
            middleName,
            phone: props.modelValue.phone || '',
            email: props.modelValue.email || '',
            socialWorker: props.modelValue.socialWorker || '',
            client_type_id: props.modelValue.client_type_id ? Number(props.modelValue.client_type_id) : null,
            status: props.modelValue.status || 'Активный',
        };
    }
});

const validateForm = () => {
    if (!form.value.lastName.trim()) return errorMessage.value = 'Фамилия обязательна';
    if (!form.value.firstName.trim()) return errorMessage.value = 'Имя обязательно';
    if (!form.value.phone.trim()) return errorMessage.value = 'Телефон обязателен';
    if (!form.value.email.trim()) return errorMessage.value = 'Email обязателен';

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.email)) return errorMessage.value = 'Некорректный email';

    if (!form.value.client_type_id) return errorMessage.value = 'Выберите тип клиента';

    errorMessage.value = null;
    return true;
};

const handleSubmit = async () => {
    if (!validateForm()) return;

    const fullName = `${form.value.lastName} ${form.value.firstName} ${form.value.middleName}`.trim();
    const payload = { ...form.value, fullName };

    try {
        let response;
        if (props.isEdit && props.modelValue?.id) {
            response = await axios.put(`/users/clients/${props.modelValue.id}`, payload);
        } else {
            response = await axios.post('/users/clients', payload);
        }

        if (response.data.success) {
            emit('submit', response.data.user);
            emit('cancel');
        } else {
            errorMessage.value = response.data.message || 'Ошибка при сохранении';
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage.value = errors.join(', ');
        } else {
            errorMessage.value = 'Ошибка подключения к серверу';
        }
    }
};

const cancel = () => emit('cancel');
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold dark:text-white">
                {{ isEdit ? 'Редактировать клиента' : 'Добавить клиента' }}
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium dark:text-white">Фамилия <span class="text-red-500">*</span></label>
                    <input v-model="form.lastName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Имя <span class="text-red-500">*</span></label>
                    <input v-model="form.firstName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Отчество</label>
                    <input v-model="form.middleName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Телефон <span class="text-red-500">*</span></label>
                    <input v-model="form.phone" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Email <span class="text-red-500">*</span></label>
                    <input v-model="form.email" type="email" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Тип клиента <span class="text-red-500">*</span></label>
                    <select v-model="form.client_type_id" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                        <option disabled value="">Выберите тип</option>
                        <option v-for="type in clientTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <div v-if="isEdit">
                    <label class="block text-sm font-medium dark:text-white">Статус</label>
                    <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                        <option>Активный</option>
                        <option>Неактивный</option>
                    </select>
                </div>

                <div v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</div>

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
