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
const isLoaded = ref(false);

const isAuthorEdit = ref(false);
const isStartDateEdit = ref(false);
const isNoteEdited = ref(false);

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

const saveChangeHandler = () => {
    const utcDate = new Date(
        Date.UTC(
            operationNote.value.start_date.getFullYear(),
            operationNote.value.start_date.getMonth(),
            operationNote.value.start_date.getDate()
        )
    );
    operationNote.value.start_date = utcDate;
    axios
        .patch(
            route("api.operation-notes.update", operationNote.value.id),
            operationNote.value
        )
        .then(() => {
            toastService.showSuccessToast(
                "Успешное обновление",
                "Сведения о мероприятии успешно обновлены в системе"
            );
            isStartDateEdit.value = false;
            isAuthorEdit.value = false;
            isNoteEdited.value = false;
        })
        .catch((e) => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Проверьте данные и повторите попытку позже"
            );
        });
};

const confirmDeleteNote = () => {
    confirm.require({
        message: "Вы действительно хотите удалить данное мероприятие?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-danger",
        rejectLabel: "Отмена",
        acceptLabel: "Удалить",
        accept: () => {
            axios
                .delete(
                    route(`api.operation-notes.destroy`, operationNote.value.id)
                )
                .then(() => {
                    toastService.showSuccessToast(
                        "Успешное удаления",
                        "Запись о мероприятии удалена"
                    );
                    router.visit("/operation");
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

const fetchOperationNote = () => {
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
            isLoaded.value = true;
            isNoteEdited.value = false;
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

                    <div class="flex gap-2">
                        <div class="flex flex-col gap-2 w-1/2">
                            <div
                                class="grid grid-cols-2 gap-2"
                                v-if="operationNote.field"
                            >
                                <span class="text-lg font-semibold">
                                    Поле:
                                </span>

                                <span>{{ operationNote.field.name }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Статус:
                                </span>
                                <div class="flex flex-col gap-2">
                                    <span>
                                        {{ operationNote.note_status.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 w-1/2">
                            <div class="grid grid-cols-2 gap-2 items-center">
                                <span class="text-lg font-semibold">
                                    Ответственный:
                                </span>

                                <span>{{ operationNote.author.name }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 items-center">
                                <span class="text-lg font-semibold">
                                    Дата начала:
                                </span>

                                <span>
                                    {{ formatDate(operationNote.start_date) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 items-center">
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
                        :note-id="props.id"
                        :field-id="operationNote.field_id"
                        :is-show-map="false"
                        :is-available-add="false"
                        :is-available-delete="false"
                        :show-expected-time="false"
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
