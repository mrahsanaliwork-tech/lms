<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    quizzes: Object,
    courses: Array,
    filters: Object,
})

const search    = ref(props.filters.search ?? '')
const status    = ref(props.filters.status ?? '')
const courseId  = ref(props.filters.course ?? '')

let timer = null
watch([search, status, courseId], () => {
    clearTimeout(timer)
    timer = setTimeout(() => router.get(route('admin.quizzes.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        course: courseId.value || undefined,
    }, { preserveState: true, replace: true }), 400)
})

function toggleStatus(quiz) {
    const action = quiz.status === 'published' ? 'unpublish' : 'publish'
    router.post(route(`admin.quizzes.${action}`, quiz.id))
}

function destroy(quiz) {
    if (!confirm(`Delete "${quiz.title}"?`)) return
    router.delete(route('admin.quizzes.destroy', quiz.id))
}

const statusVariant = { draft: 'default', published: 'success', archived: 'danger' }
</script>

<template>
    <AdminLayout title="Quizzes">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Quizzes</h1>
                    <p class="text-sm text-muted dark:text-muted">Manage quiz bank</p>
                </div>
                <a :href="route('admin.quizzes.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Quiz
                </a>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input v-model="search" type="search" placeholder="Search quizzes…" class="w-full rounded-lg border border-base dark:border-base bg-surface py-2 pl-9 pr-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <select v-model="status" class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
                <select v-model="courseId" class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Courses</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Quiz</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Questions</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Time</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Pass %</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Attempts</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!quizzes.data.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-subtle">No quizzes yet.</td>
                        </tr>
                        <tr v-for="q in quizzes.data" :key="q.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-default dark:text-default">{{ q.title }}</p>
                                <p class="text-xs text-muted dark:text-muted">{{ q.course }}</p>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">{{ q.questions_count }}</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">{{ q.time_limit ? `${q.time_limit}m` : '∞' }}</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">{{ q.passing_marks }}%</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">
                                {{ q.attempts_count }} taken
                                <span class="text-subtle">/ {{ q.attempts_allowed === 0 ? '∞' : q.attempts_allowed }} allowed</span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge :variant="statusVariant[q.status]">{{ q.status }}</Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="toggleStatus(q)" :title="q.status === 'published' ? 'Unpublish' : 'Publish'"
                                        :class="['rounded p-1.5 text-subtle', q.status === 'published' ? 'hover:text-amber-600' : 'hover:text-emerald-600']">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path v-if="q.status === 'published'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <a :href="route('admin.quizzes.show', q.id)" class="rounded p-1.5 text-subtle hover:text-primary-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a :href="route('admin.quizzes.edit', q.id)" class="rounded p-1.5 text-subtle hover:text-amber-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button @click="destroy(q)" class="rounded p-1.5 text-subtle hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="quizzes.last_page > 1" class="flex items-center justify-between border-t border-base dark:border-base px-5 py-3">
                    <p class="text-sm text-muted dark:text-muted">Showing {{ quizzes.from }}–{{ quizzes.to }} of {{ quizzes.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in quizzes.links" :key="link.label" :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
