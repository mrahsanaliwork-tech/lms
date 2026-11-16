<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="visible" :class="['flex items-start gap-3 mx-6 mt-4 p-4 rounded-xl border text-sm font-medium', styles.wrap]">
            <svg class="w-5 h-5 mt-0.5 shrink-0" :class="styles.icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" :d="styles.path" />
            </svg>
            <span :class="styles.text">{{ message }}</span>
            <button @click="visible = false" :class="['ml-auto shrink-0', styles.text, 'opacity-60 hover:opacity-100']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page    = usePage();
const visible = ref(false);
const type    = ref('success');
const message = ref('');

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) { type.value = 'success'; message.value = flash.success; visible.value = true; }
        else if (flash?.error) { type.value = 'error'; message.value = flash.error; visible.value = true; }
        if (visible.value) setTimeout(() => visible.value = false, 4000);
    },
    { deep: true }
);

const styles = computed(() => ({
    success: {
        wrap: 'bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800',
        icon: 'text-emerald-500',
        text: 'text-emerald-800 dark:text-emerald-300',
        path: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    error: {
        wrap: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
        icon: 'text-red-500',
        text: 'text-red-800 dark:text-red-300',
        path: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    },
}[type.value]));
</script>
