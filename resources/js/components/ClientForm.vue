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
    socialWorker: '',
    type: '',
    status: 'Активный',
});

watchEffect(() => {
  if (props.modelValue) {
    const [lastName = '', firstName = '', middleName = ''] = props.modelValue.fullName?.split(' ') || []
    form.value = {
      lastName,
      firstName,
      middleName,
      phone: props.modelValue.phone || '',
      email: props.modelValue.email || '',
      socialWorker: props.modelValue.socialWorker || '',
      type: props.modelValue.type || '',
      status: props.modelValue.status || 'Активный',
    }
  }
})
const handleSubmit = () => {
    emit('submit', form.value);
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold dark:text-white">{{ isEdit ? 'Редактировать клиента' : 'Добавить клиента' }}</h2>

            <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium dark:text-white">Фамилия</label>
                <input v-model="form.lastName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Имя</label>
                <input v-model="form.firstName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Отчество</label>
                <input v-model="form.middleName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Телефон</label>
                <input v-model="form.phone" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Email</label>
                <input v-model="form.email" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Соц. работник</label>
                <input v-model="form.socialWorker" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium dark:text-white">Тип клиента</label>
                <input v-model="form.type" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            </div>
            <div v-if="isEdit">
                <label class="block text-sm font-medium dark:text-white">Статус</label>
                <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                    <option value="Активный">Активный</option>
                    <option value="Неактивный">Неактивный</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button @click="$emit('cancel')" class="rounded bg-gray-300 px-4 py-2 hover:bg-gray-400">Отмена</button>
                <button @click="handleSubmit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Сохранить</button>
            </div>
            </form>
        </div>
    </div>
</template>
