<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeable && close()">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" />

                <!-- Panel -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div
                        v-if="modelValue"
                        :class="['relative bg-surface rounded-2xl shadow-2xl w-full flex flex-col', maxWidthClass]"
                        role="dialog"
                        :aria-label="title"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-base">
                            <div>
                                <h2 class="text-base font-semibold font-display text-default">{{ title }}</h2>
                                <p v-if="description" class="text-sm text-muted mt-0.5">{{ description }}</p>
                            </div>
                            <button
                                v-if="closeable"
                                @click="close"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-muted hover:text-default hover:bg-surface-3 transition-colors"
                                aria-label="Close"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5 overflow-y-auto max-h-[60vh]">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-6 pb-6 pt-4 border-t border-base flex items-center justify-end gap-3">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue:  { type: Boolean, required: true },
    title:       { type: String, default: 'Confirm' },
    description: { type: String, default: '' },
    maxWidth:    { type: String, default: 'md' },
    closeable:   { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'close']);

const maxWidthClass = computed(() => ({
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
}[props.maxWidth] ?? 'max-w-md'));

function close() {
    emit('update:modelValue', false);
    emit('close');
}
</script>
