<script setup>
import { onMounted, ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, Link, router } from "@inertiajs/vue3";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";
import toastService from "@/Services/toastService";
import Map from "@/Components/Map.vue";
import CropRotation from "@/Pages/Field/Partials/CropRotation.vue";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import InputNumber from "primevue/inputnumber";
import InlineMessage from "primevue/inlinemessage";
import Textarea from "primevue/textarea";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Tag from "primevue/tag";
import CascadeSelect from "primevue/cascadeselect";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const confirm = useConfirm();

const props = defineProps({
    noteId: String,
});

const workerUnits = ref(null);
const newWorkerUnit = ref({
    worker_id: null,
    technic: null,
    equipments: [{}],
});
const isLoaded = ref(false);
const workersList = ref();
const technicList = ref();
const equipmentList = ref();

const fetchWorkersList = () => {
    axios
        .post("/api/users/search", {
            filters: [{ field: "post", operator: "=", value: "worker" }],
            limit: "all",
        })
        .then((response) => {
            workersList.value = response.data.data;
        })
        .catch((error) => {});
};

const fetchTechnicTypeList = () => {
    axios.get(route(`api.technic-types.index`)).then((response) => {
        if (response.data.length != 0) {
            technicList.value = response.data.data;
            for (const i in technicList.value) {
                technicList.value[i].models = [{}];
            }
        }
    });
};

const fetchEquipmnetTypeList = () => {
    axios.get(route(`api.equipment-types.index`)).then((response) => {
        if (response.data.length != 0) {
            equipmentList.value = response.data.data;
            for (const i in equipmentList.value) {
                equipmentList.value[i].models = [{}];
            }
        }
    });
};

const technicGroupChange = (value) => {
    if (value.value?.models) {
        axios
            .post("/api/technic-models/search", {
                filters: [
                    {
                        field: "type_id",
                        operator: "=",
                        value: value.value.id,
                    },
                ],
            })
            .then((response) => {
                if (response.data.data.length != 0) {
                    value.value.models = response.data.data;
                    for (const i in value.value.models) {
                        value.value.models[i].technics = [{}];
                    }
                } else {
                    value.value.models[0] = {
                        id: null,
                        name: "Модели данного типа отсутствуют",
                    };
                }
            })
            .catch((error) => {});
    } else if (value.value?.technics) {
        axios
            .post("/api/technics/search?limit=all", {
                filters: [
                    {
                        field: "model_id",
                        operator: "=",
                        value: value.value.id,
                    },
                ],
            })
            .then((response) => {
                if (response.data.data.length != 0) {
                    value.value.technics = response.data.data;
                    for (const i in value.value.technics) {
                        value.value.technics[i].name =
                            value.value.technics[i].license_plate;
                    }
                } else {
                    value.value.technics[0] = {
                        id: null,
                        name: "Техника этой модели отсутствует",
                    };
                }
            })
            .catch((error) => {});
    }
};

const equipmentGroupChange = (value) => {
    if (value.value?.models) {
        axios
            .post("/api/equipment-models/search", {
                filters: [
                    {
                        field: "type_id",
                        operator: "=",
                        value: value.value.id,
                    },
                ],
            })
            .then((response) => {
                if (response.data.data.length != 0) {
                    value.value.models = response.data.data;
                    for (const i in value.value.models) {
                        value.value.models[i].equipments = [{}];
                    }
                } else {
                    value.value.models[0] = {
                        id: null,
                        name: "Модели данного типа отсутствуют",
                    };
                }
            })
            .catch((error) => {});
    } else if (value.value?.equipments) {
        axios
            .post("/api/equipments/search?limit=all", {
                filters: [
                    {
                        field: "model_id",
                        operator: "=",
                        value: value.value.id,
                    },
                ],
            })
            .then((response) => {
                if (response.data.data.length != 0) {
                    value.value.equipments = response.data.data;
                    for (const i in value.value.equipments) {
                        value.value.equipments[i].name =
                            value.value.equipments[i].marking;
                    }
                } else {
                    value.value.equipments[0] = {
                        id: null,
                        name: "Оборудование этой модели отсутствует",
                    };
                }
            })
            .catch((error) => {});
    }
};

const addToEquipments = () => {
    newWorkerUnit.value.equipments = [...newWorkerUnit.value.equipments, {}];
};

const removeFromEquipments = (index) => {
    newWorkerUnit.value.equipments = newWorkerUnit.value.equipments.filter(
        (_, i) => i !== index
    );
};

const deleteWorkerUnitHandler = (data) => {
    axios
        .delete(`/api/operation-notes/${props.noteId}/worker-units/${data.id}`)
        .then(() => {
            toastService.showSuccessToast(
                "Успешное удаления",
                "Исполнитель успешно удален"
            );
            fetchWorkerUnits();
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

// const saveChangeHandler = () => {
//     field.value.coords = JSON.parse(JSON.stringify(fieldPreview.value.coords));

//     axios
//         .patch(route("api.fields.update", field.value.id), field.value)
//         .then(() => {
//             toastService.showSuccessToast(
//                 "Успешное обновление",
//                 "Сведения об участке успешно обновлены в системе"
//             );
//             isFieldEdit.value = false;
//             isFieldNameEdit.value = false;
//             nextStatusSelect.value = false;
//         })
//         .catch((e) => {
//             toastService.showErrorToast(
//                 "Ошибка",
//                 "Что-то пошло не так. Проверьте данные и повторите попытку позже"
//             );
//         });
// };

const fetchWorkerUnits = () => {
    axios
        .get(
            `/api/operation-notes/${props.noteId}/worker-units?include=worker,technic,technic.model,technic.model.type,equipments,equipments.model`
        )
        .then((response) => {
            workerUnits.value = response.data.data;
            isLoaded.value = true;
        })
        .catch((error) => {});
};

const toWorkerDetailHandler = (data) => {
    router.visit(`/personal/${data.data.worker_id}`);
};

onMounted(() => {
    fetchWorkerUnits();
    fetchWorkersList();
    fetchTechnicTypeList();
    fetchEquipmnetTypeList();
});
</script>

<template>
    <div class="flex flex-col">
        <h2 class="font-semibold text-xl">Задействованные ресурсы</h2>

        <InlineMessage class="mt-2 w-1/2" severity="info">
            Ожидаемое время выполнения
        </InlineMessage>

        <Accordion class="border-2 rounded-lg mt-4">
            <AccordionTab>
                <template #header>
                    <span class="font-bold white-space-nowrap">
                        Добавить исполнителя
                    </span>
                </template>

                <div class="flex mt-2 gap-2 items-start">
                    <div class="flex flex-col gap-1 w-1/3">
                        <label>Механизатор</label>
                        <Dropdown
                            id="worker"
                            v-model="newWorkerUnit.worker_id"
                            :options="workersList"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Выберите сотрудника"
                            class="mt-1 w-80"
                            required
                            filter
                        />
                    </div>

                    <div class="flex flex-col gap-1 w-1/3">
                        <label>Техника</label>
                        <CascadeSelect
                            @group-change="technicGroupChange"
                            v-model="newWorkerUnit.technic"
                            :options="technicList"
                            optionLabel="name"
                            optionGroupLabel="name"
                            :optionGroupChildren="['models', 'technics']"
                            class="mt-1 w-80"
                            placeholder="Выберите технику"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value?.id">
                                    <span>
                                        {{ slotProps.value.license_plate }}
                                        -
                                        {{ slotProps.value.model_id }}
                                    </span>
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                        </CascadeSelect>
                    </div>

                    <div class="flex flex-col gap-1 w-1/3">
                        <label>Оборудование</label>

                        <div
                            class="flex gap-2 items-center"
                            v-for="(
                                equipment, eIndex
                            ) in newWorkerUnit.equipments"
                            :key="eIndex"
                        >
                            <CascadeSelect
                                @group-change="equipmentGroupChange"
                                v-model="newWorkerUnit.equipments[eIndex]"
                                :options="equipmentList"
                                optionLabel="name"
                                optionGroupLabel="name"
                                :optionGroupChildren="['models', 'equipments']"
                                class="mt-1 w-80"
                                placeholder="Выберите оборудование"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value?.id">
                                        <span>
                                            {{ slotProps.value.marking }} -
                                            {{ slotProps.value.model_id }}
                                        </span>
                                    </div>

                                    <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span>
                                </template>
                            </CascadeSelect>

                            <Button
                                :class="{ '-z-50': eIndex == 0 }"
                                outlined
                                @click="removeFromEquipments(eIndex)"
                                icon="pi pi-times"
                                severity="danger"
                                aria-label="Cancel"
                            />
                        </div>

                        <Button
                            class="text-sm p-1 mt-2 w-52"
                            @click="addToEquipments()"
                            label="Добавить оборудование"
                            outlined=""
                            severity="success"
                            icon="pi pi-plus"
                            iconPos="right"
                        />
                    </div>
                </div>

                <Button
                    class="p-2 mt-2 w-64"
                    label="Добавить исполнителя"
                    severity="success"
                    icon="pi pi-plus"
                    iconPos="right"
                />
            </AccordionTab>
        </Accordion>

        <div v-if="isLoaded">
            <DataTable
                :value="workerUnits"
                filterDisplay="menu"
                removableSort
                :rowHover="true"
                @row-click="toWorkerDetailHandler"
            >
                <template #empty>Записи не найдены</template>
                <Column field="worker.name" header="Механизатор" sortable>
                </Column>

                <Column field="technic" header="Техника">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span>{{ data.technic.license_plate }}</span>
                            <span>{{ data.technic.model.type.name }}</span>
                            <span>{{ data.technic.model.name }}</span>
                        </div>
                    </template>
                </Column>

                <Column field="equipments" header="Оборудование">
                    <template #body="{ data }">
                        <div
                            class="flex flex-col"
                            v-if="data.equipments.length"
                        >
                            <span
                                v-for="(item, index) in data.equipments"
                                :key="index"
                            >
                                {{ item.marking }} - {{ item.model.name }}
                            </span>
                        </div>
                    </template>
                </Column>

                <Column header="Статус">
                    <template #body="{ data }">
                        <span v-if="data.complete_confirm">
                            Подтвердил выполнение
                        </span>
                        <span v-else-if="data.is_used">Выполняет</span>
                        <span v-else>Не приступил</span>
                    </template>
                </Column>

                <Column header="Действия">
                    <template #body="{ data }">
                        <Button
                            class="ml-5"
                            @click="deleteWorkerUnitHandler(data)"
                            type="button"
                            severity="danger"
                            icon="pi pi-trash"
                            rounded
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style>
tr {
    cursor: pointer;
}
.p-accordion .p-accordion-header .p-accordion-header-link {
    gap: 1rem;
    justify-content: start;
}
</style>
