<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Добро пожаловать" />

    <div class="fullscreen-bg">
        <div class="overlay flex flex-col items-center justify-center">
            <ApplicationLogo class="block h-32 w-auto fill-green-50 text-800" />
            <h1 class="text-6xl text-green-50 font-semibold text-center">
                Добро пожаловать!
            </h1>

            <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="text-green-50 font-semibold border-2 border-green-50 rounded-lg py-4 px-8 text-2xl mt-12"
            >
                Главная
            </Link>

            <template v-else>
                <div class="flex gap-4 mt-12">
                    <Link
                        :href="route('login')"
                        class="text-green-50 font-semibold border-2 border-green-50 rounded-lg py-4 px-8 text-2xl"
                    >
                        Вход
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="text-green-50 font-semibold border-2 border-green-50 rounded-lg py-4 px-8 text-2xl"
                    >
                        Регистрация
                    </Link>
                </div>
            </template>
        </div>
        <video
            loop=""
            muted=""
            autoplay=""
            poster="/assets/images/welcome_background.jpg"
            class="fullscreen-bg__video"
        >
            <source src="/storage/video/video.mp4" type="video/mp4" />
        </video>
    </div>
</template>

<style>
.fullscreen-bg {
    overflow: hidden;
    position: relative;
    height: 100%;
    width: 100%;
    padding-top: 45%;
}

.fullscreen-bg__video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}
.overlay {
    background: rgba(200, 255, 180, 0.3);
    backdrop-filter: blur(4px);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 4;
}

@media (max-width: 767px) {
    .fullscreen-bg {
        background: url("/assets/images/welcome_background.jpg") center center /
            cover no-repeat;
    }
    .fullscreen-bg__video {
        display: none;
    }
}
</style>
