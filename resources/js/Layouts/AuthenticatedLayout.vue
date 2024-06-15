<script setup>
import { ref, computed, watch, onMounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link, usePage } from "@inertiajs/vue3";
import Toast from "primevue/toast";
import DynamicDialog from "primevue/dynamicdialog";
import Menubar from "primevue/menubar";
import Menu from "primevue/menu";
import Button from "primevue/button";

const profileMenu = ref();
// const isWorker = computed(() => );

const menuItems = ref();

const profileMenuItems = ref([]);

const toggleProfileMenu = (event) => {
    profileMenu.value.toggle(event);
};

onMounted(() => {
    const isWorker = usePage().props.auth.user.post == "worker";
    profileMenuItems.value = [
        {
            label: usePage().props.auth.user.email,
            items: [
                {
                    label: "Личный кабинет",
                    route: "profile.edit",
                    icon: "pi pi-user",
                },
            ],
        },
    ];

    if (!isWorker) {
        profileMenuItems.value[0].items.push({
            label: "Админ-панель",
            route: "adminboard",
            icon: "pi pi-list",
        });
    }

    profileMenuItems.value[0].items.push({
        label: "Выход",
        route: "logout",
        icon: "pi pi-sign-out",
        method: "post",
    });

    if (isWorker) {
        menuItems.value = menuItems.value = [
            {
                label: "Главная",
                route: "dashboard",
            },
            {
                label: "Отчеты",
                route: "report.index",
            },
        ];
    } else {
        menuItems.value = [
            {
                label: "Главная",
                route: "dashboard",
            },
            {
                label: "Мероприятия",
                route: "operation.index",
            },
            {
                label: "Сотрудники",
                route: "personal.index",
            },
            {
                label: "Участки",
                route: "field.index",
            },
        ];
    }
});
</script>

<template>
    <div class="bg-green-50">
        <Menubar
            class="px-4 sm:px-6 lg:px-8 bg-white"
            :model="menuItems"
            :pt="{
                start: 'mr-4',
                button: 'ml-2',
                menubuttonicon: 'w-8 h-8',
            }"
        >
            <template #start>
                <Link :href="route('welcome')">
                    <ApplicationLogo
                        class="block h-9 w-auto fill-lime-500 text-800"
                    />
                </Link>
            </template>
            <template #item="{ item, props }">
                <Link
                    v-bind="props.action"
                    class="flex align-items-center"
                    :href="route(item.route)"
                    :class="{
                        'border-b-2 border-lime-500': route().current(
                            item.route
                        ),
                    }"
                >
                    {{ item.label }}
                </Link>
            </template>
            <template #end>
                <Button
                    type="button"
                    icon="pi pi-chevron-down"
                    @click="toggleProfileMenu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                    :label="$page.props.auth.user.name"
                    iconPos="right"
                    plain
                    text
                />
                <Menu
                    ref="profileMenu"
                    id="overlay_menu"
                    :model="profileMenuItems"
                    :popup="true"
                >
                    <template #item="{ item }">
                        <Link
                            :method="item.method"
                            :href="route(item.route)"
                            class="flex gap-2 items-center p-1 w-full rounded-md"
                            :class="{
                                'bg-green-50': route().current(item.route),
                            }"
                            as="button"
                        >
                            <i :class="item.icon"></i>
                            <span>{{ item.label }}</span>
                        </Link>
                    </template>
                </Menu>
            </template>
        </Menubar>

        <div class="min-h-screen bg-100 bg-white sm:bg-inherit">
            <!-- Page Heading -->
            <header v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
                <Toast />
                <DynamicDialog />
            </main>
        </div>
    </div>
</template>

<style lang="stylus">
.p-menu-list
    display flex
    flex-direction column
</style>
