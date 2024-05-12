<script setup>
import { ref } from "vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import WindIcon from "@/Components/WindIcon.vue";
import WaterIcon from "@/Components/WaterIcon.vue";
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
            <h2 class="font-semibold text-2xl leading-tight">Главная</h2>
        </template>

        <div class="py-12">
            <div class="flex flex-col gap-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-neutral-700 shadow-xl sm:rounded-lg"
                >
                    <h3 class="font-semibold text-xl leading-tight mt-2 ml-6">
                        Прогноз погоды
                    </h3>
                    <Carousel
                        class="pt-4"
                        :value="forecast"
                        :numVisible="4"
                        :numScroll="1"
                        :responsiveOptions="responsiveOptions"
                        circular
                        :autoplayInterval="9000"
                    >
                        <template #item="slotProps">
                            <div
                                class="m-2 p-3 flex flex-col bg-neutral-600 rounded-2xl"
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
                        </template>
                    </Carousel>
                </div>

                <div
                    class="overflow-hidden bg-neutral-700 shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">You're logged in!</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="stylus">
.p-carousel-indicator.p-highlight button
    background-color #a3e635

.p-carousel-indicator button
    background-color #a1a1aa
</style>
