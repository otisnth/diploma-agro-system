<script setup>
import { inject, onMounted, ref, watch } from "vue";
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

const showObject = ref(null);
const imagesPreview = ref({});

const onUploadFiles = async (event, key) => {
    for (const field of formFields.value) {
        if (field.key == key) {
            imagesPreview.value[field.key] = event.files[0].objectURL;
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

const onClearFiles = (key) => {
    if (showObject.value) {
        imagesPreview.value[key] = showObject.value[key];
        formFields.value.find((field) => field.key == key).value =
            showObject.value[key];
    } else {
        imagesPreview.value[key] = null;
        formFields.value.find((field) => field.key == key).value = null;
    }
};

watch(
    formFields,
    () => {
        for (const field of formFields.value) {
            if (field.value && field.error) {
                field.error = false;
            }
        }
    },
    { deep: true }
);

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
                    showObject.value = response.data.data;
                    for (const field of formFields.value) {
                        if (field.type === "select") {
                            const valueId = showObject.value[field.key];
                            field.value = field.values.find(
                                (item) => item.id == valueId
                            );
                        } else {
                            field.value = showObject.value[field.key];
                        }
                        if (field.type === "image") {
                            imagesPreview.value[field.key] = field.value;
                        }
                    }
                    isLoaded.value = true;
                });
        });
});

const saveClickHandler = () => {
    for (const field of formFields.value) {
        if (!field.value && field.required) {
            field.error = true;
        }
    }

    if (formFields.value.find((item) => item.error)) {
        toastService.showErrorToast(
            "Ошибка",
            "Заполните все обязательные поля"
        );
        return;
    }

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
                    <InputText
                        :id="item.key"
                        v-model="item.value"
                        :invalid="item.error"
                    />
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
                    <div class="flex items-center">
                        <img
                            class="max-h-16 max-w-16"
                            :src="imagesPreview[item.key]"
                        />
                        <FileUpload
                            :name="item.key"
                            customUpload
                            @select="onUploadFiles($event, item.key)"
                            @clear="onClearFiles(item.key)"
                            accept="image/*"
                            :maxFileSize="1000000"
                            :pt="{
                                content: 'hidden',
                            }"
                        >
                            <template
                                #header="{
                                    chooseCallback,
                                    clearCallback,
                                    files,
                                }"
                            >
                                <div
                                    class="flex flex-wrap justify-between items-center flex-1 gap-2"
                                >
                                    <div class="flex gap-2">
                                        <Button
                                            @click="chooseCallback()"
                                            icon="pi pi-images"
                                            rounded
                                            outlined
                                        ></Button>
                                        <Button
                                            @click="clearCallback()"
                                            icon="pi pi-times"
                                            rounded
                                            outlined
                                            severity="danger"
                                            v-if="files.length"
                                        ></Button>
                                    </div>
                                </div>
                            </template>
                        </FileUpload>
                    </div>
                </div>

                <div
                    v-else-if="item.type === 'select'"
                    class="flex flex-col gap-1"
                >
                    <label :for="item.key">{{ item.title }}</label>

                    <Dropdown
                        :invalid="item.error"
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
                        :invalid="item.error"
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

.p-fileupload-buttonbar
    border none
</style>
