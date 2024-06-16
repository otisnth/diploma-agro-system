<script setup>
import { onMounted, ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";
import toastService from "@/Services/toastService";
import Tag from "primevue/tag";
import Calendar from "primevue/calendar";
import WorkerUnits from "@/Pages/Operation/Partials/WorkerUnits.vue";
import Chart from "primevue/chart";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import InlineMessage from "primevue/inlinemessage";

const confirm = useConfirm();

const props = defineProps({
    operationStatuses: Array,
    operations: Array,
    fieldStatuses: Array,
    id: String,
});

const operationNote = ref(null);
const workerUnit = ref({});
const isLoaded = ref(false);

const showUnits = ref(true);

const today = new Date();

const isStatusInProgress = computed(
    () =>
        operationNote.value.status == "inProgress" &&
        !workerUnit.value.complete_confirm &&
        workerUnit.value.is_used
);

const isShowStartOperation = computed(() => {
    return (
        ["inProgress", "assigned"].includes(operationNote.value.status) &&
        operationNote.value.start_date <= today &&
        !workerUnit.value.complete_confirm &&
        !workerUnit.value.is_used
    );
});

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

function prepareDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date;
}

const startOperation = () => {
    confirm.require({
        message:
            "Вы действительно хотите начать выполнение данного мероприятия?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-primary",
        rejectLabel: "Отмена",
        acceptLabel: "Подтвердить",
        accept: () => {
            axios
                .patch(route("api.worker-units.update", workerUnit.value.id), {
                    is_used: true,
                })
                .then(() => {
                    toastService.showSuccessToast(
                        "Успешное обновление",
                        "Выполнение мероприятия успешно начато"
                    );
                    fetchOperationNote();
                })
                .catch((e) => {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                    );
                });
        },
    });
};

const confirmFinish = () => {
    confirm.require({
        message:
            "Вы действительно хотите подтвердить выполнение данного мероприятия?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-primary",
        rejectLabel: "Отмена",
        acceptLabel: "Подтвердить",
        accept: () => {
            axios
                .patch(route("api.worker-units.update", workerUnit.value.id), {
                    complete_confirm: true,
                })
                .then(() => {
                    toastService.showSuccessToast(
                        "Успешное обновление",
                        "Выполнение мероприятия успешно подтверждено"
                    );
                    fetchOperationNote();
                })
                .catch((e) => {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                    );
                });
        },
    });
};

const fetchOperationNote = () => {
    showUnits.value = false;
    axios
        .get(
            `/api/operation-notes/${props.id}?include=field,author,sort,sort.plant`
        )
        .then((response) => {
            response.data.data.start_date = prepareDate(
                response.data.data.start_date
            );
            response.data.data.end_date = prepareDate(
                response.data.data.end_date
            );
            operationNote.value = response.data.data;
            fetchWorkerUnit();
            isLoaded.value = true;
            showUnits.value = true;
            if (
                ["seeding", "harvest", "spraying", "fertilization"].includes(
                    operationNote.value.operation
                ) &&
                ["assigned", "planned"].includes(operationNote.value.status)
            ) {
                getRecommendations();
            }
        })
        .catch((error) => {});
};

const fetchWorkerUnit = () => {
    axios
        .post("/api/worker-units/search", {
            filters: [
                {
                    field: "worker_id",
                    operator: "=",
                    value: usePage().props.auth.user.id,
                },
                {
                    field: "operation_note_id",
                    operator: "=",
                    value: operationNote.value.id,
                },
            ],
        })
        .then((response) => {
            workerUnit.value = response.data.data[0];
        })
        .catch((error) => {});
};

onMounted(() => {
    fetchOperationNote();
});
</script>

<template>
    <Head
        title="Информация о мероприятии
"
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Информация о мероприятии
                </h2>
                <Link
                    :href="route('dashboard')"
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
                    class="rounded-lg bg-white shadow-md p-6 flex flex-col gap-2"
                >
                    <div class="flex flex-col">
                        <h2 class="font-semibold text-2xl">
                            {{ operationNote.note_operation.name }}
                        </h2>

                        <span class="font-semibold" v-if="operationNote.sort">
                            {{ operationNote.sort.plant.name }}
                            ({{ operationNote.sort.name }})
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex flex-col gap-2 sm:w-1/2">
                            <div class="flex gap-2" v-if="operationNote.field">
                                <span class="text-lg font-semibold">
                                    Поле:
                                </span>

                                <span>{{ operationNote.field.name }}</span>
                            </div>

                            <div class="flex gap-2">
                                <span class="text-lg font-semibold">
                                    Статус:
                                </span>
                                <div class="flex flex-col gap-2 mt-1">
                                    <span>
                                        {{ operationNote.note_status.name }}
                                    </span>

                                    <Tag
                                        v-if="isShowStartOperation"
                                        @click="startOperation"
                                        class="self-start cursor-pointer"
                                        severity=""
                                        value="Начать выполнение"
                                    ></Tag>

                                    <Tag
                                        v-if="isStatusInProgress"
                                        @click="confirmFinish"
                                        class="self-start cursor-pointer"
                                        severity=""
                                        value="Подтвердить выполнение"
                                    ></Tag>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 sm:w-1/2">
                            <div class="flex gap-2 items-center">
                                <span class="text-lg font-semibold">
                                    Ответственный:
                                </span>

                                <span>{{ operationNote.author.name }}</span>
                            </div>

                            <div class="flex gap-2 items-center">
                                <span class="text-lg font-semibold">
                                    Дата начала:
                                </span>

                                <span>
                                    {{ formatDate(operationNote.start_date) }}
                                </span>
                            </div>

                            <div class="flex gap-2 items-center">
                                <span class="text-lg font-semibold">
                                    Дата окончания:
                                </span>

                                <span>
                                    {{ formatDate(operationNote.end_date) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <WorkerUnits
                        v-if="showUnits"
                        :note-id="props.id"
                        :field-id="operationNote.field_id"
                        :is-show-map="false"
                        :is-available-add="false"
                        :is-available-delete="false"
                        :show-expected-time="false"
                        :enable-go-detail="false"
                    />
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

.grid-line {
    display: grid;
    grid-template-columns: auto auto;
    align-items: center;
}
</style>
