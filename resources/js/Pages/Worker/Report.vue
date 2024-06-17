<script setup>
import { onMounted, ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";
import toastService from "@/Services/toastService";
import Map from "@/Components/Map.vue";
import ReportShow from "@/Components/ReportShow.vue";
import CropRotation from "@/Pages/Field/Partials/CropRotation.vue";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import InputNumber from "primevue/inputnumber";
import InlineMessage from "primevue/inlinemessage";
import Textarea from "primevue/textarea";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Tag from "primevue/tag";

const confirm = useConfirm();

const props = defineProps({
    posts: Array,
    id: String,
});

const workerUnits = ref(null);
const isLoaded = ref(false);

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

const fetchWorkerUnits = () => {
    axios
        .post("/api/worker-units/search", {
            limit: "all",
            includes: [
                { relation: "operationNote" },
                { relation: "operationNote.field" },
                { relation: "technic" },
                { relation: "technic.model" },
                { relation: "technic.model.type" },
                { relation: "equipments" },
                { relation: "equipments.model" },
            ],
            filters: [
                {
                    field: "worker_id",
                    operator: "=",
                    value: usePage().props.auth.user.id,
                },
            ],
        })
        .then((response) => {
            workerUnits.value = response.data.data.filter(
                (unit) => unit.report != "{}"
            );
            isLoaded.value = true;
        })
        .catch((error) => {});
};

onMounted(() => {
    fetchWorkerUnits();
});
</script>

<template>
    <Head
        title="Информация о сотруднике
"
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Отчеты
                </h2>
            </div>
        </template>

        <div class="py-2">
            <div
                v-if="isLoaded"
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex flex-col gap-2"
            >
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                    v-for="(item, index) in workerUnits"
                    :key="index"
                >
                    <ReportShow :unit="item" />
                </div>
            </div>
        </div>
        <ConfirmDialog></ConfirmDialog>
    </AuthenticatedLayout>
</template>

<style>
.p-accordion .p-accordion-header .p-accordion-header-link {
    gap: 1rem;
    justify-content: start;
}
</style>
