<script setup>
import { onMounted, ref, watch, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import InputText from "primevue/inputtext";
import Skeleton from "primevue/skeleton";
import toastService from "@/Services/toastService";
import axios from "axios";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { FilterMatchMode } from "primevue/api";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
    posts: Array,
});

const personals = ref(null);
const countPersonals = ref(0);

const deletePersonalId = ref(null);

const activeTab = ref(props.posts[0]);
const activePage = ref(0);
const activeSort = ref(null);

const filters = ref({});

onMounted(() => {
    fetchPersonal();
});

const initFilters = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
        email: { value: null, matchMode: FilterMatchMode.CONTAINS },
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
                ...orionFilters.value.mainFilters,
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

const detailHandler = (data) => {
    router.visit(`/personal/${data.id}`);
};

const pageHandler = ({ page }) => {
    activePage.value = page;
    fetchPersonal();
};

const orionFilters = computed(() => {
    let mainFilters = [];
    for (const key in filters.value) {
        if (filters.value[key].value) {
            mainFilters.push({
                field: key,
                operator: "ilike",
                value: `%${filters.value[key].value}%`,
            });
        }
    }
    return { mainFilters };
});

watch(
    activeSort,
    () => {
        activePage.value = 0;
        fetchPersonal();
    },
    { deep: true }
);

watch(
    () => filters.value,
    () => {
        activePage.value = 0;
        fetchPersonal();
    },
    { deep: true }
);

const confirmPersonalDelete = (data) => {
    deletePersonalId.value = data.id;
    confirm.require({
        message:
            "Вы действительно хотите удалить учетную запись данного сотрудника?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-danger",
        rejectLabel: "Отмена",
        acceptLabel: "Удалить",
        accept: () => {
            axios
                .delete(route(`api.users.destroy`, deletePersonalId.value))
                .then(() => {
                    fetchPersonal();
                    toastService.showSuccessToast(
                        "Успешное удаления",
                        "Учетная запись сотрудника была успешно удалена"
                    );
                })
                .catch(() => {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Что-то пошло не так. Возможно имеются связанные данные, проверьте и повторите попытку позднее"
                    );
                });
        },
    });
};

const isShowDeleteBtn = computed(() => {
    return usePage().props.auth.user.post === "owner";
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
                    <Button label="Зарегистрировать сотрудника" />
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 bg-white shadow-md min-h-screen"
            >
                <div class="flex gap-2 pt-2">
                    <Button
                        v-for="item in posts"
                        :key="item.id"
                        :label="item.name"
                        @click="toggleActiveTab(item)"
                        :severity="item == activeTab ? '' : 'secondary'"
                    />
                </div>

                <DataTable
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
                            />
                        </template>
                    </Column>
                    <Column
                        field="email"
                        :showFilterMatchModes="false"
                        header="Email"
                        sortable
                    >
                        <template #filter="{ filterModel }">
                            <InputText
                                v-model="filterModel.value"
                                type="text"
                                class="p-column-filter"
                                placeholder="Поиск по email"
                            />
                        </template>
                    </Column>
                    <Column
                        v-if="activeTab.id == 'worker'"
                        field="status"
                        header="Статус"
                        sortable
                    ></Column>
                    <Column header="Действия" v-if="isShowDeleteBtn">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button
                                    @click="detailHandler(data)"
                                    type="button"
                                    severity="success"
                                    icon="pi pi-info-circle"
                                    rounded
                                />

                                <Button
                                    type="button"
                                    @click="confirmPersonalDelete(data)"
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
                            :totalRecords="countPersonals"
                            @page="pageHandler"
                        >
                        </Paginator>
                    </template>
                </DataTable>
            </div>
        </div>
        <ConfirmDialog></ConfirmDialog>
    </AuthenticatedLayout>
</template>

<style lang="stylus">
.p-column-filter-menu
    margin-left 0
</style>
