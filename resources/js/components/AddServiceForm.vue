<script setup lang="ts">
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

watch(
    () => props.initial,
    (val) => {
        if (val) {
            form.value = { ...val };
        } else {
            form.value = { name: '', code: '', description: '', price: 0, type: 'main' };
        }
    },
    { immediate: true },
);

function submit() {
    emit('submit', { ...form.value });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium dark:text-white">Наименование *</label>
            <input v-model="form.name" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
        </div>

        <div>
            <label class="block text-sm font-medium dark:text-white">Шифр *</label>
            <input v-model="form.code" :disabled="isEdit" required class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
        </div>

        <div>
            <label class="block text-sm font-medium dark:text-white">Описание</label>
            <textarea v-model="form.description" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
        </div>

        <div>
            <label class="block text-sm font-medium dark:text-white">Цена (₽)</label>
            <input v-model.number="form.price" type="number" min="0" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
        </div>

        <div>
            <label class="block text-sm font-medium dark:text-white">Тип услуги</label>
            <select v-model="form.type" :disabled="isEdit" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white">
                <option value="main">Основная</option>
                <option value="additional">Дополнительная</option>
            </select>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$emit('cancel')" class="rounded bg-gray-200 px-4 py-2 dark:bg-gray-600 dark:text-white">Отмена</button>
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                {{ isEdit ? 'Сохранить' : 'Добавить' }}
            </button>
        </div>
    </form>
</template>
