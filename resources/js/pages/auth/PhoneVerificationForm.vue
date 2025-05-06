<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface PhoneVerificationForm {
    phone: string;
    code: string;
    [key: string]: any;
}

const page = usePage<{ phone: string | null }>();
const phone = page.props.phone ?? ''; // защита от null

const form = useForm<PhoneVerificationForm>({
    phone,
    code: '',
});

const submit = () => {
    form.post(route('verify.phone'), {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <AuthBase title="Verify Phone Number" description="Enter the code sent to your phone">
        <Head title="Phone Verification" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="code">Verification Code</Label>
                    <Input id="code" type="text" inputmode="numeric" maxlength="6" required autofocus v-model="form.code" placeholder="123456" />
                    <InputError :message="form.errors.code" />
                </div>

                <input type="hidden" name="phone" :value="form.phone" />

                <Button type="submit" class="w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Verify
                </Button>
            </div>
        </form>
    </AuthBase>
</template>
