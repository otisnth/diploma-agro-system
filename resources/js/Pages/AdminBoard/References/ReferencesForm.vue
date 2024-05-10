<script setup>
import { inject, onMounted, ref } from "vue";
import axios from "axios";
import Skeleton from "primevue/skeleton";
import InputText from "primevue/inputtext";
import ColorPicker from "primevue/colorpicker";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";
import FileUpload from "primevue/fileupload";
import toastService from "@/Services/toastService";

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const formFields = ref(null);

const isLoaded = ref(false);

const onUploadFiles = async (event, key) => {
    for (const field of formFields.value) {
        if (field.key == key) {
            if (!props.edit) {
                field.value = event.files;
                return;
            }
            const file = event.files[0];
            const reader = new FileReader();
            let blob = await fetch(file.objectURL).then((r) => r.blob());

            reader.readAsDataURL(blob);

            reader.onloadend = function () {
                field.value = reader.result;
            };
        }
    }
};

onMounted(() => {
    axios
        .get(route(`api.${props.id}.properties`))
        .then((response) => {
            if (response.data) {
                formFields.value = response.data.data;
            }
        })
        .then(() => {
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
        })
        .then(() => {
            if (!props.edit) {
                isLoaded.value = true;
                return;
            }
            axios
                .get(route(`api.${props.id}.show`, props.item))
                .then((response) => {
                    if (!response.data) {
                        return;
                    }
                    for (const field of formFields.value) {
                        if (field.type === "select") {
                            const valueId = response.data.data[field.key];
                            field.value = field.values.find(
                                (item) => item.id == valueId
                            );
                        } else {
                            field.value = response.data.data[field.key];
                        }
                    }
                    isLoaded.value = true;
                });
        });
});

const saveClickHandler = () => {
    const data = formFields.value.reduce((acc, item) => {
        acc[item.key] = item.value?.id || item.value;
        return acc;
    }, {});

    if (!props.edit) {
        axios
            .post(route(`api.${props.id}.store`), data, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
            .then(() => {
                toastService.showSuccessToast(
                    "Успешное добавление",
                    "Сведения были сохранены в системе"
                );
                dialogRef.value.close();
            })
            .catch(() => {
                toastService.showErrorToast(
                    "Ошибка",
                    "Что-то пошло не так. Проверьте данные и повторите попытку позднее"
                );
            });
    } else {
        axios
            .put(route(`api.${props.id}.update`, props.item), data, {
                // headers: {
                //     "Content-Type": "multipart/form-data",
                // },
            })
            .then(() => {
                toastService.showSuccessToast(
                    "Успешное обновление",
                    "Сведения были обновлены в системе"
                );
                dialogRef.value.close();
            })
            .catch(() => {
                toastService.showErrorToast(
                    "Ошибка",
                    "Что-то пошло не так. Проверьте данные и повторите попытку позднее"
                );
            });
    }
};
</script>

<template>
    <div class="flex flex-col gap-2 w-96 h-full">
        <h3 class="font-semibold text-lg text-800 leading-tight">
            {{ props.name }}
        </h3>
        <div v-if="isLoaded" class="flex flex-col gap-2">
            <div v-for="(item, index) in formFields" :key="index">
                <div v-if="item.type === 'text'" class="flex flex-col gap-1">
                    <label :for="item.key">{{ item.title }}</label>
                    <InputText :id="item.key" v-model="item.value" />
                </div>

                <div
                    v-else-if="item.type === 'color'"
                    class="flex flex-col gap-1"
                >
                    <label :for="item.key">{{ item.title }}</label>
                    <ColorPicker :id="item.key" v-model="item.value" />
                </div>

                <div
                    v-else-if="item.type === 'image'"
                    class="flex flex-col gap-1"
                >
                    <label :for="item.key">{{ item.title }}</label>
                    <FileUpload
                        mode="basic"
                        :name="item.key"
                        customUpload
                        @select="onUploadFiles($event, item.key)"
                        accept="image/*"
                        :maxFileSize="1000000"
                    />
                </div>

                <div
                    v-else-if="item.type === 'select'"
                    class="flex flex-col gap-1"
                >
                    <label :for="item.key">{{ item.title }}</label>

                    <Dropdown
                        :id="item.key"
                        filter
                        :options="item.values"
                        optionLabel="name"
                        v-model="item.value"
                    />
                </div>

                <div
                    v-else-if="item.type === 'number'"
                    class="flex flex-col gap-1"
                >
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
