<script setup>
import { inject, onMounted, ref } from "vue";
import axios from "axios";
import Skeleton from "primevue/skeleton";

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const referenceData = ref(null);

onMounted(() => {
    axios.get(route(`api.${props.id}.index`)).then((response) => {
        if (response.data.length != 0) referenceData.value = response.data.data;
    });
});
</script>

<template>
    <div>
        <h3 class="font-semibold text-lg text-800 leading-tight">
            {{ props.name }}
        </h3>
        <div>
            <div v-if="referenceData">{{ referenceData }}</div>
            <Skeleton v-else width="100%"></Skeleton>
        </div>
    </div>
</template>
