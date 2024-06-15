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

const confirm = useConfirm();

const props = defineProps({
    posts: Array,
    id: String,
});

const personal = ref(null);
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

const fetchPersonal = () => {
    axios
        .get(`/api/users/${props.id}`)
        .then((response) => {
            personal.value = response.data.data;
            isLoaded.value = true;
        })
        .catch((error) => {});
};

onMounted(() => {
    fetchPersonal();
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
                <Link
                    :href="route('personal.index')"
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
                    {{ personal }}
                </div>

                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                ></div>
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
