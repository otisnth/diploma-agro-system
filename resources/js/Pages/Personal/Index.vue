<script setup>
import { onMounted, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";

defineProps({});

const personals = ref(null);

onMounted(() => {
    axios.get(route(`api.users.index`)).then((response) => {
        if (response.data) {
            personals.value = response.data.data;
        }
    });
});
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
                        plain
                        text
                        raised
                    />
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <DataTable :value="personals" paginator :rows="15">
                    <Column field="name" header="Полное имя" sortable> </Column>
                    <Column field="post_title" header="Должность" sortable>
                    </Column>
                    <Column field="status" header="Статус" sortable> </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
