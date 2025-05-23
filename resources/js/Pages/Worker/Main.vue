<script setup>
import { onMounted, ref, watch, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import Skeleton from "primevue/skeleton";
import SelectButton from "primevue/selectbutton";
import MultiSelect from "primevue/multiselect";
import Checkbox from "primevue/checkbox";
import axios from "axios";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { FilterMatchMode } from "primevue/api";

const props = defineProps({
    operations: Array,
    operationStatuses: Array,
});

const operationNotes = ref(null);
const countNotes = ref(0);

const activePage = ref(0);
const activeSort = ref(null);

const myNotes = ref(false);

const selectedStatuses = ref(["planned", "assigned", "inProgress"]);

const filters = ref({});

onMounted(() => {
    fetchOperationNotes([
        {
            field: "status",
            value: ["planned", "assigned", "inProgress"],
            operator: "in",
        },
    ]);
});

const initFilters = () => {
    filters.value = {
        status: {
            value: ["planned", "assigned", "inProgress"],
            matchMode: FilterMatchMode.IN,
        },
        operation: { value: null, matchMode: FilterMatchMode.IN },
        field_id: { value: null, matchMode: FilterMatchMode.CONTAINS },
        created_by: { value: null, matchMode: FilterMatchMode.CONTAINS },
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

const fetchOperationNotes = () => {
    axios
        .post("/api/operation-notes/search", {
            filters: [
                ...orionFilters.value.mainFilters,
                {
                    field: "workerUnits.worker_id",
                    operator: "=",
                    value: usePage().props.auth.user.id,
                },
            ],
            sort: [activeSort.value],
            includes: [
                { relation: "field", filters: orionFilters.value.fieldFilters },
                {
                    relation: "author",
                    filters: orionFilters.value.userFilters,
                },
            ],
            page: activePage.value + 1,
        })
        .then((response) => {
            operationNotes.value = response.data.data;

            for (const i of operationNotes.value) {
                if (!i.field_id) {
                    i.field_id = "Отсутствует";
                } else {
                    i.field_id = i.field.name;
                }

                if (myNotes.value) {
                    i.created_by = "MyNotes";
                } else {
                    i.created_by = i.author.name;
                }
            }

            countNotes.value = response.data.meta.total;
        })
        .catch((error) => {});
};

const orionFilters = computed(() => {
    let mainFilters = [];
    let fieldFilters = [];
    let userFilters = [];
    for (const key in filters.value) {
        if (filters.value[key].value) {
            if (
                filters.value[key].matchMode == FilterMatchMode.IN &&
                filters.value[key].value.length
            ) {
                mainFilters.push({
                    field: key,
                    operator: "in",
                    value: filters.value[key].value,
                });
            }
            if (key == "field_id") {
                if (filters.value[key].value == "Отсутствует") {
                    mainFilters.push({
                        field: "field_id",
                        operator: "=",
                        value: null,
                    });
                } else {
                    mainFilters.push({
                        field: "field.name",
                        operator: "ilike",
                        value: `%${filters.value[key].value}%`,
                    });
                }
            }
        }
    }
    return { mainFilters, fieldFilters, userFilters };
});

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

const detailHandler = (data) => {
    router.visit(`/operation/${data.id}`);
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
        activePage.value = 0;
        fetchOperationNotes();
    },
    { deep: true }
);

watch(
    () => myNotes.value,
    () => {
        if (myNotes.value) {
            filters.value.created_by.value = "MyNotes";
        } else {
            filters.value.created_by.value = null;
        }
    }
);

watch(
    () => selectedStatuses.value,
    () => {
        filters.value.status.value = selectedStatuses.value;
    }
);
</script>

<template>
    <Head title="Запланированные мероприятия" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="font-semibold text-xl text-800 leading-tight sm:text-2xl"
                >
                    Запланированные мероприятия
                </h2>
            </div>
        </template>

        <div class="py-2">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 bg-white shadow-md min-h-screen"
            >
                <div class="flex flex-col gap-2 pt-2 pl-2 sm:pl-0">
                    <label class="text-lg font-semibold">
                        Статус мероприятия
                    </label>

                    <SelectButton
                        class="hidden sm:block"
                        v-model="selectedStatuses"
                        :options="props.operationStatuses"
                        optionLabel="name"
                        optionValue="id"
                        multiple
                    />
                    <MultiSelect
                        class="sm:hidden mr-2"
                        v-model="selectedStatuses"
                        :options="props.operationStatuses"
                        optionLabel="name"
                        optionValue="id"
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
                            <span v-if="slotProps.data.start_date">
                                {{ formatDate(slotProps.data.start_date) }}
                            </span>
                            <span v-else>Не назначена</span>
                        </template>
                    </Column>

                    <Column field="end_date" header="Дата окончания" sortable>
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.end_Date) }}
                        </template>
                    </Column>

                    <Column
                        field="operation"
                        header="Мероприятие"
                        :showFilterMatchModes="false"
                    >
                        <template #body="slotProps">
                            <span>
                                {{ slotProps.data.note_operation.name }}
                            </span>
                        </template>
                        <template #filter="{ filterModel }">
                            <MultiSelect
                                v-model="filterModel.value"
                                :options="props.operations"
                                optionLabel="name"
                                optionValue="id"
                                filter
                                class="p-column-filter w-60"
                            />
                        </template>
                    </Column>

                    <Column
                        field="field_id"
                        header="Участок"
                        :showFilterMatchModes="false"
                    >
                        <template #body="slotProps">
                            <span v-if="slotProps.data.field">
                                {{ slotProps.data.field.name }}
                            </span>
                        </template>

                        <template #filter="{ filterModel }">
                            <div class="flex items-center">
                                <Checkbox
                                    v-model="filterModel.value"
                                    inputId="empty"
                                    name="empty"
                                    value="Отсутствует"
                                />
                                <label for="empty" class="ml-2">
                                    Отсутствует
                                </label>
                            </div>
                            <InputText
                                v-model="filterModel.value"
                                :disabled="filterModel.value == 'Отсутствует'"
                                type="text"
                                class="p-column-filter mt-1"
                                placeholder="Поиск по названию"
                            />
                        </template>
                    </Column>

                    <Column
                        field="created_by"
                        header="Ответственный"
                        v-if="!myNotes"
                    >
                        <template #body="slotProps">
                            <span>
                                {{ slotProps.data.author.name }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Действия">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    @click="detailHandler(data)"
                                    type="button"
                                    severity="success"
                                    icon="pi pi-info-circle"
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
