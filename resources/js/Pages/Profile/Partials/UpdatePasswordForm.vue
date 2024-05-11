<script setup>
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
            }
            if (form.errors.current_password) {
                form.reset("current_password");
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Изменить пароль</h2>

            <p class="mt-1 text-sm">
                Чтобы обеспечить безопасность, в вашей учетной записи
                используйте длинный случайный пароль.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <label for="current_password">Текущий пароль</label>
                <InputText
                    id="current_password"
                    ref="currentPasswordInput"
                    class="mt-1 block w-full"
                    v-model="form.current_password"
                    type="password"
                    aria-describedby="current_password-help"
                />
                <small id="current_password-help" class="text-sm text-red-600">
                    {{ form.errors.current_password }}
                </small>
            </div>

            <div>
                <label for="password">Новый пароль</label>
                <InputText
                    id="password"
                    ref="passwordInput"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    type="password"
                    aria-describedby="password-help"
                />
                <small id="password-help" class="text-sm text-red-600">
                    {{ form.errors.password }}
                </small>
            </div>

            <div>
                <label for="password_confirmation">Повтор пароля</label>
                <InputText
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    type="password"
                    aria-describedby="password_confirmation-help"
                />
                <small
                    id="password_confirmation-help"
                    class="text-sm text-red-600"
                >
                    {{ form.errors.password_confirmation }}
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
                        Пароль изменен
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
