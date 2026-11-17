<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    quiz: Object,
    questions: Array,
    courses: Array,
})

const typeLabels = { mcq: 'Multiple Choice', truefalse: 'True / False', short: 'Short Answer' }

// Pre-fill questions with expanded flag + _id
const form = useForm({
    course_id:        props.quiz.course_id,
    lesson_id:        props.quiz.lesson_id ?? '',
    title:            props.quiz.title,
    time_limit:       props.quiz.time_limit ?? '',
    passing_marks:    props.quiz.passing_marks,
    attempts_allowed: props.quiz.attempts_allowed,
    is_randomized:    props.quiz.is_randomized,
    status:           props.quiz.status,
    questions:        props.questions.map(q => ({
        ...q,
        _id:      q.id,
        options:  q.options ?? (q.type === 'mcq' ? ['','','',''] : q.type === 'truefalse' ? ['True','False'] : []),
        expanded: false,
    })),
})

function emptyQuestion(type = 'mcq') {
    return {
        _id: Date.now() + Math.random(), id: null, question: '', type,
        options: type === 'mcq' ? ['','','',''] : type === 'truefalse' ? ['True','False'] : [],
        correct_answer: '', marks: 1, explanation: '', expanded: true,
    }
}

function addQuestion(type = 'mcq') { form.questions.push(emptyQuestion(type)) }
function removeQuestion(idx) { form.questions.splice(idx, 1) }

function onTypeChange(q) {
    q.correct_answer = ''
    if (q.type === 'mcq')       { q.options = ['','','',''] }
    if (q.type === 'truefalse') { q.options = ['True','False'] }
    if (q.type === 'short')     { q.options = [] }
}

function addOption(q) { if (q.options.length < 6) q.options.push('') }
function removeOption(q, i) {
    if (q.options.length > 2) {
        if (q.correct_answer === q.options[i]) q.correct_answer = ''
        q.options.splice(i, 1)
    }
}

const dragIdx = ref(null)
function onDragStart(i) { dragIdx.value = i }
function onDragOver(e, i) {
    e.preventDefault()
    if (dragIdx.value === null || dragIdx.value === i) return
    const item = form.questions.splice(dragIdx.value, 1)[0]
    form.questions.splice(i, 0, item)
    dragIdx.value = i
}

const totalMarks = computed(() => form.questions.reduce((s, q) => s + (Number(q.marks) || 0), 0))
const isValid = computed(() => form.title.trim() && form.questions.length && form.questions.every(q => q.question.trim() && q.correct_answer))

function submit() {
    const cleaned = form.questions.map(({ _id, expanded, ...q }) => ({
        ...q,
        options: q.type === 'short' ? null : q.options.filter(o => o.trim()),
    }))
    form.questions = cleaned
    form.patch(route('admin.quizzes.update', props.quiz.id))
}
</script>

<template>
    <AdminLayout :title="`Edit — ${quiz.title}`">
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center gap-3">
                <a :href="route('admin.quizzes.show', quiz.id)" class="rounded p-1 text-subtle hover:text-muted dark:hover:text-surface-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Edit Quiz</h1>
            </div>

            <!-- Settings -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Settings</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Title</label>
                        <input v-model="form.title" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Course</label>
                        <select v-model="form.course_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Time Limit (min)</label>
                        <input v-model="form.time_limit" type="number" min="1" placeholder="No limit" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Passing Score (%)</label>
                        <input v-model="form.passing_marks" type="number" min="1" max="100" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Attempts Allowed</label>
                        <input v-model="form.attempts_allowed" type="number" min="0" placeholder="0=unlimited" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div class="flex items-center gap-3 pt-5">
                        <input id="is_randomized" type="checkbox" v-model="form.is_randomized" class="h-4 w-4 rounded border-base text-primary-600" />
                        <label for="is_randomized" class="text-sm text-muted dark:text-default cursor-pointer">Randomize order</label>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Questions · {{ form.questions.length }} · {{ totalMarks }} marks</h2>
                    <div class="flex gap-2">
                        <button v-for="type in ['mcq','truefalse','short']" :key="type" @click="addQuestion(type)"
                            class="rounded-lg border border-base dark:border-base px-3 py-1.5 text-xs font-medium text-muted dark:text-muted hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-700 hover:border-primary-300">
                            + {{ typeLabels[type] }}
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="(q, qi) in form.questions" :key="q._id"
                        draggable="true" @dragstart="onDragStart(qi)" @dragover="onDragOver($event, qi)"
                        class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2/40 overflow-hidden">
                        <div class="flex items-center gap-3 px-4 py-2.5 cursor-pointer" @click="q.expanded = !q.expanded">
                            <svg class="h-4 w-4 text-subtle shrink-0 cursor-grab" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="flex-1 text-sm font-medium text-muted dark:text-default truncate">Q{{ qi + 1 }}. {{ q.question || `(${typeLabels[q.type]})` }}</span>
                            <span class="text-xs text-subtle">{{ q.marks }} mark{{ q.marks !== 1 ? 's' : '' }}</span>
                            <svg :class="['h-4 w-4 text-subtle transition-transform', q.expanded ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <button @click.stop="removeQuestion(qi)" class="text-subtle hover:text-red-500 shrink-0"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div v-if="q.expanded" class="border-t border-base dark:border-base px-4 py-4 space-y-4 bg-surface">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-muted mb-1">Type</label>
                                    <select v-model="q.type" @change="onTypeChange(q)" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default">
                                        <option value="mcq">Multiple Choice</option>
                                        <option value="truefalse">True / False</option>
                                        <option value="short">Short Answer</option>
                                    </select>
                                </div>
                                <div class="w-28">
                                    <label class="block text-xs font-medium text-muted mb-1">Marks</label>
                                    <input v-model.number="q.marks" type="number" min="1" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Question</label>
                                <textarea v-model="q.question" rows="2" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                            </div>
                            <div v-if="q.type === 'mcq'" class="space-y-2">
                                <label class="block text-xs font-medium text-muted">Options</label>
                                <div v-for="(opt, oi) in q.options" :key="oi" class="flex items-center gap-2">
                                    <input type="radio" :name="`eq_${qi}`" :value="opt" v-model="q.correct_answer" class="h-4 w-4 text-emerald-600" />
                                    <input v-model="q.options[oi]" type="text" :placeholder="`Option ${oi+1}`" class="flex-1 rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                    <button v-if="q.options.length > 2" @click="removeOption(q,oi)" class="text-subtle hover:text-red-500"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                                <button v-if="q.options.length < 6" @click="addOption(q)" class="text-xs text-primary-600">+ Add option</button>
                            </div>
                            <div v-if="q.type === 'truefalse'" class="flex gap-4">
                                <label v-for="val in ['True','False']" :key="val" class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" :name="`etf_${qi}`" :value="val" v-model="q.correct_answer" class="h-4 w-4 text-emerald-600" />
                                    <span class="rounded-lg border-2 px-4 py-2 text-sm" :class="q.correct_answer === val ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'border-base dark:border-base text-muted dark:text-default'">{{ val }}</span>
                                </label>
                            </div>
                            <div v-if="q.type === 'short'">
                                <label class="block text-xs font-medium text-muted mb-1">Model Answer</label>
                                <input v-model="q.correct_answer" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Explanation</label>
                                <input v-model="q.explanation" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a :href="route('admin.quizzes.show', quiz.id)" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2">Cancel</a>
                <button @click="submit" :disabled="form.processing || !isValid" class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-40 flex items-center gap-2">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
