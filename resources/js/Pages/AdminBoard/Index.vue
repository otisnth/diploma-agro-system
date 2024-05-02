<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";

defineProps({
    references: Array,
});

onMounted(() => {
    axios
        .get(route("api.plants.index"))
        .then((response) => {
            console.log(response);
        })
        .catch((error) => {
            console.log(error);
        });
});
</script>

<template>
    <Head title="Админ-панель" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-800 leading-tight">
                Админ-панель
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <p class="p-4 sm:p-8 text-gray-500">Admin</p>
                </div>

                <div class="p-4 sm:p-8 shadow sm:rounded-lg">
                    {{ references }}
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <Button label="Оформить заказ" />
                </div>

                <Button label="Оформить заказ" severity="secondary" />

                <div class="grid grid-cols-4">
                    <div v-for="(item, index) in references" :key="index">
                        {{ item.name }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
