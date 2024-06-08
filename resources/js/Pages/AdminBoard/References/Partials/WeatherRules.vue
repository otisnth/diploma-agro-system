<script setup>
import { ref } from "vue";
import InputNumber from "primevue/inputnumber";

const props = defineProps({
    rules: Object,
});

const inputSettings = ref({
    humidity: {
        max: 100,
        min: 0,
        suffix: "%",
    },
    temperature: {
        max: 40,
        min: -40,
        suffix: "°C",
    },
});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div v-for="(operation, oIndex) in props.rules" :key="oIndex">
            <u>{{ operation.title }}</u>
            <div
                class="p-2 border-2 mt-2"
                v-for="(weatherProps, wIndex) in operation.propeties"
                :key="wIndex"
            >
                <span>{{ weatherProps.title }}</span>
                <div class="flex flex-col gap-1">
                    <label>Идеальное значение</label>
                    <InputNumber
                        v-model="weatherProps.ideal_value"
                        :min="inputSettings[wIndex].min"
                        :max="inputSettings[wIndex].max"
                        :suffix="inputSettings[wIndex].suffix"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label>Вес показателя</label>
                    <InputNumber
                        v-model="weatherProps.weight"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
