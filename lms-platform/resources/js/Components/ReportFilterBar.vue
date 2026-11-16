<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    filters:    Object,
    routeName:  String,
    extraSlots: { type: Boolean, default: false },
})

const form = reactive({ ...props.filters })

function apply() {
    router.get(route(props.routeName), form, { preserveState: true, replace: true })
}
function reset() {
    Object.assign(form, { from: '', to: '', course_id: '', student_id: '' })
    router.get(route(props.routeName), {}, { replace: true })
}
function exportAs(type) {
    const params = new URLSearchParams({ ...form, export: type })
    window.location.href = route(props.routeName) + '?' + params.toString()
}
</script>

<template>
    <div class="flex flex-wrap items-end gap-3 rounded-2xl bg-surface border border-base dark:border-base p-4">
        <div>
            <label class="block text-xs font-medium text-muted dark:text-muted mb-1">From</label>
            <input type="date" v-model="form.from"
                class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>
        <div>
            <label class="block text-xs font-medium text-muted dark:text-muted mb-1">To</label>
            <input type="date" v-model="form.to"
                class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>
        <slot />
        <div class="flex gap-2 ml-auto">
            <button @click="reset"
                class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-muted hover:bg-surface-2 dark:hover:bg-surface-700 transition-colors">
                Reset
            </button>
            <button @click="apply"
                class="rounded-lg bg-primary-600 hover:bg-primary-700 px-4 py-2 text-sm font-medium text-white transition-colors">
                Apply
            </button>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button @click="exportAs('csv')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </button>
            <button @click="exportAs('pdf')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 px-3 py-2 text-xs font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </button>
        </div>
    </div>
</template>
