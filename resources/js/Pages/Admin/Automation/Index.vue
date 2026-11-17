<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    workflows:  Array,
    recentLogs: Array,
})

const TRIGGER_LABELS = {
    'student.registered':  'Student Registered',
    'student.enrolled':    'Student Enrolled',
    'payment.completed':   'Payment Completed',
    'course.completed':    'Course Completed',
    'quiz.completed':      'Quiz Completed',
    'assignment.submitted':'Assignment Submitted',
}

function toggle(id) {
    router.post(route('admin.automation.toggle', id), {}, { preserveScroll: true })
}

function destroy(id) {
    if (!confirm('Delete this workflow?')) return
    router.delete(route('admin.automation.destroy', id), { preserveScroll: true })
}

function formatTime(iso) {
    if (!iso) return 'Never'
    const d    = new Date(iso)
    const now  = new Date()
    const diff = Math.floor((now - d) / 1000)
    if (diff < 60)    return 'just now'
    if (diff < 3600)  return Math.floor(diff / 60) + 'm ago'
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
    return d.toLocaleDateString()
}

const statusClass = {
    success: 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
    failed:  'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
    skipped: 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
}
</script>

<template>
    <AdminLayout title="Automation">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Automation Engine</h1>
                    <p class="text-sm text-muted dark:text-muted">Trigger-based workflows that run automatically</p>
                </div>
                <a :href="route('admin.automation.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Workflow
                </a>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="stat in [
                    { label: 'Total Workflows', value: workflows.length, color: 'text-default dark:text-default' },
                    { label: 'Active',           value: workflows.filter(w => w.is_active).length, color: 'text-emerald-600 dark:text-emerald-400' },
                    { label: 'Inactive',         value: workflows.filter(w => !w.is_active).length, color: 'text-muted dark:text-muted' },
                    { label: 'Executions Today', value: recentLogs.filter(l => new Date(l.created_at) > new Date(Date.now()-86400000)).length, color: 'text-primary-600 dark:text-primary-400' },
                ]" :key="stat.label"
                    class="rounded-xl border border-base dark:border-base bg-surface p-4 text-center">
                    <p :class="['text-2xl font-bold tabular-nums', stat.color]">{{ stat.value }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ stat.label }}</p>
                </div>
            </div>

            <!-- Workflows list -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <div class="px-5 py-4 border-b border-base dark:border-base">
                    <h2 class="font-semibold text-default dark:text-default">Workflows</h2>
                </div>
                <div class="divide-y divide-surface-100 dark:divide-surface-700">
                    <div v-if="!workflows.length" class="px-5 py-10 text-center text-sm text-subtle">
                        No workflows yet. Create one to get started.
                    </div>
                    <div v-for="w in workflows" :key="w.id"
                        class="flex items-center gap-4 px-5 py-4 hover:bg-surface-2 dark:hover:bg-surface-700/40">

                        <!-- Toggle -->
                        <button @click="toggle(w.id)"
                            :class="['relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none', w.is_active ? 'bg-primary-600' : 'bg-surface-300 dark:bg-surface-2']">
                            <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', w.is_active ? 'translate-x-6' : 'translate-x-1']" />
                        </button>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-default dark:text-default">{{ w.name }}</p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs text-muted dark:text-muted">
                                    {{ TRIGGER_LABELS[w.trigger_type] ?? w.trigger_type }}
                                </span>
                                <span class="text-subtle dark:text-muted">·</span>
                                <span class="text-xs text-subtle">{{ w.actions }} action{{ w.actions !== 1 ? 's' : '' }}</span>
                                <span class="text-subtle dark:text-muted">·</span>
                                <span class="text-xs text-subtle">{{ w.logs_count }} runs</span>
                                <span class="text-subtle dark:text-muted">·</span>
                                <span class="text-xs text-subtle">Last: {{ formatTime(w.last_run_at) }}</span>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <span :class="['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium', w.is_active ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-surface-3 dark:bg-surface-2 text-muted']">
                            {{ w.is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <!-- Actions -->
                        <div class="shrink-0 flex items-center gap-1">
                            <a :href="route('admin.automation.edit', w.id)"
                                class="rounded p-1.5 text-subtle hover:text-amber-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button @click="destroy(w.id)" class="rounded p-1.5 text-subtle hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent execution log -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <div class="px-5 py-4 border-b border-base dark:border-base">
                    <h2 class="font-semibold text-default dark:text-default">Recent Executions</h2>
                </div>
                <div class="divide-y divide-surface-100 dark:divide-surface-700 max-h-96 overflow-y-auto">
                    <div v-if="!recentLogs.length" class="px-5 py-8 text-center text-sm text-subtle">No executions yet.</div>
                    <div v-for="log in recentLogs" :key="log.id"
                        class="flex items-center gap-4 px-5 py-3">
                        <span :class="['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusClass[log.status] ?? statusClass.skipped]">
                            {{ log.status }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-default dark:text-default truncate">{{ log.workflow_name }}</p>
                            <p class="text-xs text-subtle">{{ TRIGGER_LABELS[log.trigger] ?? log.trigger }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-subtle">{{ formatTime(log.created_at) }}</p>
                            <p v-if="log.error" class="text-xs text-red-500 max-w-48 truncate">{{ log.error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
