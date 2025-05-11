<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email_or_phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Регистрация" description="Вас приветствует ОСОН">
        <Head title="Регистрация" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name">Имя</Label>
                    <Input id="name" v-model="form.name" autocomplete="name" placeholder="Ваше имя" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email_or_phone">Email или телефон</Label>
                    <Input
                        id="email_or_phone"
                        type="text"
                        required
                        autocomplete="username"
                        v-model="form.email_or_phone"
                        placeholder="name@gmail.com или +7 900 999 00 00"
                    />
                    <InputError :message="form.errors.email_or_phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Пароль</Label>
                    <Input id="password" type="password" required v-model="form.password" placeholder="********" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Подтверждение пароля</Label>
                    <Input id="password_confirmation" type="password" required v-model="form.password_confirmation" placeholder="********" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Продолжить
                </Button>
            </div>

            <div class="text-muted-foreground text-center text-sm">
                Уже есть аккаунт?
                <TextLink :href="route('login')" class="underline underline-offset-4">Войти</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
