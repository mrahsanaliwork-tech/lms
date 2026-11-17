<script setup>
import { useForm } from '@inertiajs/vue3'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'

const props = defineProps({
    assignment: Object,
    submission: Object,
})

const form = useForm({
    grade:    props.submission.grade ?? '',
    feedback: props.submission.feedback ?? '',
})

function submit() {
    form.post(route('instructor.submissions.saveGrade', {
        assignment: props.assignment.id,
        submission: props.submission.id,
    }))
}
</script>

<template>
    <InstructorLayout :title="`Grade — ${assignment.title}`">
        <div class="mx-auto max-w-2xl space-y-6">
            <a :href="route('instructor.assignments.show', assignment.id)"
                class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to submissions
            </a>

            <!-- Submission info -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-bold text-default dark:text-default">{{ submission.student_name }}</h1>
                        <p class="text-sm text-muted dark:text-muted">{{ assignment.title }}</p>
                    </div>
                    <span v-if="submission.is_late"
                        class="rounded-full bg-red-100 dark:bg-red-900/40 px-3 py-1 text-xs font-semibold text-red-600 dark:text-red-400">
                        Late submission
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t border-surface-100 dark:border-base pt-4">
                    <div>
                        <p class="text-xs text-subtle">Submitted</p>
                        <p class="text-sm text-default dark:text-default">
                            {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-subtle">Max Marks</p>
                        <p class="text-sm font-semibold text-default dark:text-default">{{ assignment.max_marks }}</p>
                    </div>
                </div>

                <!-- File download -->
                <div class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2/40 p-4 flex items-center gap-3">
                    <div class="rounded-lg bg-primary-100 dark:bg-primary-900/30 p-2">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-default dark:text-default truncate">{{ submission.file_name ?? 'submission' }}</p>
                        <p class="text-xs text-subtle">Student submission file</p>
                    </div>
                    <a v-if="submission.file_url" :href="submission.file_url" target="_blank"
                        class="shrink-0 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700">
                        Download
                    </a>
                </div>

                <!-- Student notes -->
                <div v-if="submission.notes" class="rounded-lg border border-base dark:border-base p-4">
                    <p class="text-xs font-medium text-subtle mb-1 uppercase tracking-wide">Student notes</p>
                    <p class="text-sm text-muted dark:text-muted leading-relaxed whitespace-pre-line">{{ submission.notes }}</p>
                </div>
            </div>

            <!-- Grade form -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <h2 class="font-semibold text-default dark:text-default">Grade</h2>

                <!-- Score input -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">
                        Score <span class="text-subtle">/ {{ assignment.max_marks }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input v-model.number="form.grade" type="number" :min="0" :max="assignment.max_marks"
                            class="w-28 rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-lg font-semibold text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <span v-if="form.grade !== ''" class="text-sm text-muted dark:text-muted">
                            = {{ Math.round((form.grade / assignment.max_marks) * 100) }}%
                        </span>
                    </div>
                    <p v-if="form.errors.grade" class="mt-1 text-xs text-red-500">{{ form.errors.grade }}</p>
                </div>

                <!-- Feedback -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Written Feedback</label>
                    <textarea v-model="form.feedback" rows="5" placeholder="Optional feedback for the student…"
                        class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    <p class="mt-1 text-xs text-subtle">The student will receive an email with their grade and this feedback.</p>
                </div>

                <!-- Previous grade notice -->
                <div v-if="submission.graded_at" class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-xs text-amber-700 dark:text-amber-300">
                    Previously graded {{ new Date(submission.graded_at).toLocaleDateString() }} with score {{ submission.grade }}/{{ assignment.max_marks }}.
                    Saving will overwrite and re-notify the student.
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a :href="route('instructor.assignments.show', assignment.id)"
                        class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2">
                        Cancel
                    </a>
                    <button @click="submit" :disabled="form.processing || form.grade === ''"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-40">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ form.processing ? 'Saving…' : 'Save Grade & Notify Student' }}
                    </button>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>
