<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import Button from "primevue/button";

defineProps({});
</script>

<template>
    <Head title="Мероприятия" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Мероприятия
                </h2>
                <Link :href="route('operation.create')">
                    <Button label="Запланировать мероприятие" />
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex bg-white shadow-md gap-2 min-h-screen"
            ></div>
        </div>
    </AuthenticatedLayout>
</template>
