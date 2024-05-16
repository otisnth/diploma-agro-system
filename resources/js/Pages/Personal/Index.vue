<script setup>
import { onMounted, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from "primevue/api";

defineProps({});

const personals = ref(null);
const countPersonals = ref(0);

const filters = ref();

onMounted(() => {
    axios.get(route(`api.users.index`)).then((response) => {
        if (response.data) {
            personals.value = response.data.data;
            countPersonals.value = response.data.meta.total;
            console.log(countPersonals.value);
        }
    });
});

const initFilters = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

initFilters();

const fetchPersonal = () => {
    axios
        .post("/api/users/search", {
            // filters: [{ field: "post_id", operator: "=", value: "" }],
            // sort: [{ field: "name", direction: "asc" }],
            // includes: [{ relation: "" }],
        })
        .then((response) => {
            personals.value = response.data.data;

            countPersonals.value = response.data.meta.total;
        })
        .catch((error) => {});
};

const sortHandler = (e) => {
    console.log(e);
};

const pageHandler = ({ page }) => {
    console.log(e);
};
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

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <DataTable
                    class="shadow-md"
                    v-model:filters="filters"
                    :value="personals"
                    filterDisplay="menu"
                    @sort="sortHandler"
                    @page="pageHandler"
                    removableSort
                    :loading="loading"
                >
                    <template #empty> No customers found. </template>
                    <template #loading>
                        Loading customers data. Please wait.
                    </template>
                    <Column
                        field="name"
                        header="Полное имя"
                        :showFilterMatchModes="false"
                        sortable
                    >
                        <template #filter="{ filterModel }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Поиск по имени"
                            /> </template
                    ></Column>
                    <Column field="post_title" header="Должность" sortable>
                    </Column>
                    <Column field="status" header="Статус" sortable></Column>

                    <template #footer>
                        <Paginator
                            :rows="15"
                            :totalRecords="countPersonals"
                            @page="pageHandler"
                        >
                        </Paginator>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="stylus">
.p-column-filter-menu
    margin-left 0
</style>
