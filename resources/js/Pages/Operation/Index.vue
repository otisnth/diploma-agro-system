<script setup>
import { onMounted, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import Skeleton from "primevue/skeleton";
import SelectButton from "primevue/selectbutton";
import toastService from "@/Services/toastService";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from "primevue/api";

const props = defineProps({
    operations: Array,
    operationStatuses: Array,
});

const operationNotes = ref(null);
const countNotes = ref(0);

const activePage = ref(0);
const activeSort = ref(null);

const selectedStatuses = ref([]);

const filters = ref({});

onMounted(() => {
    fetchOperationNotes();
});

const initFilters = () => {
    filters.value = {
        status: { value: null, matchMode: FilterMatchMode.IN },
    };
};

initFilters();

const initSort = () => {
    activeSort.value = {
        field: "start_date",
        direction: "asc",
    };
};

initSort();

const fetchOperationNotes = (filters = []) => {
    axios
        .post("/api/operation-notes/search", {
            filters: [...filters],
            sort: [activeSort.value],
            includes: [{ relation: "field" }, { relation: "createdBy" }],
            page: activePage.value + 1,
        })
        .then((response) => {
            operationNotes.value = response.data.data;

            countNotes.value = response.data.meta.total;
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
    fetchOperationNotes();
};

const deleteFieldHandler = () => {
    selectedFields.value = {};
    fetchFields();
};

function pad(num) {
    return (num < 10 ? "0" : "") + num;
}

function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();

    const formattedDate = pad(day) + "." + pad(month) + "." + year;

    return formattedDate;
}

watch(
    activeSort,
    () => {
        activePage.value = 0;
        fetchOperationNotes();
    },
    { deep: true }
);

watch(
    () => filters.value,
    () => {
        let orionFilters = [];
        for (const key in filters.value) {
            if (filters.value[key].value) {
                if (
                    filters.value[key].matchMode == FilterMatchMode.IN &&
                    filters.value[key].value.length
                ) {
                    orionFilters.push({
                        field: key,
                        operator: "in",
                        value: filters.value[key].value,
                    });
                }
                // orionFilters.push({
                //     field: key,
                //     operator: "ilike",
                //     value: `%${filters.value[key].value}%`,
                // });
            }
        }
        activePage.value = 0;
        fetchOperationNotes(orionFilters);
    },
    { deep: true }
);

watch(
    () => selectedStatuses.value,
    () => {
        console.log("status");
        filters.value.status.value = selectedStatuses.value;
    }
);
</script>

<template>
    <Head title="Мероприятия" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Мероприятия
                </h2>
                <Link :href="route('operation.create')">
                    <Button label="Запланировать мероприятие" />
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 bg-white shadow-md min-h-screen"
            >
                <div class="flex flex-col gap-2 pt-2">
                    <label class="text-lg font-semibold">
                        Статус мероприятия
                    </label>

                    <SelectButton
                        v-model="selectedStatuses"
                        :options="props.operationStatuses"
                        optionLabel="name"
                        optionValue="id"
                        multiple
                    />
                </div>

                <DataTable
                    v-model:filters="filters"
                    :value="operationNotes"
                    filterDisplay="menu"
                    @sort="sortHandler"
                    @page="pageHandler"
                    removableSort
                >
                    <template #empty>Мероприятия не найдены</template>

                    <Column field="start_date" header="Дата начала" sortable>
                        <template #body="slotProps">
                            <span v-if="slotProps.data.start_date">{{
                                formatDate(slotProps.data.start_date)
                            }}</span>
                            <span v-else>Не назначена</span>
                        </template>
                    </Column>

                    <Column field="end_Date" header="Дата окончания" sortable>
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.end_Date) }}
                        </template>
                    </Column>

                    <Column
                        field="note_operation.name"
                        header="Мероприятие"
                    ></Column>

                    <Column field="field.name" header="Участок"></Column>

                    <Column
                        field="created_by.name"
                        header="Ответственный"
                    ></Column>

                    <Column header="Действия">
                        <template #body="{}">
                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    severity="success"
                                    icon="pi pi-info-circle"
                                    rounded
                                />

                                <Button
                                    type="button"
                                    severity="danger"
                                    icon="pi pi-trash"
                                    rounded
                                />
                            </div>
                        </template>
                    </Column>

                    <template #footer>
                        <Paginator
                            :rows="15"
                            :totalRecords="countNotes"
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
