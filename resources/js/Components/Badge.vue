<template>
    <span :class="classes">
        <span v-if="dot" class="inline-block w-1.5 h-1.5 rounded-full mr-1.5" :class="dotColor" />
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        // default | success | warning | danger | info | purple
    },
    size: { type: String, default: 'sm' },
    dot:  { type: Boolean, default: false },
});

const map = {
    default: { badge: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',      dot: 'bg-slate-400' },
    success: { badge: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', dot: 'bg-emerald-500' },
    warning: { badge: 'bg-amber-50  text-amber-700  dark:bg-amber-900/30  dark:text-amber-400',  dot: 'bg-amber-500' },
    danger:  { badge: 'bg-red-50    text-red-700    dark:bg-red-900/30    dark:text-red-400',    dot: 'bg-red-500' },
    info:    { badge: 'bg-sky-50    text-sky-700    dark:bg-sky-900/30    dark:text-sky-400',    dot: 'bg-sky-500' },
    purple:  { badge: 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400', dot: 'bg-violet-500' },
    primary: { badge: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400', dot: 'bg-indigo-500' },
};

const sizeClass = computed(() =>
    props.size === 'xs' ? 'px-1.5 py-0.5 text-[10px]' :
    props.size === 'md' ? 'px-3 py-1 text-sm' :
    'px-2 py-0.5 text-xs'
);

const classes = computed(() => [
    'inline-flex items-center font-medium rounded-full tracking-wide',
    sizeClass.value,
    (map[props.variant] ?? map.default).badge,
]);

const dotColor = computed(() => (map[props.variant] ?? map.default).dot);
</script>
