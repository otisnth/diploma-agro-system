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
import Chart from "primevue/chart";

const confirm = useConfirm();

const props = defineProps({
    unit: Object,
});

const chartData = ref();
const chartOptions = ref();

const report = computed(() => JSON.parse(props.unit.report));

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

const setChartData = () => {
    return {
        labels: [
            "Плановое время выполнения",
            "Фактически затраченное время",
            "Время простоев",
        ],
        datasets: [
            {
                label: "Часы",
                data: [
                    report.value.t.plan,
                    report.value.p.time,
                    report.value.d.stop,
                ],
                backgroundColor: [
                    "rgba(249, 115, 22, 0.2)",
                    "rgba(6, 182, 212, 0.2)",
                    "rgb(107, 114, 128, 0.2)",
                    "rgb(107, 114, 128, 0.2)",
                ],
                borderColor: [
                    "rgb(249, 115, 22)",
                    "rgb(6, 182, 212)",
                    "rgb(107, 114, 128)",
                ],
                borderWidth: 1,
            },
        ],
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue("--p-text-color");
    const textColorSecondary = documentStyle.getPropertyValue(
        "--p-text-muted-color"
    );
    const surfaceBorder = documentStyle.getPropertyValue(
        "--p-content-border-color"
    );

    return {
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
                beginAtZero: true,
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
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <span class="font-semibold text-xl">
            {{ props.unit.operation_note.note_operation.name }}
        </span>

        <div class="flex gap-24">
            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <span class="font-semibold">Дата начала: </span>
                    <span>
                        {{ formatDate(props.unit.operation_note.start_date) }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <span class="font-semibold">Дата окончания: </span>
                    <span>
                        {{ formatDate(props.unit.operation_note.end_date) }}
                    </span>
                </div>

                <div class="flex gap-2 flex-col">
                    <span class="font-semibold">Техника: </span>
                    <span class="flex pl-4">
                        {{ props.unit.technic.license_plate }} <br />
                        {{ props.unit.technic.model.type.name }} <br />
                        {{ props.unit.technic.model.name }}
                    </span>
                </div>
            </div>

            <div
                v-if="props.unit.operation_note.field_id"
                class="flex flex-col gap-2"
            >
                <div class="flex gap-2">
                    <span class="font-semibold">Участок: </span>
                    <span>
                        {{ props.unit.operation_note.field.name }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <span class="font-semibold">Площадь: </span>
                    <span>
                        {{ props.unit.operation_note.field.square }} м&sup2;
                    </span>
                </div>

                <div v-if="props.unit.equipments" class="flex gap-2 flex-col">
                    <span class="font-semibold">Оборудование: </span>
                    <div class="flex pl-4 flex-col">
                        <span
                            v-for="(item, index) in props.unit.equipments"
                            :key="index"
                        >
                            {{ item.marking }} - {{ item.model.name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2 items-center">
            <span class="text-lg font-semibold">Общая эффективность:</span>
            <span>{{ report.total.toFixed(2) }}</span>
        </div>

        <Accordion>
            <AccordionTab>
                <template #header>
                    <span class="font-bold white-space-nowrap">
                        Подробнее
                    </span>
                </template>
                <div class="flex gap-2">
                    <span class="font-semibold">Обработанная площадь: </span>
                    <span> {{ report.p.square }} м&sup2; </span>
                </div>
                <Chart type="bar" :data="chartData" :options="chartOptions" />
            </AccordionTab>
        </Accordion>
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
