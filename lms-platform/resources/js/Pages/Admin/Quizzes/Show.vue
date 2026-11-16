<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    quiz: Object,
    questions: Array,
    stats: Object,
})

function toggleStatus() {
    const action = props.quiz.status === 'published' ? 'unpublish' : 'publish'
    router.post(route(`admin.quizzes.${action}`, props.quiz.id))
}

function destroy() {
    if (!confirm(`Delete "${props.quiz.title}"?`)) return
    router.delete(route('admin.quizzes.destroy', props.quiz.id), {
        onSuccess: () => router.visit(route('admin.quizzes.index'))
    })
}

const statusVariant = { draft: 'default', published: 'success', archived: 'danger' }
const typeColor = {
    mcq: 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
    truefalse: 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300',
    short: 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300',
}
const typeLabels = { mcq: 'MCQ', truefalse: 'T/F', short: 'Short' }
</script>

<template>
    <AdminLayout :title="quiz.title">
        <div class="space-y-6">
            <a :href="route('admin.quizzes.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Quizzes
            </a>

            <!-- Header -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold text-default dark:text-default">{{ quiz.title }}</h1>
                        <p class="text-sm text-muted dark:text-muted">{{ quiz.course }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge :variant="statusVariant[quiz.status]">{{ quiz.status }}</Badge>
                    </div>
                </div>

                <!-- Config summary -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-surface-100 dark:border-base pt-4">
                    <div>
                        <p class="text-xs text-subtle">Time Limit</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ quiz.time_limit ? `${quiz.time_limit} min` : 'No limit' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-subtle">Passing Score</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ quiz.passing_marks }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-subtle">Attempts Allowed</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ quiz.attempts_allowed === 0 ? 'Unlimited' : quiz.attempts_allowed }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-subtle">Randomized</p>
                        <p class="text-sm font-medium text-default dark:text-default">{{ quiz.is_randomized ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 pt-1 border-t border-surface-100 dark:border-base">
                    <button @click="toggleStatus"
                        :class="['inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm', quiz.status === 'published' ? 'border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20']">
                        {{ quiz.status === 'published' ? 'Unpublish' : 'Publish' }}
                    </button>
                    <a :href="route('admin.quizzes.edit', quiz.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-base dark:border-base px-3 py-1.5 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">Edit Questions</a>
                    <button @click="destroy" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-800 px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="item in [{label:'Total Attempts',value:stats.attempts},{label:'Completed',value:stats.completed},{label:'Passed',value:stats.passed},{label:'Avg Score',value:stats.avg_score+'%'}]"
                    :key="item.label" class="rounded-xl border border-base dark:border-base bg-surface p-4 text-center">
                    <p class="text-2xl font-bold text-default dark:text-default">{{ item.value }}</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted">{{ item.label }}</p>
                </div>
            </div>

            <!-- Questions list -->
            <div class="rounded-xl border border-base dark:border-base bg-surface">
                <div class="px-6 py-4 border-b border-base dark:border-base">
                    <h2 class="font-semibold text-default dark:text-default">Questions ({{ questions.length }})</h2>
                </div>
                <div class="divide-y divide-surface-100 dark:divide-surface-700">
                    <div v-if="!questions.length" class="px-6 py-8 text-center text-sm text-subtle">No questions yet.</div>
                    <div v-for="(q, i) in questions" :key="q.id" class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 w-6 shrink-0 text-xs font-semibold text-subtle">{{ i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-default dark:text-default leading-relaxed">{{ q.question }}</p>

                                <!-- MCQ options -->
                                <ul v-if="q.type === 'mcq' && q.options" class="mt-2 space-y-1">
                                    <li v-for="opt in q.options" :key="opt" class="flex items-center gap-2 text-sm">
                                        <svg :class="['h-4 w-4 shrink-0', opt === q.correct_answer ? 'text-emerald-500' : 'text-subtle']" fill="currentColor" viewBox="0 0 20 20">
                                            <circle cx="10" cy="10" r="8"/>
                                        </svg>
                                        <span :class="opt === q.correct_answer ? 'text-emerald-700 dark:text-emerald-300 font-medium' : 'text-muted dark:text-muted'">{{ opt }}</span>
                                    </li>
                                </ul>

                                <!-- T/F -->
                                <p v-if="q.type === 'truefalse'" class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Correct: {{ q.correct_answer }}</p>

                                <!-- Short -->
                                <p v-if="q.type === 'short'" class="mt-1 text-xs text-subtle">Model answer: {{ q.correct_answer }}</p>

                                <p v-if="q.explanation" class="mt-1.5 text-xs text-subtle italic">{{ q.explanation }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span :class="['text-xs rounded px-2 py-0.5 font-medium', typeColor[q.type]]">{{ typeLabels[q.type] }}</span>
                                <span class="text-xs text-subtle">{{ q.marks }} mark{{ q.marks !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
