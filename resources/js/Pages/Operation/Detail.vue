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
import CropRotation from "@/Pages/Field/Partials/CropRotation.vue";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import InputNumber from "primevue/inputnumber";
import InlineMessage from "primevue/inlinemessage";
import Textarea from "primevue/textarea";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Tag from "primevue/tag";
import Calendar from "primevue/calendar";

const confirm = useConfirm();

const props = defineProps({
    operationStatuses: Array,
    operations: Array,
    id: String,
});

const operationNote = ref(null);
const isLoaded = ref(false);

const adminList = ref();
const isAuthorEdit = ref(false);
const isStartDateEdit = ref(false);
const isEndDateEdit = ref(false);
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

const noteStatusCanceled = computed(
    () => operationNote.value.status == "canceled"
);

const editingIsAvailable = computed(() => {
    return (
        ["inProgress", "assigned", "planned"].includes(
            operationNote.value.status
        ) &&
        (usePage().props.auth.user.post == "owner" ||
            usePage().props.auth.user.id == operationNote.value.created_by)
    );
});

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

const fetchOperationNote = () => {
    axios
        .get(`/api/operation-notes/${props.id}?include=field,author`)
        .then((response) => {
            response.data.data.start_date = prepareDate(
                response.data.data.start_date
            );
            response.data.data.end_date = prepareDate(
                response.data.data.end_date
            );
            operationNote.value = response.data.data;
            isLoaded.value = true;
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

watch(
    () => isEndDateEdit.value,
    () => {
        isStartDateEdit.value = false;
        isAuthorEdit.value = false;
    }
);

watch(
    () => isStartDateEdit.value,
    () => {
        isEndDateEdit.value = false;
        isAuthorEdit.value = false;
    }
);

watch(
    () => isAuthorEdit.value,
    () => {
        isStartDateEdit.value = false;
        isEndDateEdit.value = false;
    }
);

watch(
    () => operationNote.value,
    (cur, old) => {
        if (!old) {
            return;
        }
        isNoteEdited.value = true;
        operationNote.value.created_by = operationNote.value.author.id;
    },
    { deep: true }
);

onMounted(() => {
    fetchOperationNote();
    fetchAdminList();
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
                    <div>
                        <h2 class="font-semibold text-2xl">
                            {{ operationNote.note_operation.name }}
                        </h2>
                    </div>

                    <div class="flex gap-2">
                        <div class="flex flex-col gap-2 w-1/2">
                            <div class="grid grid-cols-2 gap-2">
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
                                <span>
                                    {{ operationNote.note_status.name }}
                                </span>
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
                                        v-if="editingIsAvailable"
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
                                <div class="grid-line">
                                    <Calendar
                                        v-if="isEndDateEdit"
                                        class="w-64"
                                        id="startDate"
                                        v-model="operationNote.end_date"
                                        showIcon
                                        iconDisplay="input"
                                        required
                                        pt:input:class="p-0 border-0 shadow-none"
                                    />
                                    <span class="w-64" v-else>
                                        {{ formatDate(operationNote.end_date) }}
                                    </span>

                                    <i
                                        v-if="editingIsAvailable"
                                        class="cursor-pointer pi"
                                        :class="
                                            isEndDateEdit
                                                ? 'pi-check'
                                                : 'pi-pen-to-square'
                                        "
                                        @click="isEndDateEdit = !isEndDateEdit"
                                    ></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 items-end h-full">
                        <Button
                            v-if="noteStatusCanceled"
                            severity="danger"
                            label="Удалить"
                        />

                        <Button
                            v-if="isNoteEdited"
                            label="Сохранить изменения"
                        />

                        <Button
                            v-if="isNoteEdited"
                            severity="secondary"
                            label="Отменить изменения"
                        />
                    </div>
                </div>
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    {{ operationNote }}
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
