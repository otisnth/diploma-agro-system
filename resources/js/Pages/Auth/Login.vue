<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Авторизация" />

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

            <div class="mt-4">
                <label for="password">Пароль</label>
                <InputText
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    aria-describedby="password-help"
                    required
                />
                <small id="password-help" class="text-sm text-red-600">
                    {{ form.errors.password }}
                </small>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Забыли пароль?
                </Link>

                <Button
                    class="ml-4"
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    label="Войти"
                />
            </div>
        </form>
    </GuestLayout>
</template>
