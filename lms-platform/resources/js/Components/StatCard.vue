<template>
    <div class="bg-surface rounded-xl border border-base p-4 flex flex-col gap-2 hover:shadow-sm transition-shadow">
        <!-- Icon -->
        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', iconBg]">
            <component :is="icon" v-if="icon" class="w-4 h-4" :class="iconColor" />
            <slot v-else name="icon" />
        </div>

        <!-- Number -->
        <p class="text-2xl font-bold font-display text-default tabular-nums leading-none mt-1">
            {{ formattedValue }}
        </p>

        <!-- Title -->
        <p class="text-[11px] font-medium text-muted uppercase leading-tight">{{ title }}</p>

        <!-- Trend -->
        <div v-if="trend !== undefined" class="flex items-center gap-1 flex-wrap">
            <svg v-if="trend >= 0" class="w-3 h-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
            </svg>
            <svg v-else class="w-3 h-3 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941" />
            </svg>
            <span :class="trend >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                  class="text-[11px] font-semibold">{{ Math.abs(trend) }}%</span>
            <span class="text-[11px] text-subtle">vs last month</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title:   { type: String, required: true },
    value:   { type: [Number, String], required: true },
    trend:   { type: Number, default: undefined },
    icon:    { type: Object, default: null },
    color:   { type: String, default: 'indigo' },
    prefix:  { type: String, default: '' },
    suffix:  { type: String, default: '' },
});

const colorMap = {
    indigo:  { bg: 'bg-indigo-50 dark:bg-indigo-900/20',  icon: 'text-indigo-600 dark:text-indigo-400' },
    emerald: { bg: 'bg-emerald-50 dark:bg-emerald-900/20', icon: 'text-emerald-600 dark:text-emerald-400' },
    amber:   { bg: 'bg-amber-50 dark:bg-amber-900/20',    icon: 'text-amber-600 dark:text-amber-400' },
    sky:     { bg: 'bg-sky-50 dark:bg-sky-900/20',        icon: 'text-sky-600 dark:text-sky-400' },
    violet:  { bg: 'bg-violet-50 dark:bg-violet-900/20',  icon: 'text-violet-600 dark:text-violet-400' },
    rose:    { bg: 'bg-rose-50 dark:bg-rose-900/20',      icon: 'text-rose-600 dark:text-rose-400' },
};

const iconBg    = computed(() => (colorMap[props.color] ?? colorMap.indigo).bg);
const iconColor = computed(() => (colorMap[props.color] ?? colorMap.indigo).icon);

const formattedValue = computed(() => {
    const v = typeof props.value === 'number'
        ? props.value.toLocaleString()
        : props.value;
    return `${props.prefix}${v}${props.suffix}`;
});
</script>
