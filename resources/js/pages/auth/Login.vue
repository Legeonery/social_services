<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Вход в аккаунт" description="Рады видеть вас снова!">
        <Head title="Вход" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required autofocus autocomplete="email" v-model="form.email" placeholder="you@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Пароль</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm">Забыли пароль?</TextLink>
                    </div>
                    <Input id="password" type="password" required autocomplete="current-password" v-model="form.password" placeholder="********" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox id="remember" v-model="form.remember" />
                    <Label for="remember" class="text-sm">Запомнить меня</Label>
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Войти
                </Button>
            </div>

            <div class="text-muted-foreground text-center text-sm">
                Нет аккаунта?
                <TextLink :href="route('register')" class="underline underline-offset-4">Зарегистрироваться</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
