<script setup>
import { onMounted, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Map from "@/Components/Map.vue";
import axios from "axios";
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import Calendar from "primevue/calendar";
import Textarea from "primevue/textarea";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import InlineMessage from "primevue/inlinemessage";
import Button from "primevue/button";
import toastService from "@/Services/toastService";

const props = defineProps({
    fieldStatuses: Array,
});

const form = useForm({
    name: "",
    coords: null,
    status: "",
    square: 0,
    sort: null,
    plant: null,
    startDate: null,
});

const isShowPlantBlock = ref(false);
const cultureList = ref(null);
const sortList = ref(null);

const coordsList = ref([[0, 0]]);
const coordsString = ref("");
const fieldPreview = ref([]);

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

const fetchCultureList = () => {
    axios.get(route(`api.plants.index`)).then((response) => {
        if (response.data.length != 0) cultureList.value = response.data.data;
    });
};

const fetchSortList = () => {
    axios
        .post("/api/sorts/search", {
            filters: [{ field: "plant_id", operator: "=", value: form.plant }],
        })
        .then((response) => {
            if (response.data.length != 0) sortList.value = response.data.data;
        });
};

const addCoordsToList = () => {
    coordsList.value = [...coordsList.value, [0, 0]];
};

const removeCoordsFromList = (index) => {
    coordsList.value = coordsList.value.filter((_, i) => i !== index);
};

const createField = () => {
    if (!form.name) {
        toastService.showErrorToast("Ошибка", "Введите название участка");
        return;
    }

    if (!form.square) {
        toastService.showErrorToast("Ошибка", "Введите площадь участка");
        return;
    }

    if (!form.status) {
        toastService.showErrorToast("Ошибка", "Выберите состояние участка");
        return;
    } else {
        const showPlantStatuses = [
            "seedbed",
            "growing",
            "readyToHarvest",
            "removeFoliage",
        ];

        if (showPlantStatuses.includes(form.status)) {
            if (!form.plant) {
                toastService.showErrorToast("Ошибка", "Выберите культуру");
                return;
            }

            if (!form.sort) {
                toastService.showErrorToast("Ошибка", "Выберите сорт");
                return;
            }

            if (!form.startDate) {
                toastService.showErrorToast("Ошибка", "Введите дату посева");
                return;
            } else {
                const today = new Date();
                if (form.startDate > today) {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Дата посева не может быть позже текущей"
                    );
                    return;
                }
            }
        }
    }

    if (!fieldPreview.value?.[0]) {
        toastService.showErrorToast("Ошибка", "Введите координаты участка");
        return;
    }

    form.coords = fieldPreview.value[0].coords;
    form.sort_id = form.sort;

    axios
        .post(route("api.fields.store"), form, {
            onFinish: () => form.reset(),
        })
        .then(() => {
            toastService.showSuccessToast(
                "Успешное добавление",
                "Участок добавлен в систему"
            );
            router.visit("/field");
        })
        .catch((e) => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Проверьте данные и повторите попытку позже"
            );
        });
};

watch(
    () => form,
    () => {
        const showPlantStatuses = [
            "seedbed",
            "growing",
            "readyToHarvest",
            "removeFoliage",
        ];
        if (showPlantStatuses.includes(form.status)) {
            isShowPlantBlock.value = true;
            if (!cultureList.value) {
                fetchCultureList();
            }

            if (form.plant) {
                fetchSortList();
            }
        } else {
            isShowPlantBlock.value = false;
            form.plant = null;
            form.sort = null;
            form.startDate = null;
        }
    },
    { deep: true }
);

watch(
    () => coordsList.value,
    () => {
        if (coordsList.value.length < 3) {
            fieldPreview.value = [];
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

        fieldPreview.value = [];

        coordsValidation.value.list.type = "";

        let geoJson = {
            type: "Feature",
            geometry: {
                type: "Polygon",
                coordinates: [coordsList.value],
            },
        };

        fieldPreview.value.push({
            coords: geoJson,
        });
    },
    { deep: true }
);

watch(
    () => coordsString.value,
    () => {
        let rawCoords = coordsString.value.split(/\s+/).map(Number);
        let coords = [];

        if (rawCoords.length < 6) {
            fieldPreview.value = [];
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

        fieldPreview.value = [];

        coordsValidation.value.string.type = "";

        let geoJson = {
            type: "Feature",
            geometry: {
                type: "Polygon",
                coordinates: [coords],
            },
        };

        fieldPreview.value.push({
            coords: geoJson,
        });
    },
    { deep: true }
);
</script>

<template>
    <Head title="Добавление участка" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Добавление участка
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    class="overflow-hidden p-6 bg-white shadow-md sm:rounded-lg"
                >
                    <p class="mt-1 text-sm max-w-xl">
                        Заполните основные сведения об участке. Для определенных
                        состояний, предполагающих наличие растений на участке,
                        необходимо заполнить информацию об этих растениях, при
                        этом будет создана запись в севообороте.
                    </p>

                    <form class="mt-4">
                        <input
                            type="hidden"
                            name="_token"
                            value="{{ csrf_token() }}"
                        />

                        <div class="flex gap-10">
                            <div class="flex flex-col w-1/2">
                                <div>
                                    <label for="name">Название участка</label>
                                    <InputText
                                        id="name"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                    />
                                </div>

                                <div class="mt-6">
                                    <label for="square">Площадь</label>
                                    <InputNumber
                                        v-model="form.square"
                                        id="square"
                                        :min="0"
                                        suffix=" м&sup2;"
                                        class="mt-1 w-full"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="flex flex-col w-1/2">
                                <div>
                                    <label for="status"
                                        >Состояние участка</label
                                    >
                                    <Dropdown
                                        id="status"
                                        v-model="form.status"
                                        :options="props.fieldStatuses"
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Выберите состояние"
                                        class="mt-1 w-full md:w-14rem"
                                        required
                                    />
                                </div>

                                <div
                                    class="mt-4 p-4 pt-2 bg-neutral-100 sm:rounded-lg"
                                    v-if="isShowPlantBlock"
                                >
                                    <div>
                                        <label for="culture">Культура</label>
                                        <Dropdown
                                            id="culture"
                                            v-model="form.plant"
                                            :options="cultureList"
                                            optionLabel="name"
                                            optionValue="id"
                                            placeholder="Выберите культуру"
                                            class="mt-1 w-full md:w-14rem"
                                            required
                                        />
                                    </div>

                                    <div class="mt-4">
                                        <label for="sort">Сорт</label>
                                        <Dropdown
                                            id="sort"
                                            v-model="form.sort"
                                            :options="sortList"
                                            optionLabel="name"
                                            optionValue="id"
                                            placeholder="Выберите сорт"
                                            class="mt-1 w-full md:w-14rem"
                                            required
                                            :disabled="!form.plant"
                                        />
                                    </div>

                                    <div class="mt-4">
                                        <label for="startDate">
                                            Дата посева
                                        </label>
                                        <Calendar
                                            class="mt-1 w-full md:w-14rem"
                                            id="startDate"
                                            v-model="form.startDate"
                                            showIcon
                                            iconDisplay="input"
                                            :disabled="!form.sort"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div
                    class="overflow-hidden p-6 bg-white shadow-md sm:rounded-lg"
                >
                    <div class="flex gap-10 justify-between">
                        <div class="flex flex-col w-1/2">
                            <label class="font-semibold text-md" for="coords"
                                >Координаты участка</label
                            >
                            <TabView>
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
                                        <label class="w-28">Широта</label>
                                        <label class="w-28">Долгота</label>
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
                            </TabView>
                        </div>

                        <div class="flex flex-col w-1/2">
                            <Map height="560px" :fields="fieldPreview" />
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden p-6 bg-white shadow-md sm:rounded-lg"
                >
                    <Button
                        @click="createField"
                        label="Добавить участок в систему"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
