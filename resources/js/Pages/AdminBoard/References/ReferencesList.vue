<script setup>
import { inject, onMounted, ref } from "vue";
import axios from "axios";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import toastService from "@/Services/toastService";
import ReferencesForm from "@/Pages/AdminBoard/References/ReferencesForm.vue";
import { useDialog } from "primevue/usedialog";
import { FilterMatchMode } from "primevue/api";
import InputText from "primevue/inputtext";
import Slider from "primevue/slider";
import Dropdown from "primevue/dropdown";
import ReferencesList from "@/Pages/AdminBoard/References/ReferencesList.vue";

const dialog = useDialog();

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const referenceData = ref(null);
const tableColumns = ref(null);
const filters = ref(null);

const getTableData = () => {
    axios
        .get(route(`api.${props.id}.properties`))
        .then((response) => {
            if (response.data) {
                tableColumns.value = response.data.data;
                filters.value = response.data.filters;
                for (const key in filters.value) {
                    filters.value[key].matchMode =
                        FilterMatchMode[filters.value[key].matchMode];
                }
            }
        })
        .then(() => {
            for (const field of tableColumns.value) {
                field.value = null;
                if (field.type === "select") {
                    field.values = null;
                    axios
                        .get(`${route(`api.${field.source}.index`)}?limit=all`)
                        .then((response) => {
                            if (response.data) {
                                field.values = response.data.data.map(
                                    (item) => item.name
                                );
                            }
                        });
                }
            }
        })
        .then(() => {
            axios.get(route(`api.${props.id}.index`)).then((response) => {
                if (response.data.length != 0)
                    referenceData.value = response.data.data;
            });
        });
};

onMounted(() => {
    getTableData();
});

const editClickHandler = (data) => {
    dialogRef.value.close();
    dialog.open(ReferencesForm, {
        data: { id: props.id, name: props.name, item: data.id, edit: true },
        props: {
            modal: true,
            header: "Редактирование",
            draggable: false,
            contentClass: "reference-form",
        },
        onClose: () => {
            dialog.open(ReferencesList, {
                data: {
                    id: props.id,
                    name: props.name,
                    editable: props.editable,
                    expandable: props.expandable,
                },
                props: {
                    modal: true,
                    header: "Просмотр",
                    draggable: false,
                },
            });
        },
    });
};

const deleteClickHandler = (data) => {
    axios
        .delete(route(`api.${props.id}.destroy`, data.id))
        .then(() => {
            getTableData();
            toastService.showSuccessToast(
                "Успешное удаления",
                "Сведения были удалены из системы"
            );
        })
        .catch(() => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Возможно имеются связанные данные, проверьте и повторите попытку позднее"
            );
        });
};

const addClickHandle = () => {
    dialogRef.value.close();
    dialog.open(ReferencesForm, {
        data: { id: props.id, name: props.name, edit: false },
        props: {
            modal: true,
            header: "Создание",
            draggable: false,
            contentClass: "reference-form",
        },
        onClose: () => {
            dialog.open(ReferencesList, {
                data: {
                    id: props.id,
                    name: props.name,
                    editable: props.editable,
                    expandable: props.expandable,
                },
                props: {
                    modal: true,
                    header: "Просмотр",
                    draggable: false,
                },
            });
        },
    });
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-lg text-800 leading-tight">
                {{ props.name }}
            </h3>
            <Button
                v-if="props.expandable"
                class="mt-3"
                label="Добавить"
                @click="addClickHandle"
            />
        </div>

        <div>
            <div v-if="referenceData">
                <DataTable
                    v-model:filters="filters"
                    filterDisplay="menu"
                    scrollable
                    removableSort
                    :value="referenceData"
                >
                    <Column
                        v-for="(item, index) in tableColumns"
                        :key="index"
                        :field="item.key"
                        :header="item.title"
                        :sortable="item.sortable"
                        :showFilterMatchModes="false"
                    >
                        <template
                            v-if="filters[item.key]"
                            #filter="{ filterModel }"
                        >
                            <template v-if="item.type === 'text'">
                                <InputText
                                    v-model="filterModel.value"
                                    type="text"
                                    class="p-column-filter"
                                    placeholder="Поиск по названию"
                                />
                            </template>

                            <template v-else-if="item.type === 'select'">
                                <Dropdown
                                    v-model="filterModel.value"
                                    :options="item.values"
                                    class="p-column-filter"
                                    showClear
                                >
                                </Dropdown>
                            </template>
                        </template>

                        <template #body="{ data }">
                            <template v-if="item.type === 'text'">
                                {{ data[item.key] }}
                            </template>

                            <template v-if="item.type === 'select'">
                                {{ data[item.key] }}
                            </template>

                            <template v-if="item.type === 'number'">
                                {{ item.inputProperties.prefix }}
                                {{ data[item.key] }}
                                {{ item.inputProperties.suffix }}
                            </template>

                            <img
                                v-if="item.type === 'image'"
                                class="max-h-16 max-w-16"
                                :src="data[item.key]"
                                alt=""
                            />

                            <div
                                v-else-if="item.type === 'color'"
                                class="w-6 h-6 rounded-md"
                                :style="{
                                    'background-color': '#' + data[item.key],
                                }"
                            ></div>
                        </template>
                    </Column>

                    <Column v-if="props.editable">
                        <template #body="{ data }">
                            <Button
                                @click="editClickHandler(data)"
                                type="button"
                                severity="secondary"
                                icon="pi pi-pencil"
                                rounded
                            />
                        </template>
                    </Column>

                    <Column v-if="props.expandable">
                        <template #body="{ data }">
                            <Button
                                @click="deleteClickHandler(data)"
                                type="button"
                                severity="danger"
                                icon="pi pi-trash"
                                rounded
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
            <Skeleton v-else width="100%"></Skeleton>
        </div>
    </div>
</template>
