<script setup>
import { Link } from '@inertiajs/vue3'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'

const props = defineProps({
    quizzes: { type: Array, default: () => [] },
})

const statusColors = {
    published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    draft:     'bg-surface-3 text-muted dark:bg-surface-2 dark:text-muted',
}
</script>

<template>
    <InstructorLayout title="My Quizzes">
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default">My Quizzes</h1>
                    <p class="text-sm text-muted mt-0.5">Quizzes attached to your courses</p>
                </div>
            </div>

            <div v-if="!quizzes.length" class="rounded-xl border border-base bg-surface p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-muted mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>
                <p class="text-sm font-medium text-default">No quizzes yet</p>
                <p class="text-xs text-muted mt-1">Quizzes are created per course. Go to a course to add a quiz.</p>
                <Link :href="route('instructor.courses.index')" class="mt-4 inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    Go to My Courses →
                </Link>
            </div>

            <div v-else class="bg-surface rounded-xl border border-base overflow-hidden">
                <table class="min-w-full divide-y divide-surface-200">
                    <thead class="bg-surface-2">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Quiz</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Questions</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-for="q in quizzes" :key="q.id" class="hover:bg-surface-2">
                            <td class="px-5 py-3 font-medium text-default">{{ q.title }}</td>
                            <td class="px-5 py-3 text-sm text-muted">{{ q.course }}</td>
                            <td class="px-5 py-3 text-sm text-muted">{{ q.questions_count }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-medium rounded-full px-2.5 py-0.5 capitalize', statusColors[q.status] ?? statusColors.draft]">{{ q.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </InstructorLayout>
</template>
