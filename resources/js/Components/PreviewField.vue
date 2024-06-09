<script setup>
import { ref, computed } from "vue";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";
import Map from "@/Components/Map.vue";
import toastService from "@/Services/toastService";
import axios from "axios";
import Button from "primevue/button";

const confirm = useConfirm();

const props = defineProps({
    field: Object,
    showControls: {
        type: Boolean,
        default: true,
    },
    orientation: {
        type: String,
        default: "vertical",
    },
});

const previewField = computed(() => {
    return [props.field];
});

function formatNumberWithSpaces(number) {
    let integerPart = Math.floor(number).toString();

    return integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

const isFieldEmpty = computed(() => {
    return !props.field || Object.keys(props.field).length == 0;
});

const fieldInfo = computed(() => {
    let infoArr = [];
    const { field } = props;

    if (!isFieldEmpty) return infoArr;

    infoArr.push({
        title: "Площадь: ",
        value: `${formatNumberWithSpaces(field.square)} м&sup2;`,
    });

    infoArr.push({
        title: "Состояние: ",
        value: `${field.field_status.name}`,
    });

    if (field.sort) {
        infoArr.push({
            title: "Культура: ",
            value: `${field.sort.plant.name}`,
        });
        infoArr.push({
            title: "Сорт: ",
            value: `${field.sort.name}`,
        });
    }

    return infoArr;
});

const confirmFieldDelete = () => {
    confirm.require({
        message: "Вы действительно хотите удалить запись о данном участке?",
        header: "Внимание",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined",
        acceptClass: "p-button-danger",
        rejectLabel: "Отмена",
        acceptLabel: "Удалить",
        accept: () => {
            axios
                .delete(route(`api.fields.destroy`, props.field.id))
                .then(() => {
                    fetchFields();
                    toastService.showSuccessToast(
                        "Успешное удаления",
                        "Запись об участке удалена"
                    );
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
</script>

<template>
    <div class="flex" :class="{ 'flex-col': props.orientation == 'vertical' }">
        <Map
            :class="{ 'w-1/2': props.orientation == 'horizontal' }"
            :fields="previewField"
            height="400px"
        />
        <div
            v-if="!isFieldEmpty"
            class="flex p-2 justify-between"
            :class="{ 'w-1/2': props.orientation == 'horizontal' }"
        >
            <div>
                <div v-for="(item, index) in fieldInfo" :key="index">
                    <span class="font-semibold">{{ item.title }}</span>
                    <span v-html="item.value"></span>
                </div>
            </div>

            <div class="flex flex-col gap-2" v-if="props.showControls">
                <Button
                    severity="info"
                    label="Подробнее"
                    icon="pi pi-info-circle"
                />
                <Button
                    @click="confirmFieldDelete"
                    severity="danger"
                    label="Удалить"
                    icon="pi pi-trash"
                />
            </div>
        </div>

        <ConfirmDialog></ConfirmDialog>
    </div>
</template>
