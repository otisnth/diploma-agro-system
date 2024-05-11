<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Сброс пароля" />

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

            <div class="mt-4">
                <label for="password">Пароль</label>
                <InputText
                    id="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    type="password"
                    required
                    aria-describedby="password-help"
                />
                <small id="password-help" class="text-sm text-red-600">
                    {{ form.errors.password }}
                </small>
            </div>

            <div class="mt-4">
                <label for="password_confirmation">Повтор пароля</label>
                <InputText
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    aria-describedby="password_confirmation-help"
                />
                <small
                    id="password_confirmation-help"
                    class="text-sm text-red-600"
                >
                    {{ form.errors.password_confirmation }}
                </small>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Button
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    label="Сбросить пароль"
                />
            </div>
        </form>
    </GuestLayout>
</template>
