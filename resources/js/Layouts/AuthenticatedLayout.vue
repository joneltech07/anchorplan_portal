<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <div class="px-6 py-6" v-if="$slots.header">
            <h2 class="text-xl font-semibold leading-tight text-neutral-800 dark:text-neutral-200">
                <slot name="header" />
            </h2>
        </div>
        <slot />
    </AppLayout>
</template>
