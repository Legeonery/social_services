<script setup lang="ts">
import axios from 'axios';
import { defineEmits, defineProps, ref, watch } from 'vue';

const emit = defineEmits(['submit', 'cancel']);

const props = defineProps<{
    initial?: {
        name: string;
        code: string;
        description: string;
        price: number;
        type: 'main' | 'additional';
        id?: number;
    };
    isEdit?: boolean;
}>();

const form = ref({
    name: '',
    code: '',
    description: '',
    price: 0,
    type: 'main',
});

const isLoading = ref(false);
const error = ref<string | null>(null);
const errors = ref<Record<string, string[]>>({});

watch(
    () => props.initial,
    (val) => {
        if (val) {
            form.value = { ...val };
        } else {
            form.value = { name: '', code: '', description: '', price: 0, type: 'main' };
        }
        errors.value = {};
        error.value = null;
    },
    { immediate: true },
);

async function submit() {
    error.value = null;
    errors.value = {};
    isLoading.value = true;

    try {
        let response;
        if (props.isEdit && props.initial?.id) {
            response = await axios.put(`/services-api/${props.initial.id}`, form.value);
        } else {
            response = await axios.post('/services-api', form.value);
        }

        emit('submit', response.data);
    } catch (err: any) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
        } else {
            error.value = err.response?.data?.message || 'Ошибка при сохранении';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Наименование -->
        <div>
            <label class="block text-sm font-medium dark:text-white">Наименование *</label>
            <input v-model="form.name" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            <div v-if="errors.name" class="text-sm text-red-600">{{ errors.name[0] }}</div>
        </div>

        <!-- Шифр -->
        <div>
            <label class="block text-sm font-medium dark:text-white">Шифр *</label>
            <input v-model="form.code" :disabled="isEdit" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            <div v-if="errors.code" class="text-sm text-red-600">{{ errors.code[0] }}</div>
        </div>

        <!-- Описание -->
        <div>
            <label class="block text-sm font-medium dark:text-white">Описание</label>
            <textarea v-model="form.description" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            <div v-if="errors.description" class="text-sm text-red-600">{{ errors.description[0] }}</div>
        </div>

        <!-- Цена -->
        <div>
            <label class="block text-sm font-medium dark:text-white">Цена (₽)</label>
            <input v-model.number="form.price" type="number" min="0" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
            <div v-if="errors.price" class="text-sm text-red-600">{{ errors.price[0] }}</div>
        </div>

        <!-- Тип -->
        <div>
            <label class="block text-sm font-medium dark:text-white">Тип услуги</label>
            <select v-model="form.type" :disabled="isEdit" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                <option value="main">Основная</option>
                <option value="additional">Дополнительная</option>
            </select>
            <div v-if="errors.type" class="text-sm text-red-600">{{ errors.type[0] }}</div>
        </div>

        <!-- Общая ошибка -->
        <div v-if="error" class="text-sm text-red-600">{{ error }}</div>

        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$emit('cancel')" class="rounded bg-gray-200 px-4 py-2 dark:bg-gray-600 dark:text-white">Отмена</button>
            <button type="submit" :disabled="isLoading" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">
                {{ isEdit ? 'Сохранить' : 'Добавить' }}
            </button>
        </div>
    </form>
</template>
