<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Форма для выхода
const form = useForm();
const logout = () => {
    form.post(route('logout'));
};
</script>

<template>
    <Head title="Добро пожаловать">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AuthLayout
        :title="$page.props.auth.user ? 'Вы уже вошли в систему ранее' : 'Добро пожаловать 👋'"
        :description="$page.props.auth.user ? 'Вы можете перейти в личный кабинет' : 'Войдите или создайте аккаунт, чтобы продолжить'"
    >
        <div class="flex flex-col gap-4">
            <template v-if="$page.props.auth.user">
                <!-- Вывод имени пользователя -->
                <p class="text-center text-lg text-[#1b1b18] dark:text-white">Привет, {{ $page.props.auth.user.name }}!</p>

                <!-- Кнопка "Перейти в кабинет" -->
                <Link
                    :href="route('users')"
                    class="inline-block w-full rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-[#1b1b18] transition hover:bg-gray-100 dark:border-gray-600 dark:bg-[#2a2a2a] dark:text-white dark:hover:bg-[#3a3a3a]"
                >
                    Перейти в кабинет
                </Link>

                <!-- Кнопка "Выйти" -->
                <Button
                    @click="logout"
                    class="inline-block w-full rounded-lg border border-red-300 bg-red-100 px-5 py-2 text-sm font-medium text-red-600 transition hover:bg-red-200 dark:border-red-600 dark:bg-[#3a3a3a] dark:text-red-400 dark:hover:bg-[#5a5a5a]"
                >
                    Выйти
                </Button>
            </template>

            <template v-else>
                <!-- Кнопки для неавторизованных пользователей -->
                <Link
                    :href="route('login')"
                    class="inline-block w-full rounded-lg bg-[#1b1b18] px-5 py-2 text-center text-sm font-medium text-white transition hover:bg-[#2b2b24] dark:bg-white dark:text-[#1b1b18] dark:hover:bg-[#e5e5e5]"
                >
                    Войти
                </Link>
                <Link
                    :href="route('register')"
                    class="inline-block w-full rounded-lg border border-gray-300 bg-white px-5 py-2 text-center text-sm font-medium text-[#1b1b18] transition hover:bg-gray-100 dark:border-gray-600 dark:bg-[#2a2a2a] dark:text-white dark:hover:bg-[#3a3a3a]"
                >
                    Зарегистрироваться
                </Link>
            </template>
        </div>
    </AuthLayout>
</template>
