<script setup>
import InstructorLayout from '@/Layouts/InstructorLayout.vue'

const props = defineProps({
    assignment:  Object,
    submissions: Array,
})

const pending  = props.submissions.filter(s => !s.graded_at)
const graded   = props.submissions.filter(s =>  s.graded_at)
</script>

<template>
    <InstructorLayout :title="assignment.title">
        <div class="space-y-6">
            <a :href="route('instructor.assignments.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Assignments
            </a>

            <!-- Header -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-default dark:text-default">{{ assignment.title }}</h1>
                        <p class="text-sm text-muted dark:text-muted">{{ assignment.course }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-subtle">Due</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ assignment.due_date ? new Date(assignment.due_date).toLocaleString() : '—' }}</p>
                    </div>
                </div>
                <p v-if="assignment.description" class="mt-4 text-sm text-muted dark:text-muted leading-relaxed whitespace-pre-line">{{ assignment.description }}</p>
                <div class="mt-4 flex flex-wrap gap-6 border-t border-surface-100 dark:border-base pt-4">
                    <div><p class="text-xs text-subtle">Total Submissions</p><p class="text-lg font-bold text-default dark:text-default">{{ submissions.length }}</p></div>
                    <div><p class="text-xs text-subtle">Pending</p><p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ pending.length }}</p></div>
                    <div><p class="text-xs text-subtle">Graded</p><p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ graded.length }}</p></div>
                    <div><p class="text-xs text-subtle">Max Marks</p><p class="text-lg font-bold text-default dark:text-default">{{ assignment.max_marks }}</p></div>
                </div>
            </div>

            <!-- Submissions table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <div class="px-5 py-3 border-b border-base dark:border-base bg-surface-2 dark:bg-surface flex items-center justify-between">
                    <span class="text-sm font-semibold text-muted dark:text-default">Submissions</span>
                    <span class="text-xs text-subtle">{{ submissions.length }} total</span>
                </div>
                <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Student</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Submitted</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">File</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Grade</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!submissions.length">
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-subtle">No submissions yet.</td>
                        </tr>
                        <tr v-for="s in submissions" :key="s.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40">
                            <td class="px-5 py-3 text-sm font-medium text-default dark:text-default">{{ s.student_name }}</td>
                            <td class="px-5 py-3 text-sm">
                                <span :class="s.is_late ? 'text-red-600 dark:text-red-400' : 'text-muted dark:text-muted'">
                                    {{ s.submitted_at ? new Date(s.submitted_at).toLocaleString() : '—' }}
                                </span>
                                <span v-if="s.is_late" class="ml-1 rounded-full bg-red-100 dark:bg-red-900/40 px-1.5 py-0.5 text-xs text-red-600 dark:text-red-400">Late</span>
                            </td>
                            <td class="px-5 py-3">
                                <a v-if="s.file_path" :href="s.file_path" target="_blank"
                                    class="inline-flex items-center gap-1 text-xs text-primary-600 hover:underline">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download
                                </a>
                                <span v-else class="text-xs text-subtle">—</span>
                            </td>
                            <td class="px-5 py-3">
                                <span v-if="s.graded_at" class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                                    {{ s.grade }} / {{ assignment.max_marks }}
                                </span>
                                <span v-else class="text-xs text-subtle">Not graded</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a :href="route('instructor.submissions.grade', { assignment: assignment.id, submission: s.id })"
                                    class="inline-flex items-center gap-1 rounded-lg border border-base dark:border-base px-3 py-1.5 text-xs font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                                    {{ s.graded_at ? 'Edit grade' : 'Grade' }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </InstructorLayout>
</template>
