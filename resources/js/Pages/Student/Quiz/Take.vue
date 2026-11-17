<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    quiz:    Object,
    attempt: Object,
    questions: Array,
})

// answers keyed by question id
const answers = ref({})
const current = ref(0)
const warned  = ref(false)

// Timer
const secondsLeft = ref(0)
let timerHandle = null

onMounted(() => {
    if (props.quiz.time_limit) {
        const started = new Date(props.attempt.started_at).getTime()
        const totalSecs = props.quiz.time_limit * 60
        secondsLeft.value = Math.max(0, totalSecs - Math.floor((Date.now() - started) / 1000))
        timerHandle = setInterval(() => {
            secondsLeft.value = Math.max(0, secondsLeft.value - 1)
            if (secondsLeft.value === 0) {
                clearInterval(timerHandle)
                submitQuiz(true)
            }
        }, 1000)
    }
})

onUnmounted(() => { if (timerHandle) clearInterval(timerHandle) })

const timerDisplay = computed(() => {
    const s = secondsLeft.value
    const m = Math.floor(s / 60)
    const sec = s % 60
    return `${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`
})

const timerUrgent = computed(() => props.quiz.time_limit && secondsLeft.value > 0 && secondsLeft.value < 60)

const q = computed(() => props.questions[current.value])
const answered = computed(() => Object.keys(answers.value).length)
const progress = computed(() => Math.round((answered.value / props.questions.length) * 100))

const form = useForm({ answers: {} })

function submitQuiz(autoSubmit = false) {
    if (!autoSubmit) {
        const unanswered = props.questions.length - answered.value
        if (unanswered > 0 && !confirm(`You have ${unanswered} unanswered question${unanswered > 1 ? 's' : ''}. Submit anyway?`)) return
    }
    form.answers = answers.value
    form.post(route('student.quizzes.submit', props.attempt.id))
}
</script>

<template>
    <!-- Distraction-free: no sidebar, plain white/dark -->
    <div class="min-h-screen bg-surface-2 dark:bg-surface-950 flex flex-col" style="font-family: system-ui, sans-serif;">

        <!-- Top bar -->
        <header class="sticky top-0 z-10 border-b border-base dark:border-surface-800 bg-white/90 dark:bg-surface/90 backdrop-blur-sm">
            <div class="mx-auto max-w-2xl flex items-center gap-4 px-4 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-muted dark:text-muted leading-none">Quiz</p>
                    <h1 class="font-semibold text-default dark:text-default truncate leading-snug">{{ quiz.title }}</h1>
                </div>

                <!-- Timer -->
                <div v-if="quiz.time_limit" :class="['flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-mono font-semibold tabular-nums', timerUrgent ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 animate-pulse' : 'bg-surface-3 dark:bg-surface text-muted dark:text-default']">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    {{ timerDisplay }}
                </div>

                <!-- Progress -->
                <span class="text-xs text-muted dark:text-muted shrink-0">{{ answered }}/{{ questions.length }}</span>
            </div>
            <!-- Progress bar -->
            <div class="h-0.5 bg-surface-3 dark:bg-surface">
                <div class="h-full bg-primary-500 transition-all duration-300" :style="`width:${progress}%`"></div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 mx-auto w-full max-w-2xl px-4 py-8">
            <!-- Question navigation dots -->
            <div class="flex flex-wrap gap-1.5 mb-6">
                <button v-for="(_, i) in questions" :key="i" @click="current = i"
                    :class="['w-7 h-7 rounded-full text-xs font-medium transition-colors', i === current ? 'bg-primary-600 text-white' : answers[questions[i].id] !== undefined ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted hover:bg-surface-300 dark:hover:bg-surface-600']">
                    {{ i + 1 }}
                </button>
            </div>

            <!-- Question card -->
            <div v-if="q" class="rounded-2xl border border-base dark:border-surface-800 bg-surface shadow-sm p-6 space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-subtle dark:text-subtle mb-2">Question {{ current + 1 }} of {{ questions.length }}</p>
                        <p class="text-base font-medium text-default dark:text-default leading-relaxed">{{ q.question }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-surface-3 dark:bg-surface px-2 py-0.5 text-xs text-muted dark:text-muted">{{ q.marks }} mark{{ q.marks !== 1 ? 's' : '' }}</span>
                </div>

                <!-- MCQ -->
                <div v-if="q.type === 'mcq'" class="space-y-2.5">
                    <label v-for="opt in q.options" :key="opt"
                        :class="['flex items-center gap-3 rounded-xl border-2 px-4 py-3 cursor-pointer transition-colors', answers[q.id] === opt ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-base dark:border-base hover:border-primary-300 dark:hover:border-primary-700']">
                        <input type="radio" :name="`q_${q.id}`" :value="opt" v-model="answers[q.id]" class="sr-only" />
                        <div :class="['h-5 w-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors', answers[q.id] === opt ? 'border-primary-600 bg-primary-600' : 'border-base dark:border-base']">
                            <div v-if="answers[q.id] === opt" class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                        <span :class="['text-sm', answers[q.id] === opt ? 'text-primary-700 dark:text-primary-300 font-medium' : 'text-muted dark:text-default']">{{ opt }}</span>
                    </label>
                </div>

                <!-- True / False -->
                <div v-if="q.type === 'truefalse'" class="flex gap-3">
                    <button v-for="val in ['True', 'False']" :key="val" @click="answers[q.id] = val"
                        :class="['flex-1 rounded-xl border-2 py-4 text-sm font-semibold transition-colors', answers[q.id] === val ? (val === 'True' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300') : 'border-base dark:border-base text-muted dark:text-default hover:border-base dark:hover:border-surface-600']">
                        {{ val }}
                    </button>
                </div>

                <!-- Short Answer -->
                <div v-if="q.type === 'short'">
                    <textarea v-model="answers[q.id]" rows="3" placeholder="Type your answer…"
                        class="w-full rounded-xl border-2 border-base dark:border-base bg-transparent px-4 py-3 text-sm text-default dark:text-default placeholder-surface-400 dark:placeholder-surface-500 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 resize-none transition-colors"></textarea>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-6 flex items-center justify-between">
                <button @click="current--" :disabled="current === 0"
                    class="flex items-center gap-1.5 rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-muted hover:bg-surface-2 dark:hover:bg-surface-800 disabled:opacity-30 disabled:cursor-default">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Previous
                </button>

                <button v-if="current < questions.length - 1" @click="current++"
                    class="flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    Next
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <button v-else @click="submitQuiz(false)" :disabled="form.processing"
                    class="flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ form.processing ? 'Submitting…' : 'Submit Quiz' }}
                </button>
            </div>
        </main>
    </div>
</template>
