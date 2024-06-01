<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";
import L from "leaflet";

const props = defineProps({
    width: {
        type: String,
        default: "100%",
    },
    height: {
        type: String,
        default: "800px",
    },
    fields: {
        type: Array,
    },
});

const map = ref();
const mapContainer = ref();
const fieldsLayers = ref([]);

onMounted(() => {
    mapContainer.value.style.width = props.width;
    mapContainer.value.style.height = props.height;

    map.value = L.map(mapContainer.value, {
        zoomControl: false,
    }).setView([52.609025, 39.59897], 13);
    map.value.attributionControl.setPrefix(
        '<a href="https://leafletjs.com/index.html">Leaflet</a>'
    );

    // L.tileLayer(
    //     "https://tiles.stadiamaps.com/tiles/alidade_satellite/{z}/{x}/{y}{r}.{ext}",
    //     {
    //         minZoom: 0,
    //         maxZoom: 20,
    //         attribution:
    //             '&copy; CNES, Distribution Airbus DS, © Airbus DS, © PlanetObserver (Contains Copernicus Data) | &copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    //         ext: "jpg",
    //     }
    // ).addTo(map.value);

    // L.tileLayer("https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png", {
    //     maxZoom: 17,
    //     attribution:
    //         'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)',
    // }).addTo(map.value);

    // L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    //     maxZoom: 19,
    //     attribution:
    //         '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    // }).addTo(map.value);

    // L.tileLayer("http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}", {
    //     maxZoom: 19,
    //     subdomains: ["mt0", "mt1", "mt2", "mt3"],
    // }).addTo(map.value);

    L.tileLayer("http://map.otisnth.ru/tile/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map.value);

    L.control
        .zoom({
            position: "topright",
        })
        .addTo(map.value);
});

watch(
    () => props.fields,
    () => {
        fieldsLayers.value.forEach(function (layer) {
            map.value.removeLayer(layer);
        });
        fieldsLayers.value = [];

        for (const field of props.fields) {
            const color = field?.color || "#84cc16";

            const layer = L.geoJSON(field.coords, {
                style: function (feature) {
                    return {
                        color: color,
                        weight: 1,
                        fillColor: color,
                        fillOpacity: 0.5,
                    };
                },
            }).addTo(map.value);
            fieldsLayers.value.push(layer);
            map.value.fitBounds(layer.getBounds());
        }
    },
    { deep: true }
);
</script>

<template>
    <div>
        <div
            ref="mapContainer"
            style="width: 100%; height: 800px; border-radius: 10px"
        ></div>
    </div>
</template>

<style>
@import url(../../css/leaflet.css);

.leaflet-touch .leaflet-bar {
    border-color: transparent;
}

.leaflet-touch .leaflet-bar a:first-child {
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
}

.leaflet-touch .leaflet-bar a:last-child {
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
}
</style>
