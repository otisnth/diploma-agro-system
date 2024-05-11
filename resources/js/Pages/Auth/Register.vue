<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Регистрация" />

        <form @submit.prevent="submit">
            <div>
                <label for="name">Имя</label>
                <InputText
                    id="name"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    aria-describedby="name-help"
                    required
                />
                <small id="name-help" class="text-sm text-red-600">
                    {{ form.errors.name }}
                </small>
            </div>

            <div class="mt-4">
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
                <Link
                    :href="route('login')"
                    class="underline text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Уже есть аккаунт?
                </Link>

                <Button
                    class="ml-4"
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    label="Регистрация"
                />
            </div>
        </form>
    </GuestLayout>
</template>
