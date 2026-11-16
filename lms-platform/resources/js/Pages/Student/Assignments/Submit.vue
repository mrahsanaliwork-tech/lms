<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
    assignment: Object,
    submission: Object,
})

const form = useForm({
    file:  null,
    notes: '',
})

const fileRef   = ref(null)
const fileName  = ref(null)
const dragOver  = ref(false)

function onFile(e) {
    const f = e.target?.files?.[0] ?? e.dataTransfer?.files?.[0]
    if (!f) return
    form.file = f
    fileName.value = f.name
}

function submit() {
    form.post(route('student.assignments.submit', props.assignment.id), {
        forceFormData: true,
    })
}

const canSubmit = !props.submission || (props.assignment.allow_late && props.assignment.is_overdue === false)
</script>

<template>
    <StudentLayout title="Submit Assignment">
        <div class="mx-auto max-w-xl space-y-6">
            <a :href="route('student.assignments.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to assignments
            </a>

            <!-- Assignment info -->
            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface p-6 space-y-4">
                <div>
                    <p class="text-xs text-subtle mb-1">{{ assignment.course }}</p>
                    <h1 class="text-xl font-bold text-default dark:text-default">{{ assignment.title }}</h1>
                </div>
                <p v-if="assignment.description" class="text-sm text-muted dark:text-muted leading-relaxed whitespace-pre-line">{{ assignment.description }}</p>
                <div class="grid grid-cols-2 gap-4 border-t border-surface-100 dark:border-surface-800 pt-4">
                    <div>
                        <p class="text-xs text-subtle">Due</p>
                        <p :class="['text-sm font-medium', assignment.is_overdue ? 'text-red-600 dark:text-red-400' : 'text-default dark:text-default']">
                            {{ assignment.due_date ? new Date(assignment.due_date).toLocaleString() : '—' }}
                            <span v-if="assignment.is_overdue" class="ml-1 text-xs">(overdue)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-subtle">Max Marks</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ assignment.max_marks }}</p>
                    </div>
                </div>
            </div>

            <!-- Already submitted + graded -->
            <div v-if="submission?.graded_at" class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-surface p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-emerald-100 dark:bg-emerald-900/40 p-2">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-default dark:text-default">Assignment Graded</p>
                        <p class="text-xs text-subtle">Graded {{ new Date(submission.graded_at).toLocaleDateString() }}</p>
                    </div>
                    <div class="ml-auto text-right">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ submission.grade }}<span class="text-sm font-normal text-subtle">/{{ assignment.max_marks }}</span></p>
                        <p class="text-xs text-subtle">{{ Math.round((submission.grade / assignment.max_marks) * 100) }}%</p>
                    </div>
                </div>
                <div v-if="submission.feedback" class="rounded-xl border border-surface-100 dark:border-base bg-surface-2 dark:bg-surface/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-subtle mb-1">Instructor Feedback</p>
                    <p class="text-sm text-muted dark:text-muted leading-relaxed whitespace-pre-line">{{ submission.feedback }}</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted dark:text-muted">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <a v-if="submission.file_url" :href="submission.file_url" target="_blank" class="hover:text-primary-600 hover:underline">{{ submission.file_name }}</a>
                </div>
            </div>

            <!-- Submitted but not yet graded -->
            <div v-else-if="submission" class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-surface p-6 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-amber-100 dark:bg-amber-900/40 p-2">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-default dark:text-default">Submitted — Awaiting Grade</p>
                        <p class="text-xs text-subtle">
                            {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '' }}
                            <span v-if="submission.is_late" class="ml-1 text-red-500">(late)</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted dark:text-muted">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 4 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <a v-if="submission.file_url" :href="submission.file_url" target="_blank" class="hover:text-primary-600 hover:underline">{{ submission.file_name }}</a>
                </div>
                <p v-if="submission.notes" class="text-sm text-muted dark:text-muted italic">{{ submission.notes }}</p>
            </div>

            <!-- Submission form (not yet submitted, or late allowed) -->
            <div v-else class="rounded-2xl border border-base dark:border-surface-800 bg-surface p-6 space-y-5">
                <h2 class="font-semibold text-default dark:text-default">Submit Your Work</h2>

                <!-- Late warning -->
                <div v-if="assignment.is_overdue && assignment.allow_late"
                    class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-xs text-amber-700 dark:text-amber-300">
                    This assignment is past due. Late submissions are accepted but may be penalised.
                </div>

                <!-- Drop zone -->
                <div @click="fileRef.click()" @drop.prevent="onFile" @dragover.prevent="dragOver = true" @dragleave="dragOver = false"
                    :class="['rounded-xl border-2 border-dashed p-8 text-center cursor-pointer transition-colors', dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-base dark:border-base hover:border-primary-400 dark:hover:border-primary-600']">
                    <input ref="fileRef" type="file" accept=".pdf,.doc,.docx,.zip" class="sr-only" @change="onFile" />
                    <div v-if="!fileName">
                        <svg class="mx-auto h-10 w-10 text-subtle dark:text-muted mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium text-muted dark:text-muted">Drop your file here, or click to browse</p>
                        <p class="text-xs text-subtle mt-1">PDF, DOC, DOCX, ZIP — max 20 MB</p>
                    </div>
                    <div v-else class="flex items-center justify-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="font-medium truncate max-w-xs">{{ fileName }}</span>
                        <button @click.stop="form.file = null; fileName = null" class="text-subtle hover:text-red-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.file" class="text-xs text-red-500">{{ form.errors.file }}</p>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Notes (optional)</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Any notes for your instructor…"
                        class="w-full rounded-xl border border-base dark:border-base bg-transparent px-4 py-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 resize-none"></textarea>
                </div>

                <button @click="submit" :disabled="form.processing || !form.file"
                    class="w-full rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-40 flex items-center justify-center gap-2">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ form.processing ? 'Submitting…' : 'Submit Assignment' }}
                </button>
            </div>
        </div>
    </StudentLayout>
</template>
