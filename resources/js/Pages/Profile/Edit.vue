<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const isShowDeleteBlock = computed(() => {
    return usePage().props.auth.user.post !== "worker";
});
</script>

<template>
    <Head title="Личный кабинет" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight">Личный кабинет</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow-md sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-md sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="p-4 sm:p-8 bg-white shadow-md sm:rounded-lg"
                    v-if="isShowDeleteBlock"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
