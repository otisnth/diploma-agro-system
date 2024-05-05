<script setup>
import { inject, onMounted, ref } from "vue";
import axios from "axios";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const dialogRef = inject("dialogRef");

const props = dialogRef.value.data;

const referenceData = ref(null);
const tableColumns = ref(null);

onMounted(() => {
    axios
        .get(route(`api.${props.id}.properties`))
        .then((response) => {
            if (response.data) {
                tableColumns.value = response.data.data;
            }
        })
        .then(() => {
            axios.get(route(`api.${props.id}.index`)).then((response) => {
                if (response.data.length != 0)
                    referenceData.value = response.data.data;
            });
        });
});
</script>

<template>
    <div>
        <h3 class="font-semibold text-lg text-800 leading-tight">
            {{ props.name }}
        </h3>
        <div>
            <div v-if="referenceData">
                <DataTable :value="referenceData">
                    <Column
                        v-for="(item, index) in tableColumns"
                        :key="index"
                        :field="item.key"
                        :header="item.title"
                    >
                        <template #body="{ data }">
                            <template v-if="item.type === 'text'">
                                {{ data[item.key] }}
                            </template>

                            <template v-if="item.type === 'number'">
                                {{ item.inputProperties.prefix }}
                                {{ data[item.key] }}
                                {{ item.inputProperties.suffix }}
                            </template>

                            <img
                                v-if="item.type === 'image'"
                                class="max-h-16 max-w-16"
                                :src="data[item.key]"
                                alt=""
                            />

                            <div
                                v-else-if="item.type === 'color'"
                                class="w-6 h-6 rounded-md"
                                :style="{
                                    'background-color': '#' + data[item.key],
                                }"
                            ></div>
                        </template>
                    </Column>
                </DataTable>
            </div>
            <Skeleton v-else width="100%"></Skeleton>
        </div>
    </div>
</template>
