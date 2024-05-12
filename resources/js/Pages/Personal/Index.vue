<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";

defineProps({});
</script>

<template>
    <Head title="Сотрудники" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Сотрудники
                </h2>
                <Link :href="route('personal.create')">
                    <Button
                        label="Зарегистрировать сотрудника"
                        severity="contrast"
                        outlined
                    />
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"></div>
        </div>
    </AuthenticatedLayout>
</template>
