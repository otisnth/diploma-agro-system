<script setup>
import { onMounted, ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, useForm, Link, router } from "@inertiajs/vue3";
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

const props = defineProps({
    fieldStatuses: Array,
    id: String,
});

const field = ref(null);
const isLoaded = ref(false);

const coordsList = ref([[0, 0]]);
const coordsString = ref("");
const fieldPreview = ref();

const coordsInputMode = ref();

const isFieldEdit = ref(false);

const isFieldNameEdit = ref(false);

const coordsValidation = ref({
    list: {
        type: "",
        message: "",
    },
    string: {
        type: "",
        message: "",
    },
});

const isShowNextStatus = computed(() => {
    return field.value.status == "growing";
});

const isShowConfirmGrow = computed(() => {
    return field.value.status == "seedbed";
});

const nextStatusArray = computed(() => {
    return props.fieldStatuses.filter((status) =>
        ["readyToHarvest", "withered", "removeFoliage"].includes(status.id)
    );
});

const nextStatusSelect = ref(false);

const confirmGrowHandler = () => {
    field.value.status = "growing";
    field.value.field_status.name = props.fieldStatuses.find(
        (status) => status.id == field.value.status
    ).name;
    isFieldEdit.value = true;
};

const cancelChangeHandler = () => {
    fetchField();
    isFieldEdit.value = false;
    isFieldNameEdit.value = false;
    nextStatusSelect.value = false;
};

const saveChangeHandler = () => {
    field.value.coords = JSON.parse(JSON.stringify(fieldPreview.value.coords));

    axios
        .patch(route("api.fields.update", field.value.id), field.value)
        .then(() => {
            toastService.showSuccessToast(
                "Успешное обновление",
                "Сведения об участке успешно обновлены в системе"
            );
            isFieldEdit.value = false;
            isFieldNameEdit.value = false;
            nextStatusSelect.value = false;
        })
        .catch((e) => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Проверьте данные и повторите попытку позже"
            );
        });
};

function formatNumberWithSpaces(number) {
    let integerPart = Math.floor(number).toString();

    return integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

const fetchField = () => {
    axios
        .get(`/api/fields/${props.id}?include=sort,sort.plant`)
        .then((response) => {
            field.value = response.data.data;
            fieldPreview.value = JSON.parse(JSON.stringify(response.data.data));
            isLoaded.value = true;
            coordsList.value = extractCoordinates(
                JSON.parse(JSON.stringify(field.value.coords))
            );
            coordsString.value = geoJsonToString(
                JSON.parse(JSON.stringify(field.value.coords))
            );
        })
        .catch((error) => {});
};

const addCoordsToList = () => {
    coordsList.value = [...coordsList.value, [0, 0]];
};

const removeCoordsFromList = (index) => {
    coordsList.value = coordsList.value.filter((_, i) => i !== index);
};

const changeSquareHandler = (area) => {
    if (field.value.square != Math.round(area)) isFieldEdit.value = true;
    field.value.square = Math.round(area);
};

const drawEditedHandler = (geoJson) => {
    fieldPreview.value.coords = JSON.parse(JSON.stringify(field.value.coords));
    if (!geoJson) {
        return;
    }

    geoJson.geometry.coordinates[0].pop();

    fieldPreview.value.coords = JSON.parse(JSON.stringify(geoJson));
};

const toggleNameEdit = () => {
    isFieldNameEdit.value = !isFieldNameEdit.value;
    isFieldEdit.value = true;
};

function extractCoordinates(geojson) {
    const coordinates = [];

    const geometry = geojson.geometry;

    geometry.coordinates.forEach((coord) => coordinates.push(...coord));

    return coordinates;
}

function geoJsonToString(geojson) {
    const coordinates = extractCoordinates(geojson);
    return coordinates.map((coord) => coord[1] + " " + coord[0]).join(" ");
}

watch(
    () => coordsList.value,
    () => {
        if (coordsList.value.length < 3) {
            fieldPreview.value.coords = JSON.parse(
                JSON.stringify(field.value.coords)
            );
            coordsValidation.value.list.type = "info";
            coordsValidation.value.list.message =
                "Введите координаты минимум 3-ех точек";
            return;
        }

        for (const coords of coordsList.value) {
            if (!coords[0] || !coords[1]) {
                coordsValidation.value.list.type = "error";
                coordsValidation.value.list.message =
                    "Введены некорректные координаты";
                return;
            }
        }

        fieldPreview.value.coords = JSON.parse(
            JSON.stringify(field.value.coords)
        );

        coordsValidation.value.list.type = "";

        let geoJson = {
            type: "Feature",
            geometry: {
                type: "Polygon",
                coordinates: [coordsList.value],
            },
        };

        fieldPreview.value.coords = geoJson;
    },
    { deep: true }
);

watch(
    () => coordsString.value,
    () => {
        let rawCoords = coordsString.value.split(/\s+/).map(Number);
        let coords = [];

        if (rawCoords.length < 6) {
            fieldPreview.value.coords = JSON.parse(
                JSON.stringify(field.value.coords)
            );
            coordsValidation.value.string.type = "info";
            coordsValidation.value.string.message =
                "Введите координаты минимум 3-ех точек";
            return;
        }

        for (let i = 0; i < rawCoords.length; i += 2) {
            if (i + 1 < rawCoords.length) {
                if (isNaN(rawCoords[i]) || isNaN(rawCoords[i + 1])) {
                    coordsValidation.value.string.type = "error";
                    coordsValidation.value.string.message =
                        "Допускаются только числовые значения";
                    return;
                }

                if (!rawCoords[i + 1]) {
                    coordsValidation.value.string.type = "error";
                    coordsValidation.value.string.message =
                        "Введено неверное число координат";
                    return;
                }

                if (rawCoords[i + 1] > 180 || rawCoords[i + 1] < -180) {
                    coordsValidation.value.string.type = "error";
                    coordsValidation.value.string.message =
                        "Значение долготы должно быть в диапазоне от -180 до 180";
                    return;
                }

                if (rawCoords[i] > 90 || rawCoords[i] < -90) {
                    coordsValidation.value.string.type = "error";
                    coordsValidation.value.string.message =
                        "Значение широты должно быть в диапазоне от -90 до 90";
                    return;
                }

                coords.push([rawCoords[i + 1], rawCoords[i]]);
            } else {
                coordsValidation.value.string.type = "error";
                coordsValidation.value.string.message =
                    "Введено неверное число координат";
                return;
            }
        }

        fieldPreview.value.coords = JSON.parse(
            JSON.stringify(field.value.coords)
        );

        coordsValidation.value.string.type = "";

        let geoJson = {
            type: "Feature",
            geometry: {
                type: "Polygon",
                coordinates: [coords],
            },
        };

        fieldPreview.value.coords = geoJson;
    },
    { deep: true }
);

watch(
    () => coordsInputMode.value,
    () => {
        fieldPreview.value.coords = JSON.parse(
            JSON.stringify(field.value.coords)
        );
        coordsList.value = extractCoordinates(
            JSON.parse(JSON.stringify(field.value.coords))
        );
        coordsString.value = geoJsonToString(
            JSON.parse(JSON.stringify(field.value.coords))
        );
    }
);

onMounted(() => {
    fetchField();
});
</script>

<template>
    <Head title="Информация об участке" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Информация об участке
                </h2>
                <Link
                    :href="route('field.index')"
                    class="flex items-center gap-1 mt-2"
                >
                    <i class="pi pi-chevron-left"></i>
                    <span class="font-semibold text-md">К списку</span>
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div
                v-if="isLoaded"
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex flex-col gap-2"
            >
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <div class="flex gap-2">
                        <div class="w-1/2 flex flex-col gap-2">
                            <div class="grid grid-cols-2 items-center">
                                <InputText
                                    v-if="isFieldNameEdit"
                                    type="text"
                                    v-model="field.name"
                                    pt:root:class="border-0 shadow-none text-2xl font-semibold p-0"
                                />

                                <h2 v-else class="font-semibold text-2xl">
                                    {{ field.name }}
                                </h2>

                                <i
                                    class="cursor-pointer pi pi-pen-to-square"
                                    @click="toggleNameEdit"
                                ></i>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Площадь:
                                </span>
                                <span>
                                    {{ formatNumberWithSpaces(field.square) }}
                                    м&sup2;
                                </span>

                                <span class="text-lg font-semibold">
                                    Состояние поля:
                                </span>
                                <div class="flex flex-col">
                                    <Dropdown
                                        v-if="nextStatusSelect"
                                        id="status"
                                        v-model="field.status"
                                        :options="nextStatusArray"
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Выберите состояние"
                                        pt:root:class="border-0 shadow-none"
                                        pt:input:class="p-0"
                                        required
                                        @change="isFieldEdit = true"
                                    />
                                    <span v-else>
                                        {{ field.field_status.name }}
                                    </span>
                                    <Tag
                                        v-if="isShowConfirmGrow"
                                        @click="confirmGrowHandler"
                                        class="self-start cursor-pointer"
                                        severity="success"
                                        value="Подтвердить прорастание"
                                    ></Tag>

                                    <Tag
                                        v-if="
                                            isShowNextStatus &&
                                            !nextStatusSelect
                                        "
                                        @click="
                                            nextStatusSelect = !nextStatusSelect
                                        "
                                        class="self-start cursor-pointer"
                                        severity="success"
                                        value="Обновить состояние"
                                    ></Tag>

                                    <Tag
                                        v-if="
                                            isShowNextStatus && nextStatusSelect
                                        "
                                        @click="
                                            nextStatusSelect = !nextStatusSelect
                                        "
                                        class="self-start cursor-pointer"
                                        severity="danger"
                                        value="Отмена"
                                    ></Tag>
                                </div>

                                <span
                                    v-if="field.sort"
                                    class="text-lg font-semibold"
                                >
                                    Культура:
                                </span>
                                <span v-if="field.sort">
                                    {{ field.sort.plant.name }}
                                </span>

                                <span
                                    v-if="field.sort"
                                    class="text-lg font-semibold"
                                >
                                    Сорт:
                                </span>
                                <span v-if="field.sort">
                                    {{ field.sort.name }}
                                </span>
                            </div>

                            <div>
                                <span class="text-lg font-semibold">
                                    Мероприятие:
                                </span>
                            </div>

                            <div class="flex gap-2 items-end h-full">
                                <Button severity="danger" label="Удалить" />

                                <Button
                                    v-if="isFieldEdit"
                                    @click="saveChangeHandler"
                                    label="Сохранить изменения"
                                />

                                <Button
                                    v-if="isFieldEdit"
                                    @click="cancelChangeHandler"
                                    severity="secondary"
                                    label="Отменить изменения"
                                />
                            </div>
                        </div>

                        <Map
                            class="w-1/2"
                            :fields="[fieldPreview]"
                            height="400px"
                            :getFieldSquare="true"
                            @change-square="changeSquareHandler"
                            :draw-polygon="coordsInputMode == 2 ? true : false"
                            @draw-edited="drawEditedHandler"
                        />
                    </div>

                    <Accordion>
                        <AccordionTab>
                            <template #header>
                                <span>
                                    <span class="font-bold white-space-nowrap">
                                        Редактирование координат
                                    </span>
                                </span>
                            </template>
                            <TabView v-model:active-index="coordsInputMode">
                                <TabPanel header="Покоординатный ввод">
                                    <p class="mt-1 mb-2 text-sm max-w-xl">
                                        Введите координаты крайних точек
                                        участка, начиная с любого угла по
                                        часовой стрелке.
                                    </p>

                                    <InlineMessage
                                        v-if="
                                            coordsValidation.list.type == 'info'
                                        "
                                        severity="info"
                                    >
                                        {{ coordsValidation.list.message }}
                                    </InlineMessage>
                                    <InlineMessage
                                        v-else-if="
                                            coordsValidation.list.type ==
                                            'error'
                                        "
                                        severity="error"
                                    >
                                        {{ coordsValidation.list.message }}
                                    </InlineMessage>

                                    <div
                                        class="flex mt-2 gap-2 justify-items-start"
                                    >
                                        <label class="w-28 font-semibold"
                                            >Широта</label
                                        >
                                        <label class="w-28 font-semibold"
                                            >Долгота</label
                                        >
                                    </div>
                                    <div
                                        class="flex mt-2 gap-2 justify-items-start"
                                        v-for="(item, index) in coordsList"
                                        :key="index"
                                    >
                                        <InputNumber
                                            v-model="item[1]"
                                            inputClass="w-28"
                                            :min="-90"
                                            :max="90"
                                            required
                                            :minFractionDigits="0"
                                            :maxFractionDigits="6"
                                        />

                                        <InputNumber
                                            v-model="item[0]"
                                            inputClass="w-28"
                                            :min="-180"
                                            :max="180"
                                            required
                                            :minFractionDigits="0"
                                            :maxFractionDigits="6"
                                        />

                                        <Button
                                            v-if="index > 0"
                                            outlined
                                            @click="removeCoordsFromList(index)"
                                            icon="pi pi-times"
                                            severity="danger"
                                            aria-label="Cancel"
                                        />
                                    </div>

                                    <Button
                                        class="text-sm p-2 mt-2"
                                        @click="addCoordsToList"
                                        label="Добавить координаты"
                                        severity="success"
                                        icon="pi pi-plus"
                                        iconPos="right"
                                    />
                                </TabPanel>
                                <TabPanel header="Ввод строкой">
                                    <p class="mt-1 mb-2 text-sm max-w-xl">
                                        Введите координаты крайних точек
                                        участка, начиная с любого угла по
                                        часовой стрелке. В качестве разделителя
                                        используйте пробел.
                                    </p>

                                    <InlineMessage
                                        v-if="
                                            coordsValidation.string.type ==
                                            'info'
                                        "
                                        severity="info"
                                    >
                                        {{ coordsValidation.string.message }}
                                    </InlineMessage>
                                    <InlineMessage
                                        v-else-if="
                                            coordsValidation.string.type ==
                                            'error'
                                        "
                                        severity="error"
                                    >
                                        {{ coordsValidation.string.message }}
                                    </InlineMessage>

                                    <Textarea
                                        class="mt-3 w-full md:w-14rem"
                                        v-model="coordsString"
                                        autoResize
                                        rows="5"
                                        cols="30"
                                    />
                                </TabPanel>

                                <TabPanel header="Ручной ввод">
                                    <p class="mt-1 mb-2 text-sm max-w-xl">
                                        Отметьте границы участка в ручном
                                        режиме. Для этого воспользуйтесь
                                        инструментами в левом верхнем углу
                                        карты.
                                    </p>
                                </TabPanel>
                            </TabView>
                        </AccordionTab>
                    </Accordion>
                </div>

                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <h2 class="font-semibold text-xl">Севооборот</h2>
                    <CropRotation :field_id="field.id" />
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
