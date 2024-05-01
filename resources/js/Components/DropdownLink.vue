<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <Link
        class="block w-full px-4 py-2 text-left text-sm leading-5 text-700 hover:bg-100 focus:outline-none focus:bg-100 transition duration-150 ease-in-out"
    >
        <slot />
    </Link>
</template>
