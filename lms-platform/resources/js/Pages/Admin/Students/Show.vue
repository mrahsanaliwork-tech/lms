<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    student: Object,
    stats: Object,
    enrollments: Array,
    certificates: Array,
})

const activeTab = ref('enrollments')

const tabs = [
    { key: 'enrollments', label: 'Enrollments' },
    { key: 'certificates', label: 'Certificates' },
]

const statusVariant = {
    active: 'success',
    inactive: 'default',
    graduated: 'info',
    suspended: 'danger',
}

const enrollmentVariant = {
    active: 'info',
    completed: 'success',
    dropped: 'danger',
    pending: 'warning',
}

function destroy() {
    if (!confirm(`Remove ${props.student.name}? This can be restored later.`)) return
    router.delete(route('admin.students.destroy', props.student.id), {
        onSuccess: () => router.visit(route('admin.students.index'))
    })
}
</script>

<template>
    <AdminLayout :title="student.name">
        <div class="space-y-6">
            <!-- Back link -->
            <a :href="route('admin.students.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted dark:hover:text-primary-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Students
            </a>

            <!-- Profile Card -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-2xl font-bold text-primary-700 dark:text-primary-300">
                            {{ student.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-default dark:text-default">{{ student.name }}</h1>
                            <p class="text-sm text-muted dark:text-muted">{{ student.email }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs text-muted dark:text-muted">{{ student.student_id }}</span>
                                <Badge :variant="statusVariant[student.status] ?? 'default'" dot size="sm">{{ student.status }}</Badge>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="route('admin.students.edit', student.id)"
                            class="inline-flex items-center gap-2 rounded-lg border border-base dark:border-base px-3 py-2 text-sm font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <button @click="destroy"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 dark:border-red-800 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Details grid -->
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 border-t border-surface-100 dark:border-base pt-6">
                    <div v-for="item in [
                        { label: 'Phone', value: student.phone ?? '—' },
                        { label: 'Organization', value: student.organization ?? '—' },
                        { label: 'Batch', value: student.batch ?? '—' },
                        { label: 'Group', value: student.group ?? '—' },
                        { label: 'Enrolled', value: student.enrolled_at ?? '—' },
                        { label: 'Last Login', value: student.last_login ?? 'Never' },
                    ]" :key="item.label">
                        <p class="text-xs text-subtle dark:text-subtle">{{ item.label }}</p>
                        <p class="mt-0.5 text-sm font-medium text-muted dark:text-default">{{ item.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div v-for="item in [
                    { label: 'Enrollments', value: stats.enrollments },
                    { label: 'Completed', value: stats.completed },
                    { label: 'Certificates', value: stats.certificates },
                    { label: 'Avg Progress', value: Math.round(stats.avg_progress) + '%' },
                ]" :key="item.label"
                    class="rounded-xl border border-base dark:border-base bg-surface p-4 text-center">
                    <p class="text-2xl font-bold text-default dark:text-default">{{ item.value }}</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted">{{ item.label }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="rounded-xl border border-base dark:border-base bg-surface">
                <div class="flex border-b border-base dark:border-base">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                        :class="['px-5 py-3 text-sm font-medium transition-colors', activeTab === tab.key ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400' : 'text-muted hover:text-muted dark:hover:text-subtle']">
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Enrollments -->
                <div v-if="activeTab === 'enrollments'" class="divide-y divide-surface-100 dark:divide-surface-700">
                    <div v-if="!enrollments.length" class="px-6 py-8 text-center text-sm text-subtle">No enrollments yet.</div>
                    <div v-for="e in enrollments" :key="e.id" class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-medium text-default dark:text-default">{{ e.course }}</p>
                            <p class="text-xs text-muted dark:text-muted">Enrolled {{ e.enrolled }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-24 rounded-full bg-surface-3 dark:bg-surface-2">
                                    <div class="h-1.5 rounded-full bg-primary-500" :style="`width:${e.progress}%`"></div>
                                </div>
                                <span class="text-xs text-muted">{{ e.progress }}%</span>
                            </div>
                            <Badge :variant="enrollmentVariant[e.status] ?? 'default'" size="sm">{{ e.status }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Certificates -->
                <div v-if="activeTab === 'certificates'" class="divide-y divide-surface-100 dark:divide-surface-700">
                    <div v-if="!certificates.length" class="px-6 py-8 text-center text-sm text-subtle">No certificates yet.</div>
                    <div v-for="c in certificates" :key="c.id" class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-medium text-default dark:text-default">{{ c.course }}</p>
                            <p class="text-xs font-mono text-muted dark:text-muted">{{ c.number }}</p>
                        </div>
                        <span class="text-sm text-muted dark:text-muted">{{ c.issued_at }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
