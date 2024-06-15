<script setup>
import { onMounted, ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";
import toastService from "@/Services/toastService";
import Map from "@/Components/Map.vue";
import CropRotation from "@/Pages/Field/Partials/CropRotation.vue";
import Accordion from "primevue/accordion";
import AccordionTab from "primevue/accordiontab";
import InputNumber from "primevue/inputnumber";
import InlineMessage from "primevue/inlinemessage";
import Textarea from "primevue/textarea";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Tag from "primevue/tag";

const confirm = useConfirm();

const props = defineProps({
    posts: Array,
    id: String,
});

const personal = ref(null);
const changedPersonal = ref({});
const isLoaded = ref(false);

const originalEmail = ref();
const newPassword = ref({});

const isNameEdit = ref(false);
const isEmailEdit = ref(false);
const isPostEdit = ref(false);

const isPersonalEdited = ref(false);

const selectedPost = ref(null);

function pad(num) {
    return (num < 10 ? "0" : "") + num;
}

function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();

    const formattedDate = pad(day) + "." + pad(month) + "." + year;

    return formattedDate;
}

const isEditAvailable = computed(() => {
    return (
        usePage().props.auth.user.post == "owner" ||
        personal.value.post == "worker"
    );
});

const changePostHandler = () => {
    personal.value.post = selectedPost.value.id;
    personal.value.post_title = selectedPost.value.name;
};

const saveChangeHandler = () => {
    axios
        .patch(route("updatePersonal"), changedPersonal.value)
        .then(() => {
            toastService.showSuccessToast(
                "Успешное обновление",
                "Сведения о сотруднике успешно обновлены в системе"
            );
            isEmailEdit.value = false;
            isNameEdit.value = false;
            isPostEdit.value = false;
            isPersonalEdited.value = false;
        })
        .catch((e) => {
            if (e?.response?.data?.error) {
                toastService.showErrorToast("Ошибка", e.response.data.error);
            } else if (e?.response?.data?.message) {
                console.log(e);
                toastService.showErrorToast("Ошибка", e.response.data.message);
            } else {
                toastService.showErrorToast(
                    "Ошибка",
                    "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                );
            }
        });
};

const cancelChangeHandler = () => {
    isEmailEdit.value = false;
    isNameEdit.value = false;
    isPostEdit.value = false;
    isPersonalEdited.value = false;
    fetchPersonal();
};

const newPasswordHandler = () => {
    if (
        newPassword.value?.password != newPassword.value?.password_confirmation
    ) {
        toastService.showErrorToast("Ошибка", "Пароли не совпадают");
        return;
    }

    newPassword.value.id = personal.value.id;

    axios
        .put(route("updatePassword"), newPassword.value)
        .then(() => {
            toastService.showSuccessToast(
                "Успешное обновление",
                "Пароль учетной записи сотрудника успешно обновлен в системе"
            );
            newPassword.value.password = "";
            newPassword.value.password_confirmation = "";
        })
        .catch((e) => {
            if (e?.response?.data?.error) {
                toastService.showErrorToast("Ошибка", e.response.data.error);
            } else if (e?.response?.data?.message) {
                console.log(e);
                toastService.showErrorToast("Ошибка", e.response.data.message);
            } else {
                toastService.showErrorToast(
                    "Ошибка",
                    "Что-то пошло не так. Проверьте данные и повторите попытку позже"
                );
            }
        });
};

const fetchPersonal = () => {
    axios
        .get(`/api/users/${props.id}`)
        .then((response) => {
            personal.value = response.data.data;
            originalEmail.value = personal.value.email;
            isLoaded.value = true;
        })
        .catch((error) => {});
};

onMounted(() => {
    fetchPersonal();
});

watch(
    () => personal.value,
    (nVal, oVal) => {
        if (!oVal) {
            return;
        }
        changedPersonal.value.id = personal.value.id;
        changedPersonal.value.name = personal.value.name;
        changedPersonal.value.post = personal.value.post;

        if (originalEmail.value != personal.value.email) {
            changedPersonal.value.email = personal.value.email;
        }

        isPersonalEdited.value = true;
    },
    { deep: true }
);

watch(
    () => isPostEdit.value,
    () => {
        isEmailEdit.value = false;
        isNameEdit.value = false;
    }
);

watch(
    () => isEmailEdit.value,
    () => {
        isPostEdit.value = false;
        isNameEdit.value = false;
    }
);

watch(
    () => isNameEdit.value,
    () => {
        isEmailEdit.value = false;
        isPostEdit.value = false;
    }
);
</script>

<template>
    <Head
        title="Информация о сотруднике
"
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="font-semibold text-2xl text-800 leading-tight">
                    Информация о сотруднике
                </h2>
                <Link
                    :href="route('personal.index')"
                    class="flex items-center gap-1 mt-2"
                >
                    <i class="pi pi-chevron-left"></i>
                    <span class="font-semibold text-md">К списку</span>
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div
                v-if="isLoaded"
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex flex-col gap-2"
            >
                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <div class="flex gap-2 items-center">
                        <InputText
                            v-if="isNameEdit"
                            type="text"
                            v-model="personal.name"
                            pt:root:class="border-0 shadow-none text-2xl font-semibold p-0"
                        />

                        <h2 v-else class="font-semibold text-2xl">
                            {{ personal.name }}
                        </h2>

                        <i
                            class="cursor-pointer pi pi-pen-to-square"
                            :class="
                                isNameEdit ? 'pi-check' : 'pi-pen-to-square'
                            "
                            @click="isNameEdit = !isNameEdit"
                        ></i>
                    </div>

                    <div class="flex gap-2 items-center">
                        <span class="text-lg font-semibold"> Email: </span>

                        <InputText
                            v-if="isEmailEdit"
                            type="text"
                            v-model="personal.email"
                            pt:root:class="border-0 shadow-none p-0"
                        />
                        <span v-else>
                            {{ personal.email }}
                        </span>

                        <i
                            class="cursor-pointer pi"
                            :class="
                                isEmailEdit ? 'pi-check' : 'pi-pen-to-square'
                            "
                            @click="isEmailEdit = !isEmailEdit"
                        ></i>
                    </div>

                    <div class="flex gap-2 items-center">
                        <span class="text-lg font-semibold"> Должность: </span>

                        <Dropdown
                            v-if="isPostEdit"
                            id="post"
                            v-model="selectedPost"
                            :options="posts"
                            optionLabel="name"
                            placeholder="Выберите должность"
                            pt:root:class="border-0 shadow-none"
                            pt:input:class="p-0"
                            required
                            @change="changePostHandler"
                        />
                        <span v-else>
                            {{ personal.post_title }}
                        </span>

                        <i
                            class="cursor-pointer pi"
                            :class="
                                isPostEdit ? 'pi-check' : 'pi-pen-to-square'
                            "
                            @click="isPostEdit = !isPostEdit"
                        ></i>
                    </div>

                    <div class="flex gap-2 items-end h-full mt-4">
                        <Button
                            v-if="isEditAvailable"
                            severity="danger"
                            label="Удалить"
                        />

                        <Button
                            v-if="isPersonalEdited"
                            @click="saveChangeHandler"
                            label="Сохранить изменения"
                        />

                        <Button
                            v-if="isPersonalEdited"
                            @click="cancelChangeHandler"
                            severity="secondary"
                            label="Отменить изменения"
                        />
                    </div>
                </div>

                <div
                    v-if="isEditAvailable"
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    <Accordion>
                        <AccordionTab header="Сбросить пароль">
                            <div>
                                <label class="font-semibold" for="password"
                                    >Новый пароль</label
                                >
                                <InputText
                                    id="password"
                                    ref="passwordInput"
                                    class="mt-1 block w-full"
                                    v-model="newPassword.password"
                                    type="password"
                                    aria-describedby="password-help"
                                />
                            </div>

                            <div>
                                <label
                                    class="font-semibold"
                                    for="password_confirmation"
                                    >Повтор пароля</label
                                >
                                <InputText
                                    id="password_confirmation"
                                    class="mt-1 block w-full"
                                    v-model="newPassword.password_confirmation"
                                    type="password"
                                    aria-describedby="password_confirmation-help"
                                />
                            </div>

                            <Button
                                @click="newPasswordHandler"
                                class="mt-4"
                                type="submit"
                                label="Сохранить"
                            />
                        </AccordionTab>
                    </Accordion>
                </div>

                <div
                    class="rounded-lg bg-white shadow-md flex flex-col gap-2 p-6"
                >
                    {{ personal }}
                </div>
            </div>
        </div>
        <ConfirmDialog></ConfirmDialog>
    </AuthenticatedLayout>
</template>

<style>
.p-accordion .p-accordion-header .p-accordion-header-link {
    gap: 1rem;
    justify-content: start;
}
</style>
