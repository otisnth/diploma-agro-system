<script setup>
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium">Удаление аккаунта</h2>

            <p class="mt-1 text-sm">
                После удаления вашей учетной записи все ее данные будут удалены
                без возможности восстановления.
            </p>
        </header>

        <Button
            @click="confirmUserDeletion"
            label="Удалить аккаунт"
            severity="danger"
        />

        <Dialog
            v-model:visible="confirmingUserDeletion"
            modal
            :draggable="false"
            header="Удаление аккаунта"
            class="w-1/3"
            @hide="closeModal"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium">
                    Вы уверены, что хотите удалить свою учетную запись?
                </h2>

                <p class="mt-1 text-sm">
                    После удаления вашей учетной записи все ее данные будут
                    удалены безвозвратно. Пожалуйста, введите свой пароль, чтобы
                    подтвердить, что вы хотите окончательно удалить свою учетную
                    запись.
                </p>

                <div class="mt-6">
                    <InputText
                        id="password"
                        ref="passwordInput"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        type="password"
                        placeholder="Пароль"
                        aria-describedby="password-help"
                        @keyup.enter="deleteUser"
                    />
                    <small id="password-help" class="text-sm text-red-600">
                        {{ form.errors.password }}
                    </small>
                </div>

                <div class="mt-6 flex justify-end">
                    <Button
                        label="Отмена"
                        @click="closeModal"
                        severity="secondary"
                    />

                    <Button
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                        label="Удалить аккаунт"
                        severity="danger"
                    />
                </div>
            </div>
        </Dialog>
    </section>
</template>
