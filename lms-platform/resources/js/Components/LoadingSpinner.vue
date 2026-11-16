<template>
    <div :class="wrapClass" role="status" aria-label="Loading">
        <svg
            :class="spinClass"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span v-if="label" class="ml-2 text-sm text-muted">{{ label }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    size:    { type: String, default: 'md' },   // sm | md | lg
    label:   { type: String, default: '' },
    center:  { type: Boolean, default: false },
    color:   { type: String, default: 'text-primary-500' },
});

const sizes = { sm: 'h-4 w-4', md: 'h-6 w-6', lg: 'h-10 w-10' };

const wrapClass = computed(() => [
    'inline-flex items-center',
    props.center && 'justify-center w-full py-8',
]);
const spinClass = computed(() => ['animate-spin', sizes[props.size] ?? sizes.md, props.color]);
</script>
