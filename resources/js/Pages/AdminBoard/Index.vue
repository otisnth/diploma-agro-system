<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import toastService from "@/Services/toastService";

defineProps({
    references: Array,
});

const showClickHandle = (item) => {
    console.log(item);
};
const addClickHandle = (item) => {
    console.log(item);
};
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
                <div class="grid grid-cols-4 gap-4 p-4 sm:p-8 text-black">
                    <div
                        class="bg-white rounded-lg p-4"
                        v-for="(item, index) in references"
                        :key="index"
                    >
                        <h3 class="font-semibold">{{ item.name }}</h3>

                        <div
                            class="cursor-pointer flex items-center gap-1"
                            @click="showClickHandle(item)"
                        >
                            <i class="pi pi-list"></i>
                            <span>Просмотреть</span>
                        </div>

                        <div
                            class="cursor-pointer flex items-center gap-1"
                            v-if="item.expandable"
                            @click="addClickHandle(item)"
                        >
                            <i class="pi pi-plus"></i>
                            <span>Добавить</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
