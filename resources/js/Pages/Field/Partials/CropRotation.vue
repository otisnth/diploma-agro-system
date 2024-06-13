<script setup>
import { ref, onMounted, watch } from "vue";
import InputNumber from "primevue/inputnumber";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import Button from "primevue/button";
import Calendar from "primevue/calendar";
import Dropdown from "primevue/dropdown";
import { FilterMatchMode, FilterOperator } from "primevue/api";
import { useForm } from "@inertiajs/vue3";
import toastService from "@/Services/toastService";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const props = defineProps({
    field_id: Number,
});

const form = useForm({
    start_date: "",
    end_date: "",
    sort_id: "",
});

const selectedCulture = ref();
const today = new Date();

const cropRotation = ref(null);
const countCropRotation = ref(0);
const cultureList = ref();
const sortList = ref();

const deleteCropRotationId = ref(null);

const activePage = ref(0);
const activeSort = ref(null);

const initSort = () => {
    activeSort.value = {
        field: "start_date",
        direction: "asc",
    };
};

initSort();

const fetchCropRotation = (filters = []) => {
    axios
        .post("/api/crop-rotations/search", {
            filters: [
                {
                    field: "field_id",
                    value: props.field_id,
                    matchMode: FilterMatchMode.EQUALS,
                },
            ],
            sort: [activeSort.value],
            includes: [{ relation: "sort" }, { relation: "sort.plant" }],
            page: activePage.value + 1,
        })
        .then((response) => {
            cropRotation.value = response.data.data;

            countCropRotation.value = response.data.meta.total;
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
    fetchCropRotation();
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

const fetchCultureList = () => {
    axios.get(route(`api.plants.index`)).then((response) => {
        if (response.data.length != 0) cultureList.value = response.data.data;
    });
};

const fetchSortList = () => {
    axios
        .post("/api/sorts/search", {
            filters: [
                {
                    field: "plant_id",
                    operator: "=",
                    value: selectedCulture.value,
                },
            ],
        })
        .then((response) => {
            if (response.data.length != 0) sortList.value = response.data.data;
        });
};

const confirmCropRotationDelete = (data) => {
    console.log("delete");
    deleteCropRotationId.value = data.id;
    confirm.require({
        message: "Вы действительно хотите удалить данную запись севооборота?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-danger",
        rejectLabel: "Отмена",
        acceptLabel: "Удалить",
        accept: () => {
            axios
                .delete(
                    route(
                        `api.crop-rotations.destroy`,
                        deleteCropRotationId.value
                    )
                )
                .then(() => {
                    fetchCropRotation();
                    toastService.showSuccessToast(
                        "Успешное удаления",
                        "Запись севооборота была успешно удалена"
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

const createCropRotation = () => {
    if (!form.start_date) {
        toastService.showErrorToast("Ошибка", "Введите дату посева");
        return;
    }

    if (!form.end_date) {
        toastService.showErrorToast("Ошибка", "Введите дату уборки");
        return;
    }

    if (form.end_date <= form.start_date) {
        toastService.showErrorToast(
            "Ошибка",
            "Дата уборки не может быть раньше или совпадать с датой посадки"
        );
        return;
    }

    if (!form.sort_id) {
        toastService.showErrorToast("Ошибка", "Выберите сорт растения");
        return;
    }

    form.field_id = props.field_id;

    axios
        .post(route("api.crop-rotations.store"), form, {
            onFinish: () => {
                form.reset();
                selectedCulture.value = null;
            },
        })
        .then(() => {
            toastService.showSuccessToast(
                "Успешное добавление",
                "Запись севооборота добавлен в систему"
            );
            fetchCropRotation();
        })
        .catch((e) => {
            if (e?.response?.data?.error) {
                toastService.showErrorToast("Ошибка", e.response.data.error);
            } else {
                toastService.showErrorToast(
                    "Ошибка",
                    "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                );
            }
        });
};

onMounted(() => {
    fetchCropRotation();
    fetchCultureList();
});

watch(
    activeSort,
    () => {
        activePage.value = 0;
        fetchCropRotation();
    },
    { deep: true }
);

watch(
    () => selectedCulture.value,
    () => {
        fetchSortList();
    }
);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="bg-green-50 rounded-lg p-4 border-2">
            <span class="text-lg font-semibold">Добавление записи</span>
            <div class="flex gap-2 justify-between mt-2">
                <div class="flex flex-col">
                    <label for="">Дата посева</label>
                    <Calendar
                        v-model="form.start_date"
                        class="mt-1"
                        id="startDate"
                        showIcon
                        iconDisplay="input"
                        required
                        :maxDate="today"
                    />
                </div>

                <div class="flex flex-col">
                    <label for="">Дата уборки</label>
                    <Calendar
                        v-model="form.end_date"
                        class="mt-1"
                        id="endDate"
                        showIcon
                        iconDisplay="input"
                        required
                        :maxDate="today"
                    />
                </div>

                <div class="flex flex-col flex-grow">
                    <label for="culture"> Культура </label>
                    <Dropdown
                        id="culture"
                        v-model="selectedCulture"
                        :options="cultureList"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Выберите культуру"
                        class="mt-1"
                        required
                    />
                </div>

                <div class="flex flex-col flex-grow">
                    <label for="sort"> Сорт </label>
                    <Dropdown
                        id="sort"
                        v-model="form.sort_id"
                        :options="sortList"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Выберите сорт"
                        class="mt-1"
                        required
                        :disabled="!selectedCulture"
                    />
                </div>

                <Button
                    class="self-end"
                    @click="createCropRotation"
                    label="Создать"
                />
            </div>
        </div>

        <DataTable
            :value="cropRotation"
            filterDisplay="menu"
            @sort="sortHandler"
            @page="pageHandler"
            removableSort
        >
            <template #empty>Записи не найдены</template>
            <Column field="start_date" header="Дата посева" sortable>
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.start_date) }}
                </template>
            </Column>

            <Column field="end_date" header="Дата уборки" sortable>
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.end_date) }}
                </template>
            </Column>

            <Column field="sort.plant.name" header="Культура"> </Column>

            <Column field="sort.name" header="Сорт"> </Column>

            <Column header="Действия">
                <template #body="{ data }">
                    <Button
                        class="ml-5"
                        type="button"
                        @click="confirmCropRotationDelete(data)"
                        severity="danger"
                        icon="pi pi-trash"
                        rounded
                    />
                </template>
            </Column>

            <template #footer>
                <Paginator
                    :rows="7"
                    :totalRecords="countCropRotation"
                    @page="pageHandler"
                >
                </Paginator>
            </template>
        </DataTable>
        <ConfirmDialog></ConfirmDialog>
    </div>
</template>
