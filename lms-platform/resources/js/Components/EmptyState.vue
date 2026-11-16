<template>
    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
        <div class="w-16 h-16 rounded-2xl bg-surface-3 flex items-center justify-center mb-4">
            <svg v-if="!$slots.icon" class="w-8 h-8 text-subtle" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <slot name="icon" />
        </div>

        <h3 class="text-sm font-semibold text-default mb-1 font-display">{{ title }}</h3>
        <p class="text-sm text-muted max-w-xs">{{ description }}</p>

        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title:       { type: String, default: 'Nothing here yet' },
    description: { type: String, default: 'Get started by creating your first item.' },
});
</script>
