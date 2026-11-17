<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    attempt:  Object,
    quiz:     Object,
    reviewed: Array,
    canRetake: Boolean,
})

const scoreColor = computed(() => {
    if (props.attempt.passed) return 'text-emerald-600 dark:text-emerald-400'
    if (props.attempt.score >= props.quiz.passing_marks * 0.7) return 'text-amber-600 dark:text-amber-400'
    return 'text-red-600 dark:text-red-400'
})

const stats = computed(() => [
    { label: 'Correct',       value: props.reviewed.filter(q => q.is_correct).length,                       color: 'text-emerald-600 dark:text-emerald-400' },
    { label: 'Incorrect',     value: props.reviewed.filter(q => !q.is_correct && q.type !== 'short').length, color: 'text-red-600 dark:text-red-400' },
    { label: 'Manual Review', value: props.reviewed.filter(q => q.type === 'short').length,                  color: 'text-amber-600 dark:text-amber-400' },
])

const typeColor = {
    mcq:       'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
    truefalse: 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
    short:     'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
}
const typeLabel = { mcq: 'MCQ', truefalse: 'T/F', short: 'Short' }
</script>

<template>
    <div class="min-h-screen bg-surface-2 dark:bg-surface-950" style="font-family: system-ui, sans-serif;">
        <div class="mx-auto max-w-2xl px-4 py-10 space-y-8">

            <!-- Score card -->
            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface p-8 text-center shadow-sm">
                <div :class="['text-7xl font-bold tabular-nums leading-none mb-2', scoreColor]">{{ attempt.score }}%</div>
                <div :class="['inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold mb-4', attempt.passed ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300']">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="attempt.passed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ attempt.passed ? 'Passed' : 'Not Passed' }}
                </div>
                <p class="text-muted dark:text-muted text-sm">{{ quiz.title }}</p>
                <p class="text-xs text-subtle dark:text-subtle mt-1">Passing score: {{ quiz.passing_marks }}%</p>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-3">
                <div v-for="item in stats" :key="item.label"
                    class="rounded-xl border border-base dark:border-surface-800 bg-surface p-4 text-center">
                    <p :class="['text-2xl font-bold tabular-nums', item.color]">{{ item.value }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ item.label }}</p>
                </div>
            </div>

            <!-- Manual grading notice -->
            <div v-if="attempt.needs_manual_grading" class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 flex gap-3 items-start">
                <svg class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Pending manual grading</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Your short-answer responses are being reviewed by an instructor. The final result may change.</p>
                </div>
            </div>

            <!-- Answer review -->
            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-100 dark:border-surface-800">
                    <h2 class="font-semibold text-default dark:text-default">Answer Review</h2>
                </div>
                <div class="divide-y divide-surface-100 dark:divide-surface-800">
                    <div v-for="(q, i) in reviewed" :key="q.id" class="px-6 py-5">
                        <div class="flex items-start gap-3">
                            <!-- Indicator -->
                            <div :class="['mt-0.5 h-5 w-5 rounded-full flex items-center justify-center shrink-0', q.type === 'short' ? 'bg-amber-100 dark:bg-amber-900/40' : q.is_correct ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-red-100 dark:bg-red-900/40']">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path v-if="q.type === 'short'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01"/>
                                    <path v-else-if="q.is_correct" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" :class="'text-emerald-600 dark:text-emerald-400'"/>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" :class="'text-red-600 dark:text-red-400'"/>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs text-subtle dark:text-subtle">Q{{ i + 1 }}</span>
                                    <span :class="['text-xs rounded px-1.5 py-0.5 font-medium', typeColor[q.type]]">{{ typeLabel[q.type] }}</span>
                                </div>
                                <p class="text-sm text-default dark:text-default leading-relaxed mb-2">{{ q.question }}</p>

                                <!-- MCQ options -->
                                <div v-if="q.type === 'mcq' && q.options" class="space-y-1.5">
                                    <div v-for="opt in q.options" :key="opt"
                                        :class="['flex items-center gap-2 rounded-lg px-3 py-2 text-sm', opt === q.correct_answer ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' : opt === q.student_answer && !q.is_correct ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-surface-2 dark:bg-surface/50 border border-transparent']">
                                        <svg v-if="opt === q.correct_answer" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <svg v-else-if="opt === q.student_answer" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        <div v-else class="h-3.5 w-3.5 shrink-0"></div>
                                        <span :class="opt === q.correct_answer ? 'text-emerald-700 dark:text-emerald-300 font-medium' : opt === q.student_answer ? 'text-red-700 dark:text-red-300' : 'text-muted dark:text-muted'">{{ opt }}</span>
                                    </div>
                                </div>

                                <!-- T/F -->
                                <div v-if="q.type === 'truefalse'" class="flex gap-2">
                                    <span v-for="val in ['True','False']" :key="val"
                                        :class="['rounded-lg border-2 px-4 py-2 text-sm font-medium', val === q.correct_answer ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : val === q.student_answer && !q.is_correct ? 'border-red-400 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'border-base dark:border-base text-muted dark:text-muted']">
                                        {{ val }}
                                    </span>
                                </div>

                                <!-- Short -->
                                <div v-if="q.type === 'short'" class="space-y-2">
                                    <div class="rounded-lg bg-surface-2 dark:bg-surface/50 px-3 py-2 text-sm">
                                        <p class="text-xs text-subtle dark:text-subtle mb-0.5">Your answer</p>
                                        <p class="text-muted dark:text-muted">{{ q.student_answer || '(no answer)' }}</p>
                                    </div>
                                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/10 px-3 py-2 text-sm">
                                        <p class="text-xs text-amber-500 dark:text-amber-400 mb-0.5">Awaiting review</p>
                                    </div>
                                </div>

                                <!-- Explanation -->
                                <p v-if="q.explanation && q.type !== 'short'" class="mt-2 text-xs text-subtle dark:text-subtle italic">{{ q.explanation }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <Link :href="route('student.courses.index')" class="flex-1 rounded-xl border border-base dark:border-base py-3 text-center text-sm font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-800">
                    Back to Courses
                </Link>
                <Link v-if="canRetake" :href="route('student.quizzes.start', quiz.id)" method="get" as="button"
                    class="flex-1 rounded-xl bg-primary-600 py-3 text-center text-sm font-semibold text-white hover:bg-primary-700">
                    Retake Quiz
                </Link>
            </div>
        </div>
    </div>
</template>
