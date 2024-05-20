<script setup>
import { ref } from "vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import WindIcon from "@/Components/WindIcon.vue";
import WaterIcon from "@/Components/WaterIcon.vue";
import Map from "@/Components/Map.vue";
import { Head } from "@inertiajs/vue3";
import Carousel from "primevue/carousel";
import Button from "primevue/button";

defineProps({
    forecast: Object,
});

const responsiveOptions = ref([
    {
        breakpoint: "1400px",
        numVisible: 2,
        numScroll: 1,
    },
    {
        breakpoint: "1199px",
        numVisible: 3,
        numScroll: 1,
    },
    {
        breakpoint: "767px",
        numVisible: 2,
        numScroll: 1,
    },
    {
        breakpoint: "575px",
        numVisible: 1,
        numScroll: 1,
    },
]);
</script>

<template>
    <Head title="Главная" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-800 leading-tight">
                Главная
            </h2>
        </template>

        <div class="py-2">
            <div class="flex flex-col gap-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden sm:rounded-lg pt-4">
                    <h3 class="font-semibold text-xl leading-tight ml-6">
                        Прогноз погоды
                    </h3>
                    <Carousel
                        class="pt-4"
                        :value="forecast"
                        :numVisible="4"
                        :numScroll="1"
                        :responsiveOptions="responsiveOptions"
                        circular
                    >
                        <template #item="slotProps">
                            <div
                                class="m-2 flex flex-col bg-white rounded-md border-green-50 weather-card"
                            >
                                <div
                                    class="pt-3 pl-3 weather-card-header rounded-md text-white"
                                >
                                    <span>
                                        {{ slotProps.data.date }}
                                    </span>
                                    <div class="flex items-center">
                                        <img
                                            class="w-16"
                                            :src="slotProps.data.icon"
                                        />
                                        <span>
                                            {{ slotProps.data.temp }}
                                        </span>
                                    </div>
                                </div>
                                <div class="py-3 pl-3 weather-card-data">
                                    <span>
                                        Ощущается как
                                        {{ slotProps.data.feels_like }}
                                    </span>
                                    <div class="flex items-center">
                                        <WindIcon class="w-9 h-9" />
                                        <span>
                                            {{ slotProps.data.windSpeed }}
                                            {{ slotProps.data.windDir }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <WaterIcon class="w-9 h-9" />
                                        <span>
                                            {{ slotProps.data.humidity }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Carousel>
                </div>

                <div class="overflow-hidden bg-white shadow-md sm:rounded-lg">
                    <div class="p-4">
                        <Map />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="stylus">
.weather-card-header
    background linear-gradient(90deg, #acadac 0%, rgba(0, 212, 255, 0) 100%), url(/assets/images/sky.jpg)
    // linear-gradient(90deg, #acadac 0%, rgba(0, 212, 255, 0) 100%), url(/assets/images/sky.jpg)
    background-size cover
</style>

<style>
.weather-card {
    box-shadow: 0 4px 6px -1px rgb(132 204 22 / 10%),
        0 2px 4px -2px rgb(113 245 90 / 10%);
}
</style>
