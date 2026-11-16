<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'

const props = defineProps({
    assignments: Object,
    courses:     Array,
})

const showCreate = ref(false)

const form = useForm({
    course_id:             '',
    title:                 '',
    description:           '',
    due_date:              '',
    max_marks:             100,
    allow_late_submission: false,
})

function submit() {
    form.post(route('instructor.assignments.store'), {
        onSuccess: () => { showCreate.value = false; form.reset() },
    })
}

function destroy(id) {
    if (!confirm('Delete this assignment?')) return
    router.delete(route('instructor.assignments.destroy', id))
}
</script>

<template>
    <InstructorLayout title="Assignments">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Assignments</h1>
                    <p class="text-sm text-muted dark:text-muted">Create and grade student assignments</p>
                </div>
                <button @click="showCreate = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Assignment
                </button>
            </div>

            <!-- Create modal -->
            <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div class="w-full max-w-lg rounded-2xl bg-surface p-6 shadow-xl space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-default dark:text-default">New Assignment</h2>
                        <button @click="showCreate = false" class="text-subtle hover:text-muted dark:hover:text-surface-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-muted mb-1">Course</label>
                            <select v-model="form.course_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                                <option value="">Select course…</option>
                                <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                            <p v-if="form.errors.course_id" class="mt-1 text-xs text-red-500">{{ form.errors.course_id }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-muted mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-muted mb-1">Description</label>
                            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Due Date</label>
                            <input v-model="form.due_date" type="datetime-local" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            <p v-if="form.errors.due_date" class="mt-1 text-xs text-red-500">{{ form.errors.due_date }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Max Marks</label>
                            <input v-model.number="form.max_marks" type="number" min="1" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div class="sm:col-span-2 flex items-center gap-2">
                            <input id="allow_late" type="checkbox" v-model="form.allow_late_submission" class="h-4 w-4 rounded border-base text-primary-600" />
                            <label for="allow_late" class="text-sm text-muted dark:text-default cursor-pointer">Allow late submissions</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="showCreate = false" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default">Cancel</button>
                        <button @click="submit" :disabled="form.processing" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                            {{ form.processing ? 'Creating…' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Assignment</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Due</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Marks</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Submissions</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Pending</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!assignments.data.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-subtle">No assignments yet.</td>
                        </tr>
                        <tr v-for="a in assignments.data" :key="a.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40">
                            <td class="px-5 py-3">
                                <p class="font-medium text-default dark:text-default">{{ a.title }}</p>
                                <p class="text-xs text-muted dark:text-muted">{{ a.course }}</p>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <span :class="a.is_overdue ? 'text-red-600 dark:text-red-400' : 'text-muted dark:text-muted'">
                                    {{ a.due_date ? new Date(a.due_date).toLocaleDateString() : '—' }}
                                </span>
                                <span v-if="a.is_overdue" class="ml-1 text-xs text-red-400">(overdue)</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">{{ a.max_marks }}</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">{{ a.submissions_count }}</td>
                            <td class="px-5 py-3">
                                <span v-if="a.pending_count > 0" class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
                                    {{ a.pending_count }} to grade
                                </span>
                                <span v-else class="text-xs text-emerald-600 dark:text-emerald-400">All graded</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a :href="route('instructor.assignments.show', a.id)" class="rounded p-1.5 text-subtle hover:text-primary-600" title="View submissions">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button @click="destroy(a.id)" class="rounded p-1.5 text-subtle hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div v-if="assignments.last_page > 1" class="flex items-center justify-between border-t border-base dark:border-base px-5 py-3">
                    <p class="text-sm text-muted">Showing {{ assignments.from }}–{{ assignments.to }} of {{ assignments.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in assignments.links" :key="link.label" :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>
