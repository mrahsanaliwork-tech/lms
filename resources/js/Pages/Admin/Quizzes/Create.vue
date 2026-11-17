<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    courses: Array,
    course_id: Number,
})

const form = useForm({
    course_id:        props.course_id ?? '',
    lesson_id:        '',
    title:            '',
    time_limit:       '',
    passing_marks:    50,
    attempts_allowed: 1,
    is_randomized:    false,
    status:           'draft',
    questions:        [],
})

// ── Question types config ─────────────────────────────────────────
const typeLabels = { mcq: 'Multiple Choice', truefalse: 'True / False', short: 'Short Answer' }

function emptyQuestion(type = 'mcq') {
    return {
        _id:            Date.now() + Math.random(),
        question:       '',
        type,
        options:        type === 'mcq' ? ['', '', '', ''] : type === 'truefalse' ? ['True', 'False'] : [],
        correct_answer: '',
        marks:          1,
        explanation:    '',
        expanded:       true,
    }
}

function addQuestion(type = 'mcq') {
    form.questions.push(emptyQuestion(type))
}

function removeQuestion(idx) {
    form.questions.splice(idx, 1)
}

function onTypeChange(q) {
    q.correct_answer = ''
    if (q.type === 'mcq')       { q.options = ['', '', '', ''] }
    if (q.type === 'truefalse') { q.options = ['True', 'False'] }
    if (q.type === 'short')     { q.options = [] }
}

function addOption(q) {
    if (q.options.length < 6) q.options.push('')
}
function removeOption(q, i) {
    if (q.options.length > 2) {
        if (q.correct_answer === q.options[i]) q.correct_answer = ''
        q.options.splice(i, 1)
    }
}

// ── Drag reorder questions ────────────────────────────────────────
const dragIdx = ref(null)
function onDragStart(i) { dragIdx.value = i }
function onDragOver(e, i) {
    e.preventDefault()
    if (dragIdx.value === null || dragIdx.value === i) return
    const item = form.questions.splice(dragIdx.value, 1)[0]
    form.questions.splice(i, 0, item)
    dragIdx.value = i
}

// ── Validation ────────────────────────────────────────────────────
const totalMarks = computed(() => form.questions.reduce((s, q) => s + (Number(q.marks) || 0), 0))
const isValid = computed(() => {
    if (!form.course_id || !form.title.trim() || form.questions.length === 0) return false
    return form.questions.every(q => q.question.trim() && q.correct_answer)
})

function submit() {
    // Strip internal _id and expanded before submitting
    const cleaned = form.questions.map(({ _id, expanded, ...q }) => ({
        ...q,
        options: q.type === 'short' ? null : q.options.filter(o => o.trim()),
    }))
    form.questions = cleaned
    form.post(route('admin.quizzes.store'), {
        onError: () => {
            // restore expanded state on error
            form.questions = form.questions.map(q => ({ ...q, _id: Date.now() + Math.random(), expanded: true }))
        }
    })
}
</script>

<template>
    <AdminLayout title="New Quiz">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Back -->
            <div class="flex items-center gap-3">
                <a :href="route('admin.quizzes.index')" class="rounded p-1 text-subtle hover:text-muted dark:hover:text-surface-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Create Quiz</h1>
            </div>

            <!-- Settings card -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Quiz Settings</h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Title <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" placeholder="e.g. Chapter 3 Assessment"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="form.errors.title ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Course <span class="text-red-500">*</span></label>
                        <select v-model="form.course_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="">Select course</option>
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
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Time Limit (minutes)</label>
                        <input v-model="form.time_limit" type="number" min="1" max="300" placeholder="No limit"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Passing Score (%)</label>
                        <input v-model="form.passing_marks" type="number" min="1" max="100"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Attempts Allowed</label>
                        <input v-model="form.attempts_allowed" type="number" min="0" placeholder="0 = unlimited"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p class="mt-0.5 text-xs text-subtle">0 = unlimited</p>
                    </div>
                    <div class="flex items-center gap-3 pt-5">
                        <input id="is_randomized" type="checkbox" v-model="form.is_randomized" class="h-4 w-4 rounded border-base text-primary-600" />
                        <label for="is_randomized" class="text-sm text-muted dark:text-default cursor-pointer">Randomize question order</label>
                    </div>
                </div>
            </div>

            <!-- Question Builder -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Questions</h2>
                        <p v-if="form.questions.length" class="text-xs text-subtle mt-0.5">{{ form.questions.length }} question{{ form.questions.length !== 1 ? 's' : '' }} · {{ totalMarks }} marks total</p>
                    </div>
                    <!-- Add question buttons -->
                    <div class="flex gap-2">
                        <button v-for="type in ['mcq', 'truefalse', 'short']" :key="type" @click="addQuestion(type)"
                            class="rounded-lg border border-base dark:border-base px-3 py-1.5 text-xs font-medium text-muted dark:text-muted hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-700 hover:border-primary-300">
                            + {{ typeLabels[type] }}
                        </button>
                    </div>
                </div>

                <p v-if="!form.questions.length" class="rounded-lg border-2 border-dashed border-base dark:border-base p-8 text-center text-sm text-subtle">
                    No questions yet. Add MCQ, True/False, or Short Answer questions above.
                </p>

                <p v-if="form.errors.questions" class="text-xs text-red-500">{{ form.errors.questions }}</p>

                <div class="space-y-3">
                    <div v-for="(q, qi) in form.questions" :key="q._id"
                        draggable="true" @dragstart="onDragStart(qi)" @dragover="onDragOver($event, qi)"
                        class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2/40 overflow-hidden">

                        <!-- Question header -->
                        <div class="flex items-center gap-3 px-4 py-2.5 cursor-pointer" @click="q.expanded = !q.expanded">
                            <svg class="h-4 w-4 text-subtle shrink-0 cursor-grab" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="flex-1 text-sm font-medium text-muted dark:text-default truncate">
                                Q{{ qi + 1 }}. {{ q.question || `(${typeLabels[q.type]})` }}
                            </span>
                            <span class="text-xs text-subtle shrink-0">{{ q.marks }} mark{{ q.marks !== 1 ? 's' : '' }}</span>
                            <span :class="['text-xs rounded px-2 py-0.5 shrink-0', q.type === 'mcq' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : q.type === 'truefalse' ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' : 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300']">
                                {{ typeLabels[q.type] }}
                            </span>
                            <svg :class="['h-4 w-4 text-subtle shrink-0 transition-transform', q.expanded ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <button @click.stop="removeQuestion(qi)" class="text-subtle hover:text-red-500 shrink-0">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Question body -->
                        <div v-if="q.expanded" class="border-t border-base dark:border-base px-4 py-4 space-y-4 bg-surface">
                            <!-- Type & marks row -->
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Type</label>
                                    <select v-model="q.type" @change="onTypeChange(q)" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default">
                                        <option value="mcq">Multiple Choice</option>
                                        <option value="truefalse">True / False</option>
                                        <option value="short">Short Answer</option>
                                    </select>
                                </div>
                                <div class="w-28">
                                    <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Marks</label>
                                    <input v-model.number="q.marks" type="number" min="1" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                </div>
                            </div>

                            <!-- Question text -->
                            <div>
                                <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Question <span class="text-red-500">*</span></label>
                                <textarea v-model="q.question" rows="2" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none" placeholder="Enter the question…"></textarea>
                            </div>

                            <!-- MCQ options -->
                            <div v-if="q.type === 'mcq'" class="space-y-2">
                                <label class="block text-xs font-medium text-muted dark:text-muted">Options <span class="text-xs text-subtle">(select the correct one)</span></label>
                                <div v-for="(opt, oi) in q.options" :key="oi" class="flex items-center gap-2">
                                    <input type="radio" :name="`q_${qi}_correct`" :value="opt || `opt_${oi}`" v-model="q.correct_answer"
                                        class="h-4 w-4 text-emerald-600 border-base focus:ring-emerald-500 shrink-0" />
                                    <input v-model="q.options[oi]" type="text" :placeholder="`Option ${oi + 1}`"
                                        class="flex-1 rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                    <button v-if="q.options.length > 2" @click="removeOption(q, oi)" class="text-subtle hover:text-red-500 shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <button v-if="q.options.length < 6" @click="addOption(q)" class="text-xs text-primary-600 hover:text-primary-700">+ Add option</button>
                            </div>

                            <!-- True/False -->
                            <div v-if="q.type === 'truefalse'" class="flex gap-4">
                                <label class="flex items-center gap-2 text-sm text-muted dark:text-default cursor-pointer">
                                    <input type="radio" name="`q_${qi}_tf`" value="True" v-model="q.correct_answer" class="h-4 w-4 text-emerald-600" />
                                    <span class="rounded-lg border-2 px-4 py-2" :class="q.correct_answer === 'True' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'border-base dark:border-base'">True</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm text-muted dark:text-default cursor-pointer">
                                    <input type="radio" name="`q_${qi}_tf`" value="False" v-model="q.correct_answer" class="h-4 w-4 text-emerald-600" />
                                    <span class="rounded-lg border-2 px-4 py-2" :class="q.correct_answer === 'False' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'border-base dark:border-base'">False</span>
                                </label>
                            </div>

                            <!-- Short Answer -->
                            <div v-if="q.type === 'short'">
                                <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Model Answer (for reference)</label>
                                <input v-model="q.correct_answer" type="text" placeholder="Expected answer (used for manual grading reference)"
                                    class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                <p class="mt-0.5 text-xs text-subtle">Short-answer questions require manual grading.</p>
                            </div>

                            <!-- Explanation -->
                            <div>
                                <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Explanation (shown after attempt)</label>
                                <input v-model="q.explanation" type="text" placeholder="Optional explanation…"
                                    class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between">
                <div v-if="form.questions.length" class="text-sm text-muted dark:text-muted">
                    {{ form.questions.length }} question{{ form.questions.length !== 1 ? 's' : '' }} · {{ totalMarks }} total marks
                </div>
                <div v-else></div>
                <div class="flex gap-3">
                    <a :href="route('admin.quizzes.index')" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">Cancel</a>
                    <button @click="submit" :disabled="form.processing || !isValid"
                        class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-40 flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ form.processing ? 'Creating…' : 'Create Quiz' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
