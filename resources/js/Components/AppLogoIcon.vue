<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="100"
        height="100"
        viewBox="0 0 100 100"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <linearGradient id="gdi" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#00a2ff" />
                <stop offset="100%" stop-color="#8a2be2" />
            </linearGradient>
        </defs>
        <path d="M50 5 L89 27.5 V72.5 L50 95 L11 72.5 V27.5 L50 5Z" stroke="url(#gdi)" stroke-width="3.5" fill="rgba(0,162,255,0.08)"/>
        <circle cx="50" cy="30" r="8" stroke="url(#gdi)" stroke-width="5" fill="none"/>
        <line x1="50" y1="38" x2="50" y2="72" stroke="url(#gdi)" stroke-width="5" stroke-linecap="round"/>
        <line x1="35" y1="50" x2="65" y2="50" stroke="url(#gdi)" stroke-width="4.5" stroke-linecap="round"/>
        <path d="M50 72 Q38 72 36 62" stroke="url(#gdi)" stroke-width="4.5" stroke-linecap="round" fill="none"/>
        <path d="M50 72 Q62 72 64 62" stroke="url(#gdi)" stroke-width="4.5" stroke-linecap="round" fill="none"/>
        <circle cx="50" cy="30" r="4.5" fill="#00a2ff"/>
        <circle cx="36" cy="63" r="4" fill="#8a2be2"/>
        <circle cx="64" cy="63" r="4" fill="#8a2be2"/>
        <line x1="36" y1="63" x2="50" y2="30" stroke="#00a2ff" stroke-width="1.5" stroke-dasharray="3 3" opacity="0.35"/>
        <line x1="64" y1="63" x2="50" y2="30" stroke="#8a2be2" stroke-width="1.5" stroke-dasharray="3 3" opacity="0.35"/>
    </svg>
</template>
