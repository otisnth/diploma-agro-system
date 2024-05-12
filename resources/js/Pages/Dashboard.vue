<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Главная" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight">Главная</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-neutral-700 shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">You're logged in!</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
