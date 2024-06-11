<script setup>
import { onMounted, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head, Link, router } from "@inertiajs/vue3";
import PreviewField from "@/Components/PreviewField.vue";
import Dropdown from "primevue/dropdown";
import Calendar from "primevue/calendar";
import Button from "primevue/button";
import CascadeSelect from "primevue/cascadeselect";
import InlineMessage from "primevue/inlinemessage";
import toastService from "@/Services/toastService";
import Chart from "primevue/chart";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";

const props = defineProps({
    operations: Array,
});

const today = new Date();

const chartOptions = ref();
const chartData = ref();

const fieldsList = ref();
const cultureList = ref();
const sortList = ref();
const workersList = ref();
const technicList = ref();
const equipmentList = ref();

const isShowPlantBlock = ref(false);

const recommendations = ref(null);

const selectedField = ref();
const selectedOperation = ref();
const selectedStartDate = ref();
const selectedCulture = ref();
const selectedSort = ref();
const selectedUnitsList = ref([
    {
        worker_id: null,
        technic: null,
        equipments: [{}],
    },
]);

const createOperationNote = () => {
    const unitsList = JSON.parse(JSON.stringify(selectedUnitsList.value));
    let operationNote = {};

    operationNote.start_date = selectedStartDate.value;

    if (selectedOperation.value) {
        operationNote.operation = selectedOperation.value.id;
    } else {
        toastService.showErrorToast("Ошибка", "Выберите тип мероприятия");
        return;
    }

    if (selectedOperation.value.isNeedField) {
        if (!selectedField.value) {
            toastService.showErrorToast(
                "Ошибка",
                "Для данного мероприятия необходимо выбрать поле"
            );
            return;
        } else {
            if (
                selectedOperation.value.currentFieldStatus.includes(
                    selectedField.value.status
                )
            ) {
                operationNote.field_id = selectedField.value.id;
            } else {
                toastService.showErrorToast(
                    "Ошибка",
                    "Выбран неподходящий тип мероприятия для текущего состояния поля"
                );
                return;
            }
        }
    }

    if (operationNote.operation == "seeding" && !selectedSort.value) {
        toastService.showErrorToast(
            "Ошибка",
            "Выберите сорт растения для данного мероприятия"
        );
        return;
    } else {
        operationNote.sort_id = selectedSort.value;
    }

    for (const i in unitsList) {
        if (unitsList[i].worker_id && !unitsList[i].technic?.id) {
            toastService.showErrorToast(
                "Ошибка",
                "Проверьте выбранную технику"
            );
            return;
        }

        if (!unitsList[i].worker_id && unitsList[i].technic) {
            toastService.showErrorToast(
                "Ошибка",
                "Проверьте выбранных механизаторов"
            );
            return;
        }
    }

    const workerIds = unitsList.map((item) => item.worker_id);
    const uniqueWorkerIds = new Set(workerIds);
    if (uniqueWorkerIds.size !== workerIds.length) {
        toastService.showErrorToast(
            "Ошибка",
            "Невозможно выбрать механизатора более одного раза"
        );
        return;
    }

    const technicIds = unitsList.map((item) => item.technic?.id);
    const uniquetechnicIds = new Set(technicIds);
    if (uniquetechnicIds.size !== technicIds.length) {
        toastService.showErrorToast(
            "Ошибка",
            "Невозможно выбрать технику более одного раза"
        );
        return;
    }

    const equipmentIds = unitsList
        .flatMap((entry) => entry?.equipments.map((equipment) => equipment?.id))
        .filter((id) => id != null);
    const uniqueEquipmentIds = new Set(equipmentIds);
    if (equipmentIds.length !== uniqueEquipmentIds.size) {
        toastService.showErrorToast(
            "Ошибка",
            "Невозможно выбрать оборудование более одного раза"
        );
        return;
    }

    if (operationNote.start_date && unitsList[0].worker_id) {
        operationNote.status = "assigned";
    } else {
        operationNote.status = "planned";
    }

    axios
        .post(route("api.operation-notes.store"), operationNote)
        .then((noteRes) => {
            const noteId = noteRes.data.data.id;

            const mappedUnits = {
                resources: unitsList.map((item) => ({
                    worker_id: item.worker_id,
                    technic_id: item.technic.id,
                    equipments: item.equipments
                        .map((equipment) => equipment.id)
                        .filter((id) => id != null),
                })),
            };

            axios
                .post(
                    route(
                        "api.operation-notes.worker-units.batchStore",
                        noteId
                    ),
                    mappedUnits
                )
                .then((workerUnitsRes) => {
                    const workerUnits = workerUnitsRes.data.data;
                    for (const i in workerUnits) {
                        if (mappedUnits.resources[i].equipments.length) {
                            axios
                                .patch(
                                    route(
                                        "api.worker-units.equipments.sync",
                                        workerUnits[i].id
                                    ),
                                    {
                                        resources:
                                            mappedUnits.resources[i].equipments,
                                    }
                                )
                                .catch((e) => {
                                    toastService.showErrorToast(
                                        "Ошибка",
                                        "Что-то пошло не так. Не удалось добавить оборудование. Проверьте данные и отредактируйте их самостоятельно"
                                    );
                                });
                        }
                    }
                })
                .catch((e) => {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Что-то пошло не так. Не удалось добавить задействованные ресурсы. Отредактируйте их самостоятельно"
                    );
                });
        })
        .then(() => {
            toastService.showSuccessToast(
                "Действие выполнено",
                "Мероприятие успешно запланировано"
            );
            router.visit("/operation");
        })
        .catch((e) => {
            console.log(e);
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Не удалось создать мероприятие. Проверьте данные и повторите попытку позже"
            );
        });
};

const fetchFieldsList = () => {
    axios
        .post("/api/fields/search", {
            limit: "all",
            includes: [{ relation: "sort" }, { relation: "sort.plant" }],
        })
        .then((response) => {
            fieldsList.value = response.data.data;
        })
        .catch((error) => {});
};

const fetchCultureList = () => {
    axios.get(route(`api.plants.index`)).then((response) => {
        if (response.data.length != 0) cultureList.value = response.data.data;
    });
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

const addToUnitsList = () => {
    selectedUnitsList.value = [
        ...selectedUnitsList.value,
        {
            worker_id: null,
            technic: null,
            equipments: [{}],
        },
    ];
};

const removeFromUnitsList = (index) => {
    selectedUnitsList.value = selectedUnitsList.value.filter(
        (_, i) => i !== index
    );
};

const addToEquipments = (item) => {
    item.equipments = [...item.equipments, {}];
};

const removeFromEquipments = (item, index) => {
    item.equipments = item.equipments.filter((_, i) => i !== index);
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

const getRecommendations = () => {
    recommendations.value = null;
    let data = {};

    if (selectedField.value) {
        data.field = selectedField.value?.id;
    } else {
        return;
    }

    data.operation = selectedOperation.value?.id;

    if (!data.operation) return;

    if (data.operation === "seeding") {
        if (selectedCulture.value) {
            data.plant = selectedCulture.value;
        } else {
            return;
        }
    }

    axios
        .post(route("api.operation-notes.recommendations"), data)
        .then((response) => {
            recommendations.value = response.data.data.recommendation;
            chartData.value = {
                labels: response.data.data.period,
                datasets: [
                    {
                        label: "Отклонение от идеальных условий",
                        data: response.data.data.values,
                        tension: 0.4,
                    },
                ],
            };
        })
        .catch((e) => {});
};

const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue("--text-color");
    const textColorSecondary = documentStyle.getPropertyValue(
        "--text-color-secondary"
    );
    const surfaceBorder = documentStyle.getPropertyValue("--surface-border");

    return {
        maintainAspectRatio: false,
        aspectRatio: 0.6,
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                },
            },
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                },
                grid: {
                    color: surfaceBorder,
                },
            },
            y: {
                ticks: {
                    color: textColorSecondary,
                },
                grid: {
                    color: surfaceBorder,
                },
            },
        },
    };
};

onMounted(() => {
    fetchFieldsList();
    fetchWorkersList();
    fetchTechnicTypeList();
    fetchEquipmnetTypeList();
    chartOptions.value = setChartOptions();
});

watch(
    () => selectedOperation.value,
    () => {
        if (selectedOperation.value.id === "seeding") {
            fetchCultureList();
            isShowPlantBlock.value = true;
        } else {
            isShowPlantBlock.value = false;
        }

        chartData.value = null;
        recommendations.value = null;

        if (
            ["seeding", "harvest", "spraying", "fertilization"].includes(
                selectedOperation.value.id
            )
        ) {
            getRecommendations();
        }
    }
);

watch(
    () => selectedCulture.value,
    () => {
        fetchSortList();
        chartData.value = null;
        recommendations.value = null;
        getRecommendations();
    }
);

watch(
    () => selectedField.value,
    () => {
        chartData.value = null;
        recommendations.value = null;
        getRecommendations();
    }
);
</script>

<template>
    <Head title="Планирование мероприятия" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Планирование мероприятия
                </h2>
                <Link
                    :href="route('operation.index')"
                    class="flex items-center gap-1 mt-2"
                >
                    <i class="pi pi-chevron-left"></i>
                    <span class="font-semibold text-md">К списку</span>
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-md sm:rounded-lg">
                    <div class="p-4 flex flex-col">
                        <label class="font-semibold">Поле</label>
                        <Dropdown
                            v-model="selectedField"
                            :options="fieldsList"
                            id="field"
                            optionLabel="name"
                            placeholder="Выберите поле"
                            class="mt-1 w-1/4"
                            required
                            showClear
                        />
                        <PreviewField
                            class="mt-3"
                            :field="selectedField"
                            :showControls="false"
                            orientation="horizontal"
                        />
                    </div>
                </div>

                <div
                    class="overflow-hidden bg-white shadow-md sm:rounded-lg p-4"
                >
                    <div class="flex">
                        <div class="flex flex-col gap-1 w-1/2">
                            <label class="font-semibold">Мероприятие</label>
                            <Dropdown
                                v-model="selectedOperation"
                                :options="props.operations"
                                id="operation"
                                optionLabel="name"
                                placeholder="Выберите тип мероприятия"
                                class="mt-1 w-1/2"
                                required
                            />
                            <label class="font-semibold mt-2">
                                Планируемая дата начала
                            </label>

                            <Calendar
                                class="mt-1 w-1/2"
                                id="startDate"
                                v-model="selectedStartDate"
                                showIcon
                                iconDisplay="input"
                                required
                                :minDate="today"
                            />
                        </div>

                        <div
                            v-if="isShowPlantBlock"
                            class="flex flex-col gap-1 w-1/2"
                        >
                            <label class="font-semibold" for="culture">
                                Культура
                            </label>
                            <Dropdown
                                id="culture"
                                v-model="selectedCulture"
                                :options="cultureList"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Выберите культуру"
                                class="mt-1 w-1/2"
                                required
                            />
                            <label class="font-semibold mt-2" for="sort">
                                Сорт
                            </label>
                            <Dropdown
                                id="sort"
                                v-model="selectedSort"
                                :options="sortList"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Выберите сорт"
                                class="mt-1 w-1/2"
                                required
                                :disabled="!selectedCulture"
                            />
                        </div>
                    </div>

                    <div v-if="recommendations" class="mt-2">
                        <InlineMessage class="mt-2" severity="info">
                            {{ recommendations }}
                        </InlineMessage>
                        <Accordion v-if="chartData">
                            <AccordionTab>
                                <template #header>
                                    <span>
                                        <span
                                            class="font-bold white-space-nowrap"
                                        >
                                            Подробнее
                                        </span>
                                    </span>
                                </template>
                                <p class="m-0">
                                    <Chart
                                        type="line"
                                        :data="chartData"
                                        :options="chartOptions"
                                        class="h-96"
                                    />
                                </p>
                            </AccordionTab>
                        </Accordion>
                    </div>
                </div>

                <div
                    class="overflow-hidden bg-white shadow-md sm:rounded-lg p-4 flex flex-col"
                >
                    <label class="font-semibold">
                        Задейстованные ресурсы
                    </label>

                    <InlineMessage class="mt-2 w-1/2" severity="info">
                        Ожидаемое время выполнения
                    </InlineMessage>

                    <div
                        class="flex mt-2 gap-2 pb-2 border-b-4 items-start"
                        v-for="(item, index) in selectedUnitsList"
                        :key="index"
                    >
                        <div class="flex flex-col gap-1 w-1/3">
                            <label v-if="index == 0">Механизатор</label>
                            <Dropdown
                                id="worker"
                                v-model="item.worker_id"
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
                            <label v-if="index == 0">Техника</label>
                            <CascadeSelect
                                @group-change="technicGroupChange"
                                v-model="item.technic"
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
                            <label v-if="index == 0">Оборудование</label>

                            <div
                                class="flex gap-2 items-center"
                                v-for="(equipment, eIndex) in item.equipments"
                                :key="eIndex"
                            >
                                <CascadeSelect
                                    @group-change="equipmentGroupChange"
                                    v-model="item.equipments[eIndex]"
                                    :options="equipmentList"
                                    optionLabel="name"
                                    optionGroupLabel="name"
                                    :optionGroupChildren="[
                                        'models',
                                        'equipments',
                                    ]"
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
                                    @click="removeFromEquipments(item, eIndex)"
                                    icon="pi pi-times"
                                    severity="danger"
                                    aria-label="Cancel"
                                />
                            </div>

                            <Button
                                class="text-sm p-1 mt-2 w-52"
                                @click="addToEquipments(item)"
                                label="Добавить оборудование"
                                outlined=""
                                severity="success"
                                icon="pi pi-plus"
                                iconPos="right"
                            />
                        </div>

                        <Button
                            :class="{ '-z-50': index == 0 }"
                            @click="removeFromUnitsList(index)"
                            icon="pi pi-times"
                            severity="danger"
                            aria-label="Cancel"
                        />
                    </div>

                    <Button
                        class="text-sm p-2 mt-2 w-64"
                        @click="addToUnitsList"
                        label="Добавить исполнителя"
                        severity="success"
                        icon="pi pi-plus"
                        iconPos="right"
                    />
                </div>

                <div
                    class="overflow-hidden p-6 bg-white shadow-md sm:rounded-lg"
                >
                    <Button
                        @click="createOperationNote"
                        label="Запланировать мероприятие"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.p-accordion .p-accordion-header .p-accordion-header-link {
    gap: 1rem;
    justify-content: start;
}
</style>
