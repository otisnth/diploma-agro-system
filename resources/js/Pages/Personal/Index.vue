<script setup>
import { onMounted, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import Skeleton from "primevue/skeleton";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from "primevue/api";

const props = defineProps({
    posts: Array,
});

const personals = ref(null);
const countPersonals = ref(0);

const activeTab = ref(props.posts[0]);
const activePage = ref(0);
const activeSort = ref(null);

const filters = ref();

onMounted(() => {
    fetchPersonal();
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

const toggleActiveTab = (item) => {
    activePage.value = 0;
    activeTab.value = item;
    initSort();
    fetchPersonal();
};

const fetchPersonal = () => {
    axios
        .post("/api/users/search", {
            filters: [
                { field: "post", operator: "=", value: activeTab.value.id },
            ],
            sort: [activeSort.value],
            // includes: [{ relation: "" }],
            page: activePage.value + 1,
        })
        .then((response) => {
            personals.value = response.data.data;

            countPersonals.value = response.data.meta.total;
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
};

watch(
    activeSort,
    () => {
        activePage.value = 0;
        fetchPersonal();
    },
    { deep: true }
);
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
                    <Button label="Зарегистрировать сотрудника" />
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="flex gap-2">
                    <Button
                        v-for="item in posts"
                        :key="item.id"
                        :label="item.name"
                        @click="toggleActiveTab(item)"
                        :severity="item == activeTab ? '' : 'secondary'"
                    />
                </div>

                <DataTable
                    class="shadow-md"
                    v-model:filters="filters"
                    :value="personals"
                    filterDisplay="menu"
                    @sort="sortHandler"
                    @page="pageHandler"
                    removableSort
                >
                    <template #empty>Сотрудники не найдены</template>
                    <!-- <template #loading>
                        <Skeleton width="100%" height="100%"></Skeleton>
                    </template> -->
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
