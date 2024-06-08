<script setup>
import { onMounted, ref, watch } from "vue";
import PreviewField from "@/Pages/Field/Partials/PreviewField.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import Skeleton from "primevue/skeleton";
import toastService from "@/Services/toastService";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from "primevue/api";

const props = defineProps({
    posts: Array,
});

const fields = ref(null);
const countFields = ref(0);

const activePage = ref(0);
const activeSort = ref(null);

const filters = ref({});

const selectedFields = ref({});

onMounted(() => {
    fetchFields();
});

const initFilters = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

initFilters();

const initSort = () => {
    activeSort.value = {
        field: "name",
        direction: "asc",
    };
};

initSort();

const fetchFields = (filters = []) => {
    axios
        .post("/api/fields/search", {
            filters: [...filters],
            sort: [activeSort.value],
            includes: [{ relation: "sort" }, { relation: "sort.plant" }],
            page: activePage.value + 1,
        })
        .then((response) => {
            fields.value = response.data.data;

            countFields.value = response.data.meta.total;
        })
        .catch((error) => {});
};

const sortHandler = ({ sortField, sortOrder }) => {
    if (!sortField) {
        initSort();
        return;
    }
    activeSort.value = {
        field: sortField,
        direction: sortOrder > 0 ? "asc" : "desc",
    };
};

const pageHandler = ({ page }) => {
    activePage.value = page;
    fetchFields();
};

watch(
    activeSort,
    () => {
        activePage.value = 0;
        fetchFields();
    },
    { deep: true }
);

watch(
    () => filters.value,
    () => {
        let orionFilters = [];
        for (const key in filters.value) {
            if (filters.value[key].value) {
                orionFilters.push({
                    field: key,
                    operator: "ilike",
                    value: `%${filters.value[key].value}%`,
                });
            }
        }
        activePage.value = 0;
        fetchFields(orionFilters);
    },
    { deep: true }
);
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
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex bg-white shadow-md gap-2 min-h-screen"
            >
                <DataTable
                    class="w-1/2"
                    v-model:filters="filters"
                    v-model:selection="selectedFields"
                    selectionMode="single"
                    :value="fields"
                    filterDisplay="menu"
                    @sort="sortHandler"
                    @page="pageHandler"
                    removableSort
                >
                    <template #empty>Участки не найдены</template>
                    <Column
                        field="name"
                        header="Название"
                        :showFilterMatchModes="false"
                        sortable
                    >
                        <template #filter="{ filterModel }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Поиск по имени"
                            />
                        </template>
                    </Column>

                    <template #footer>
                        <Paginator
                            :rows="15"
                            :totalRecords="countFields"
                            @page="pageHandler"
                        >
                        </Paginator>
                    </template>
                </DataTable>

                <PreviewField class="w-1/2" :field="selectedFields" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="stylus">
.p-column-filter-menu
    margin-left 0
</style>
