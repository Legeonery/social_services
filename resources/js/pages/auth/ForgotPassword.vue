<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Забыли пароль" description="Введите свой e-mail, чтобы получить ссылку для сброса пароля">
        <Head title="Восстановление пароля" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" placeholder="you@example.com" autofocus />
                <InputError :message="form.errors.email" />
            </div>

            <Button class="w-full" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Отправить ссылку
            </Button>

            <div class="text-muted-foreground text-center text-sm">
                <span>Вернуться к </span>
                <TextLink :href="route('login')" class="underline underline-offset-4">входу</TextLink>
            </div>
        </form>
    </AuthLayout>
</template>
