<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
    status: String,
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <Head title="Восстановление пароля" />

        <div class="mb-4 text-sm">
            Забыли пароль? Без проблем. Просто сообщите нам свой адрес
            электронной почты, и мы вышлем вам ссылку для сброса пароля, которая
            позволит вам выбрать новый.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="email">Email</label>
                <InputText
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    aria-describedby="email-help"
                    required
                />
                <small id="email-help" class="text-sm text-red-600">
                    {{ form.errors.email }}
                </small>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Button
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    label="Получить ссылку для сброса"
                />
            </div>
        </form>
    </GuestLayout>
</template>
