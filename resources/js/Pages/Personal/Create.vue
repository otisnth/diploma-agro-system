<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import toastService from "@/Services/toastService";

defineProps({
    posts: Array,
});

const form = useForm({
    name: "",
    email: "",
    post: null,
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    axios
        .post(route("addPersonal"), form, {
            onFinish: () => form.reset(),
        })
        .then(() => {
            toastService.showSuccessToast(
                "Успешная регистрация",
                "Учетная запись пользователя добавлена в систему"
            );
            router.visit("/personal");
        })
        .catch((e) => {
            for (const key in e.response.data.errors) {
                toastService.showErrorToast(
                    "Ошибка",
                    e.response.data.errors[key][0]
                );
            }
        });
};
</script>

<template>
    <Head title="Добавление сотрудника" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Добавление сотрудника
                </h2>
                <Link :href="route('personal.index')">
                    <Button label="К списку" plain text raised />
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    class="overflow-hidden p-6 bg-white shadow-md sm:rounded-lg"
                >
                    <p class="mt-1 text-sm max-w-xl">
                        Заполните информацию профиля и создайте учетную запись
                        сотрудника. После регистрации сообщите сотруднику данные
                        для авторизации.
                    </p>

                    <form @submit.prevent="submit" class="mt-4 max-w-xl">
                        <input
                            type="hidden"
                            name="_token"
                            value="{{ csrf_token() }}"
                        />
                        <div>
                            <label for="name">Полное имя</label>
                            <InputText
                                id="name"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                            />
                        </div>

                        <div class="mt-4">
                            <label for="email">Email</label>
                            <InputText
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                            />
                        </div>

                        <div class="mt-4">
                            <label for="email">Должность</label>
                            <Dropdown
                                v-model="form.post"
                                :options="posts"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Выберите должность"
                                class="mt-1 w-full md:w-14rem"
                                required
                            />
                        </div>

                        <div class="mt-4">
                            <label for="password">Пароль</label>
                            <InputText
                                id="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                type="password"
                                required
                            />
                        </div>

                        <div class="mt-4">
                            <label for="password_confirmation"
                                >Повтор пароля</label
                            >
                            <InputText
                                id="password_confirmation"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                            />
                        </div>

                        <div class="flex items-center justify-start mt-4">
                            <Button
                                type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                label="Зарегистрировать"
                            />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
