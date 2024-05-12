<script setup>
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Личная информация</h2>

            <p class="mt-1 text-sm">
                Обновите информацию профиля и адрес электронной почты.
            </p>

            <p class="mt-2 text-lg">
                Ваша должность -
                {{ user.post_title }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
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

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    label="Сохранить"
                />

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                    class="transition ease-in-out"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm">
                        Данные обновлены
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
