<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import ReferencesList from "@/Pages/AdminBoard/References/ReferencesList.vue";
import ReferencesForm from "@/Pages/AdminBoard/References/ReferencesForm.vue";
import { useDialog } from "primevue/usedialog";
const dialog = useDialog();

defineProps({
    references: Array,
});

const showClickHandle = (item) => {
    dialog.open(ReferencesList, {
        data: {
            id: item.id,
            name: item.name,
            editable: item.editable,
            expandable: item.expandable,
        },
        props: {
            modal: true,
            header: "Просмотр",
            draggable: false,
            contentClass: "reference-list",
        },
    });
};
const addClickHandle = (item) => {
    dialog.open(ReferencesForm, {
        data: { id: item.id, name: item.name, edit: false },
        props: {
            modal: true,
            header: "Создание",
            draggable: false,
            contentClass: "reference-form",
        },
    });
};
</script>

<template>
    <Head title="Админ-панель" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-800 leading-tight">
                Админ-панель
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-4 gap-4 p-4 sm:p-8">
                    <div
                        class="bg-white shadow-md rounded-lg p-4 flex flex-col gap-2"
                        v-for="(item, index) in references"
                        :key="index"
                    >
                        <h3 class="text-xl font-semibold">{{ item.name }}</h3>

                        <div
                            class="cursor-pointer flex items-center gap-1 text-lg"
                            @click="showClickHandle(item)"
                        >
                            <i class="pi pi-list"></i>
                            <span>Просмотреть</span>
                        </div>

                        <div
                            class="cursor-pointer flex items-center gap-1 text-lg"
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
