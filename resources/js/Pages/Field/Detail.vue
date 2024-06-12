<script setup>
import { onMounted, ref, computed } from "vue";
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

const props = defineProps({
    fieldStatuses: Array,
    id: String,
});

const field = ref(null);
const isLoaded = ref(false);

function formatNumberWithSpaces(number) {
    let integerPart = Math.floor(number).toString();

    return integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

const fetchField = (filters = []) => {
    axios
        .get(`/api/fields/${props.id}?include=sort,sort.plant`)
        .then((response) => {
            field.value = response.data.data;
            isLoaded.value = true;
        })
        .catch((error) => {});
};

const previewField = computed(() => {
    return [field.value];
});

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
                            <h2 class="font-semibold text-xl">
                                {{ field.name }}
                            </h2>

                            <div>
                                <span>Площадь: </span>
                                <span
                                    >{{
                                        formatNumberWithSpaces(field.square)
                                    }}
                                    м&sup2;</span
                                >
                            </div>

                            <div>
                                <span>Состояние поля: </span>
                                <span>{{ field.field_status.name }}</span>
                            </div>

                            <div v-if="field.sort">
                                <div>
                                    <span>Культура: </span>
                                    <span>{{ field.sort.plant.name }}</span>
                                </div>

                                <div>
                                    <span>Сорт: </span>
                                    <span>{{ field.sort.name }}</span>
                                </div>
                            </div>

                            <div>
                                <span>Мероприятие: </span>
                            </div>
                        </div>

                        <Map class="w-1/2" :fields="[field]" height="400px" />
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
                            <p class="m-0"></p>
                        </AccordionTab>
                    </Accordion>
                </div>

                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <h2 class="font-semibold text-lg">Севооборот</h2>
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
