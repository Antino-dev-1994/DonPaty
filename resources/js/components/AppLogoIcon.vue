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
        viewBox="0 0 48 48"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            d="M11 27c0-7.2 5.8-13 13-13s13 5.8 13 13v8H11v-8Z"
        />
        <path
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="3"
            d="m18 18 3 6m4-9 3 7m4-4 2 6M9 35h30"
        />
    </svg>
</template>
