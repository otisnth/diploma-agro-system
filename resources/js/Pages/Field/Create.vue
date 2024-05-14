<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";

defineProps({});
</script>

<template>
    <Head title="Добавление участка" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-800 leading-tight">
                Добавление участка
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-md sm:rounded-lg">
                    <div class="p-6">Форма</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
