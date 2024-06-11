<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";
import L from "leaflet";
import "leaflet-draw";

const emit = defineEmits(["changeSquare"]);

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
    technics: {
        type: [Array, Object],
    },
    isShowPopups: {
        type: Boolean,
        default: false,
    },
    isShowLegend: {
        type: Boolean,
        default: false,
    },
    getFieldSquare: {
        type: Boolean,
        default: false,
    },
});

const map = ref();
const mapContainer = ref();
const fieldsLayers = ref([]);
const technicsLayers = ref([]);
const mapLegend = ref();

const colorScheme = ref("plant");

const plantList = ref();
const fieldStatusList = ref();

const initMap = () => {
    map.value = L.map(mapContainer.value, {
        zoomControl: false,
    }).setView([52.6018, 39.504708], 13);

    map.value.attributionControl.setPrefix(
        '<a href="https://leafletjs.com/index.html">Leaflet</a>'
    );

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
};

const fetchLists = async () => {
    await axios
        .get(route("api.plants.index"), {
            limit: "all",
        })
        .then((response) => {
            plantList.value = response.data.data;
            plantList.value.push({
                name: "Пары",
                color: "84cc16",
            });
        })
        .catch((error) => {});

    await axios
        .get(route("api.fields.statuses"))
        .then((response) => {
            fieldStatusList.value = response.data.data;
        })
        .catch((error) => {});
};

const updateLegend = (type) => {
    colorScheme.value = type;

    var content = document.querySelector(".legend-content");
    if (!content) return;

    content.innerHTML = "";

    if (type === "plant") {
        for (const i in plantList.value) {
            content.innerHTML +=
                "<div>" +
                '<i style="background:' +
                "#" +
                plantList.value[i].color +
                '"></i> ' +
                plantList.value[i].name +
                "<br>" +
                "</div>";
        }
    } else {
        for (const i in fieldStatusList.value) {
            content.innerHTML +=
                "<div>" +
                '<i style="background:' +
                "#" +
                fieldStatusList.value[i].color +
                '"></i> ' +
                fieldStatusList.value[i].name +
                "<br>" +
                "</div>";
        }
    }
    renderFields();
};

const renderFields = () => {
    fieldsLayers.value.forEach(function (layer) {
        map.value.removeLayer(layer);
    });
    fieldsLayers.value = [];

    for (const field of props.fields) {
        if (!field) continue;

        let color;

        if (colorScheme.value === "plant") {
            color = field?.sort?.plant?.color
                ? `#${field.sort.plant.color}`
                : "#84cc16";
        } else {
            color = `#${field.field_status.color}`;
        }

        const layer = ref(
            L.geoJSON(field.coords, {
                style: function (feature) {
                    return {
                        color: color,
                        weight: 1,
                        fillColor: color,
                        fillOpacity: 0.5,
                    };
                },
            })
        ).value.addTo(map.value);

        if (props.isShowPopups) {
            let info = `<b>${field.name}</b>
                        <br> <b>Площадь:</b> ${field.square} м&sup2;
                        <br> <b>Состояние:</b> ${field.field_status.name}`;

            if (field.sort) {
                info = `${info}
                        <br> <b>Культура:</b> ${field.sort.plant.name}
                        <br> <b>Сорт:</b> ${field.sort.name}`;
            }

            layer.bindPopup(info).openTooltip();
        }

        fieldsLayers.value.push(layer);
    }
};

const renderTechnics = () => {
    technicsLayers.value.forEach(function (layer) {
        map.value.removeLayer(layer);
    });
    technicsLayers.value = [];

    for (const i in props.technics) {
        const technic = props.technics[i];

        if (!technic) continue;

        const technicIcon = L.icon({
            className: "technic-icon",
            iconUrl: technic.model.type.icon_path,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16],
        });

        const marker = ref(
            L.marker([technic.position.lat, technic.position.lon], {
                icon: technicIcon,
            })
        ).value.addTo(map.value);

        technicsLayers.value.push(marker);

        const lastUpdate = new Date(technic.position.datetime);

        const formattedDate =
            String(lastUpdate.getDate()).padStart(2, "0") +
            "." +
            String(lastUpdate.getMonth() + 1).padStart(2, "0") +
            "." +
            lastUpdate.getFullYear() +
            " " +
            String(lastUpdate.getHours()).padStart(2, "0") +
            ":" +
            String(lastUpdate.getMinutes()).padStart(2, "0");

        let info = `
        <b>${technic.model.type.name}</b> ${technic.model.name}
        <br> <b>Госномер:</b> ${technic.license_plate}
        <br> <b>Скорость:</b> ${technic.position.speed} км/ч
        <br> <b>Последнее обновление:</b> ${formattedDate}
        `;

        const workerUnit = technic.workerUnit;

        if (workerUnit) {
            info = `${info}
        <br> <b>Механизатор:</b> ${workerUnit.worker.name}
            `;

            const equipments = workerUnit.equipments;
            if (equipments) {
                info = `${info}
                <br> <b>Оборудование:</b>
                <br>
                `;

                for (const eq in equipments) {
                    info = `${info}
                 - ${equipments[eq].model.name}`;
                }
            }
        }

        marker.bindPopup(info).openTooltip();
    }
};

onMounted(async () => {
    await fetchLists();

    mapContainer.value.style.width = props.width;
    mapContainer.value.style.height = props.height;

    initMap();

    if (props.isShowLegend) {
        mapLegend.value = L.control({ position: "topleft" });

        mapLegend.value.onAdd = function (map) {
            let legendContainer = L.DomUtil.create("div", "info legend"),
                headerLegend = L.DomUtil.create(
                    "div",
                    "flex px-1",
                    legendContainer
                ),
                legendBody = L.DomUtil.create("div", "px-1", legendContainer),
                toggleBtn = L.DomUtil.create("button", ""),
                content = L.DomUtil.create(
                    "div",
                    "legend-content py-2",
                    legendContainer
                ),
                switchBox = L.DomUtil.create(
                    "div",
                    "mt-1 flex rounded-md bg-green-50 px-1 border-2",
                    legendContainer
                ),
                plantBtn = L.DomUtil.create(
                    "button",
                    "border-r py-1 pr-1 legend-active",
                    switchBox
                ),
                stateBtn = L.DomUtil.create(
                    "button",
                    "border-l py-1 pl-1",
                    switchBox
                );

            toggleBtn.innerHTML = "<i class='pi pi-chevron-left'></i>";
            toggleBtn.onclick = () => {
                let display = legendBody.style.display;
                legendBody.style.display =
                    display === "none" ? "block" : "none";
                toggleBtn.innerHTML =
                    display === "none"
                        ? "<i class='pi pi-chevron-left'></i>"
                        : "<i class='pi pi-chevron-right'></i>";
                legendContainer.classList.toggle("legend-hide");
            };

            plantBtn.innerHTML = "Культуры";
            plantBtn.onclick = () => {
                updateLegend("plant");
                plantBtn.classList.toggle("legend-active");
                stateBtn.classList.toggle("legend-active");
            };

            stateBtn.innerHTML = "Состояния";
            stateBtn.onclick = () => {
                updateLegend("state");
                plantBtn.classList.toggle("legend-active");
                stateBtn.classList.toggle("legend-active");
            };

            switchBox.appendChild(plantBtn);
            switchBox.appendChild(stateBtn);

            legendBody.appendChild(switchBox);
            legendBody.appendChild(content);

            headerLegend.appendChild(toggleBtn);

            legendContainer.appendChild(headerLegend);
            legendContainer.appendChild(legendBody);

            return legendContainer;
        };

        mapLegend.value.addTo(map.value);
    }

    updateLegend(colorScheme.value);
    renderTechnics();

    if (fieldsLayers?.value?.[0]) {
        map.value.fitBounds(fieldsLayers.value[0].getBounds());
    }
});

watch(
    () => props.fields,
    async (newValue, oldValue) => {
        if (!oldValue.length) {
            return;
        }
        renderFields();

        if (newValue[0] && Object.keys(newValue[0]).length) {
            map.value.fitBounds(fieldsLayers.value[0].getBounds());
        }

        if (props.getFieldSquare && fieldsLayers?.value?.[0]) {
            const area = L.GeometryUtil.geodesicArea(
                fieldsLayers.value[0].getLayers()[0].getLatLngs()[0]
            );
            emit("changeSquare", area);
        } else {
            emit("changeSquare", 0);
        }
    },
    { deep: true }
);

watch(
    () => props.technics,
    (newValue, oldValue) => {
        if (Object.keys(oldValue).length === 0) {
            return;
        }
        renderTechnics();
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

.legend {
    padding: 6px 8px;
    font: 14px/16px Arial, Helvetica, sans-serif;
    background: white;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}

.legend-hide {
    background: transparent;
    box-shadow: none;
}

.legend button {
    width: 100%;
    margin: 4px 0;
}

.legend .legend-content {
    display: flex;
    flex-direction: column;
    width: 170px;
    max-height: 50%;
    overflow-x: hidden;
    overflow-y: auto;
    gap: 4px;
}

.legend i {
    width: 18px;
    height: 18px;
    float: left;
    margin-right: 8px;
    opacity: 0.7;
}

.legend-active {
    color: #84cc16;
}

.technic-icon {
    border-radius: 50%;
    border: 2px solid black;
}
</style>
