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
const dialog = useDialog();

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const referenceData = ref(null);
const tableColumns = ref(null);

const getTableData = () => {
    axios
        .get(route(`api.${props.id}.properties`))
        .then((response) => {
            if (response.data) {
                tableColumns.value = response.data.data;
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
    dialog.open(ReferencesForm, {
        data: { id: props.id, name: props.name, item: data.id, edit: true },
        props: {
            modal: true,
            header: "Редактирование",
            draggable: false,
            contentClass: "reference-form",
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
</script>

<template>
    <div>
        <h3 class="font-semibold text-lg text-800 leading-tight">
            {{ props.name }}
        </h3>
        <div>
            <div v-if="referenceData">
                <DataTable scrollable removableSort :value="referenceData">
                    <Column
                        v-for="(item, index) in tableColumns"
                        :key="index"
                        :field="item.key"
                        :header="item.title"
                        :sortable="item.sortable"
                    >
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
