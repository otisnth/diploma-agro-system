<script setup>
import { inject, onMounted, ref } from "vue";
import axios from "axios";
import Skeleton from "primevue/skeleton";
import InputText from "primevue/inputtext";
import ColorPicker from "primevue/colorpicker";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";
import toastService from "@/Services/toastService";

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const formFields = ref(null);

onMounted(() => {
    axios.get(route(`api.${props.id}.properties`)).then((response) => {
        if (response.data) {
            formFields.value = response.data.data;
            for (const field of formFields.value) {
                field.value = null;
                if (field.type === "select") {
                    field.values = null;
                    axios
                        .get(`${route(`api.${field.source}.index`)}?limit=all`)
                        .then((response) => {
                            if (response.data) {
                                field.values = response.data.data;
                            }
                        });
                }
            }
        }
    });
});

const saveClickHandler = () => {
    const data = formFields.value.reduce((acc, item) => {
        acc[item.key] = item.value?.id || item.value;
        return acc;
    }, {});

    axios
        .post(route(`api.${props.id}.store`), data)
        .then(() => {
            toastService.showSuccessToast(
                "Успешное добавление",
                "Сведения были сохранены в системе"
            );
        })
        .catch(() => {
            toastService.showErrorToast(
                "Ошибка",
                "Что-то пошло не так. Проверьте данные и повторите попытку позднее"
            );
        });
};
</script>

<template>
    <div class="flex flex-col gap-2 w-96 h-full">
        <h3 class="font-semibold text-lg text-800 leading-tight">
            {{ props.name }}
        </h3>
        <div v-if="formFields" class="flex flex-col gap-2">
            <div v-for="(item, index) in formFields" :key="index">
                <div v-if="item.type === 'text'" class="flex flex-col gap-1">
                    <label :for="item.key">{{ item.title }}</label>
                    <InputText :id="item.key" v-model="item.value" />
                </div>

                <div v-if="item.type === 'color'" class="flex flex-col gap-1">
                    <label :for="item.key">{{ item.title }}</label>
                    <ColorPicker :id="item.key" v-model="item.value" />
                </div>

                <div v-if="item.type === 'select'" class="flex flex-col gap-1">
                    <label :for="item.key">{{ item.title }}</label>

                    <Dropdown
                        :id="item.key"
                        filter
                        :options="item.values"
                        optionLabel="name"
                        v-model="item.value"
                    />
                </div>

                <div v-if="item.type === 'number'" class="flex flex-col gap-1">
                    <label :for="item.key">{{ item.title }}</label>
                    <InputNumber
                        v-model="item.value"
                        :inputId="item.key"
                        :min="item.inputProperties.min"
                        :max="item.inputProperties.max"
                        :suffix="item.inputProperties.suffix"
                        :prefix="item.inputProperties.prefix"
                    />
                </div>
            </div>
            <Button class="mt-3" label="Сохранить" @click="saveClickHandler" />
        </div>
        <Skeleton v-else width="100%" height="190px"></Skeleton>
    </div>
</template>

<style lang="stylus">
.reference-form
    min-height 240px
</style>
