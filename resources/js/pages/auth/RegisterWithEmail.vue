<template>
    <div class="mx-auto mt-10 max-w-md rounded bg-white p-6 shadow">
        <h2 class="mb-4 text-2xl font-bold">Подтвердите вашу почту</h2>

        <div v-if="email" class="mb-4 text-gray-700">
            На <strong>{{ email }}</strong> было отправлено письмо с подтверждением.
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                <div v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                    {{ form.errors.email }}
                </div>
            </div>

            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" :disabled="form.processing">
                Отправить повторно
            </button>
        </form>
    </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const email = computed(() => page.props.email);

const form = useForm({
    email: email.value || '',
});

const submit = () => {
    form.post('/register/check', {
        preserveScroll: true,
    });
};
</script>
