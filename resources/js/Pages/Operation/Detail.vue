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

const today = new Date();

const operationNote = ref(null);
const isLoaded = ref(false);

const chartOptions = ref();
const chartData = ref();

const adminList = ref();
const isAuthorEdit = ref(false);
const isStartDateEdit = ref(false);
const isNoteEdited = ref(false);
const nextFieldStatus = ref(null);
const recommendations = ref(null);

const isShowNextFieldStatus = ref(false);

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

const noteStatusCanceled = computed(
    () => operationNote.value.status == "canceled"
);

const noteStatusInProgress = computed(
    () => operationNote.value.status == "inProgress"
);

const isAvailableAddWorkerUnits = computed(() =>
    ["inProgress", "assigned", "planned"].includes(operationNote.value.status)
);

const isAvailableDeleteWorkerUnits = computed(
    () => !["canceled", "completed"].includes(operationNote.value.status)
);

const nextFieldStatuses = computed(() => {
    if (operationNote.value.note_operation.isNeedField) {
        return props.fieldStatuses.filter((status) =>
            operationNote.value.note_operation.nextFieldStatus.includes(
                status.id
            )
        );
    }
    return [];
});

const completeIsAvailable = computed(() => {
    return (
        ["awaitConfirm", "inProgress"].includes(operationNote.value.status) &&
        (usePage().props.auth.user.post == "owner" ||
            usePage().props.auth.user.id == operationNote.value.created_by)
    );
});

const showNextFieldStatusHandler = () => {
    if (!operationNote.value.note_operation.isNeedField) {
        confirmNoteComplete();
    } else {
        isShowNextFieldStatus.value = true;
    }
};

const changeStatusAvailable = computed(() => {
    return (
        ["inProgress", "assigned", "planned", "awaitConfirm"].includes(
            operationNote.value.status
        ) &&
        (usePage().props.auth.user.post == "owner" ||
            usePage().props.auth.user.id == operationNote.value.created_by)
    );
});

const editingIsAvailable = computed(() => {
    return (
        ["inProgress", "assigned", "planned"].includes(
            operationNote.value.status
        ) &&
        (usePage().props.auth.user.post == "owner" ||
            usePage().props.auth.user.id == operationNote.value.created_by)
    );
});

const cancelChangeHandler = () => {
    fetchOperationNote();
    isStartDateEdit.value = false;
    isAuthorEdit.value = false;
    isNoteEdited.value = false;
};

const saveChangeHandler = () => {
    if (operationNote.value.start_date) {
        const utcDate = new Date(
            Date.UTC(
                operationNote.value.start_date.getFullYear(),
                operationNote.value.start_date.getMonth(),
                operationNote.value.start_date.getDate()
            )
        );
        operationNote.value.start_date = utcDate;
    }
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
            fetchOperationNote();
        })
        .catch((e) => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Проверьте данные и повторите попытку позже"
            );
        });
};

const confirmNoteCancel = () => {
    confirm.require({
        message: "Вы действительно хотите отменить данное мероприятие?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-danger",
        rejectLabel: "Назад",
        acceptLabel: "Отменить",
        accept: () => {
            axios
                .patch(
                    route("api.operation-notes.update", operationNote.value.id),
                    { status: "canceled" }
                )
                .then(() => {
                    toastService.showSuccessToast(
                        "Успешная отмена",
                        "Мероприятие отменено"
                    );
                    operationNote.value.status = "canceled";
                    fetchOperationNote();
                    isStartDateEdit.value = false;
                    isAuthorEdit.value = false;
                    isNoteEdited.value = false;
                })
                .catch((e) => {
                    if (e?.response?.data?.error) {
                        toastService.showErrorToast(
                            "Ошибка",
                            e.response.data.error
                        );
                    } else {
                        toastService.showErrorToast(
                            "Ошибка",
                            "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                        );
                    }
                });
        },
    });
};

const confirmNoteComplete = () => {
    if (
        !nextFieldStatus.value &&
        operationNote.value.note_operation.isNeedField
    ) {
        toastService.showErrorToast(
            "Ошибка",
            "Выберите следующее состояние поля"
        );
        return;
    }
    confirm.require({
        message: "Вы действительно хотите завершить данное мероприятие?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-success",
        rejectLabel: "Назад",
        acceptLabel: "Завершить",
        accept: () => {
            axios
                .patch(
                    route("api.operation-notes.update", operationNote.value.id),
                    { status: "completed" }
                )
                .then(() => {
                    operationNote.value.status = "completed";
                    isShowNextFieldStatus.value = false;
                    toastService.showSuccessToast(
                        "Успешное завершение",
                        "Мероприятие завершено"
                    );
                    fetchOperationNote();
                    isStartDateEdit.value = false;
                    isAuthorEdit.value = false;
                    isNoteEdited.value = false;

                    if (operationNote.value.note_operation.isNeedField) {
                        axios
                            .patch(
                                route(
                                    "api.fields.update",
                                    operationNote.value.field_id
                                ),
                                {
                                    status: nextFieldStatus.value,
                                    ...(operationNote.value.sort_id && {
                                        sort_id: operationNote.value.sort_id,
                                    }),
                                }
                            )
                            .then(() => {})
                            .catch((e) => {
                                toastService.showErrorToast(
                                    "Ошибка",
                                    "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                                );
                            });
                    }
                })
                .catch((e) => {
                    toastService.showErrorToast(
                        "Ошибка",
                        "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                    );
                });
        },
        reject: () => {
            isShowNextFieldStatus.value = false;
        },
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

const fetchAdminList = () => {
    axios
        .post("/api/users/search", {
            filters: [{ field: "post", operator: "=", value: "admin" }],
            limit: "all",
        })
        .then((response) => {
            adminList.value = response.data.data;
        })
        .catch((error) => {});
};

const getRecommendations = () => {
    recommendations.value = null;
    let data = {};

    if (operationNote.value.field_id) {
        data.field = operationNote.value.field_id;
    } else {
        return;
    }

    data.operation = operationNote.value.operation;

    if (data.operation === "seeding") {
        data.plant = operationNote.value.sort.plant_id;
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

watch(
    () => isStartDateEdit.value,
    () => {
        isAuthorEdit.value = false;
        isNoteEdited.value = true;
    }
);

watch(
    () => isAuthorEdit.value,
    () => {
        isStartDateEdit.value = false;
        isNoteEdited.value = true;
    }
);

watch(
    () => operationNote.value,
    (cur, old) => {
        if (!old) {
            return;
        }
        operationNote.value.created_by = operationNote.value.author.id;
    },
    { deep: true }
);

onMounted(() => {
    fetchOperationNote();
    fetchAdminList();
    chartOptions.value = setChartOptions();
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
                    :href="route('operation.index')"
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
                                <Link
                                    :href="
                                        route(
                                            'field.detail',
                                            operationNote.field.id
                                        )
                                    "
                                >
                                    <u>{{ operationNote.field.name }}</u>
                                </Link>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Статус:
                                </span>
                                <div class="flex flex-col gap-2">
                                    <span>
                                        {{ operationNote.note_status.name }}
                                    </span>

                                    <Tag
                                        v-if="editingIsAvailable"
                                        @click="confirmNoteCancel"
                                        class="self-start cursor-pointer"
                                        severity="danger"
                                        value="Отменить"
                                    ></Tag>

                                    <div v-if="completeIsAvailable">
                                        <Tag
                                            v-if="!isShowNextFieldStatus"
                                            @click="showNextFieldStatusHandler"
                                            class="self-start cursor-pointer"
                                            severity="success"
                                            value="Завершить"
                                        ></Tag>

                                        <div v-else class="flex items-center">
                                            <Dropdown
                                                id="fieldStatus"
                                                class="w-48"
                                                v-model="nextFieldStatus"
                                                :options="nextFieldStatuses"
                                                optionLabel="name"
                                                optionValue="id"
                                                placeholder="Состояние поля"
                                                pt:root:class="border-0 shadow-none"
                                                pt:input:class="p-0"
                                                required
                                            />

                                            <i
                                                v-if="changeStatusAvailable"
                                                class="cursor-pointer pi pi-check"
                                                @click="confirmNoteComplete"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 w-1/2">
                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Ответственный:
                                </span>

                                <div class="grid-line">
                                    <Dropdown
                                        v-if="isAuthorEdit"
                                        class="w-64"
                                        id="author"
                                        v-model="operationNote.author"
                                        :options="adminList"
                                        optionLabel="name"
                                        placeholder="Выберите ответственного"
                                        pt:root:class="border-0 shadow-none"
                                        pt:input:class="p-0"
                                        required
                                        filter
                                    />
                                    <Link
                                        v-else
                                        class="w-64"
                                        :href="
                                            route(
                                                'personal.detail',
                                                operationNote.author.id
                                            )
                                        "
                                    >
                                        <u>{{ operationNote.author.name }}</u>
                                    </Link>

                                    <i
                                        v-if="changeStatusAvailable"
                                        class="cursor-pointer pi"
                                        :class="
                                            isAuthorEdit
                                                ? 'pi-check'
                                                : 'pi-pen-to-square'
                                        "
                                        @click="isAuthorEdit = !isAuthorEdit"
                                    ></i>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Дата начала:
                                </span>
                                <div class="grid-line">
                                    <Calendar
                                        v-if="isStartDateEdit"
                                        class="w-64"
                                        id="startDate"
                                        v-model="operationNote.start_date"
                                        showIcon
                                        iconDisplay="input"
                                        required
                                        :min-date="today"
                                        pt:input:class="p-0 border-0 shadow-none"
                                    />
                                    <span class="w-64" v-else>
                                        {{
                                            formatDate(operationNote.start_date)
                                        }}
                                    </span>

                                    <i
                                        v-if="editingIsAvailable"
                                        class="cursor-pointer pi"
                                        :class="
                                            isStartDateEdit
                                                ? 'pi-check'
                                                : 'pi-pen-to-square'
                                        "
                                        @click="
                                            isStartDateEdit = !isStartDateEdit
                                        "
                                    ></i>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <span class="text-lg font-semibold">
                                    Дата окончания:
                                </span>

                                <span class="w-64">
                                    {{ formatDate(operationNote.end_date) }}
                                </span>
                            </div>
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

                    <div class="flex gap-2 items-end h-full mt-4">
                        <Button
                            v-if="noteStatusCanceled"
                            @click="confirmDeleteNote"
                            severity="danger"
                            label="Удалить"
                        />

                        <Button
                            v-if="isNoteEdited"
                            @click="saveChangeHandler"
                            label="Сохранить изменения"
                        />

                        <Button
                            v-if="isNoteEdited"
                            @click="cancelChangeHandler"
                            severity="secondary"
                            label="Отменить изменения"
                        />
                    </div>
                </div>
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <WorkerUnits
                        :note-id="props.id"
                        :field-id="operationNote.field_id"
                        :is-show-map="noteStatusInProgress"
                        :is-available-add="isAvailableAddWorkerUnits"
                        :is-available-delete="isAvailableDeleteWorkerUnits"
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
