<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";

defineProps({});
</script>

<template>
    <Head title="Участки" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Участки
                </h2>
                <Link :href="route('field.create')">
                    <Button label="Добавить участок" />
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"></div>
        </div>
    </AuthenticatedLayout>
</template>
