<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ workflow: Object })

// ── Constants ────────────────────────────────────────────────────────────────

const TRIGGERS = [
    { value: 'student.registered',   label: 'Student Registers',        icon: 'user-plus',  desc: 'When a new student account is created' },
    { value: 'student.enrolled',     label: 'Student Enrolls',          icon: 'book-open',  desc: 'When a student enrolls in a course' },
    { value: 'payment.completed',    label: 'Payment Completed',        icon: 'credit-card',desc: 'When a successful payment is processed' },
    { value: 'course.completed',     label: 'Course Completed',         icon: 'badge-check', desc: 'When a student finishes all lessons' },
    { value: 'quiz.completed',       label: 'Quiz Completed',           icon: 'clipboard',  desc: 'When a student submits a quiz attempt' },
    { value: 'assignment.submitted', label: 'Assignment Submitted',     icon: 'paper-clip', desc: 'When a student submits an assignment' },
]

const CONDITION_FIELDS = {
    'student.registered':   ['user_name', 'user_email'],
    'student.enrolled':     ['user_name', 'course_title', 'course_id'],
    'payment.completed':    ['amount', 'course_id'],
    'course.completed':     ['user_name', 'course_title', 'course_id'],
    'quiz.completed':       ['score', 'total', 'passed', 'quiz_title'],
    'assignment.submitted': ['student_name', 'assignment_title', 'course_id'],
}

const OPERATORS = [
    { value: 'equals',          label: 'equals' },
    { value: 'not_equals',      label: 'does not equal' },
    { value: 'contains',        label: 'contains' },
    { value: 'greater_than',    label: 'greater than' },
    { value: 'less_than',       label: 'less than' },
    { value: 'is_empty',        label: 'is empty' },
    { value: 'is_not_empty',    label: 'is not empty' },
]

const ACTIONS = [
    { value: 'send_welcome_email',     label: 'Send welcome email',        icon: 'mail',       fields: [] },
    { value: 'send_enrollment_email',  label: 'Send enrollment email',     icon: 'mail',       fields: [] },
    { value: 'send_payment_email',     label: 'Send payment confirmation', icon: 'mail',       fields: [] },
    { value: 'send_certificate_email', label: 'Send certificate email',    icon: 'badge-check', fields: [] },
    { value: 'send_notification',      label: 'Send in-app notification',  icon: 'bell',       fields: ['title', 'message', 'url'] },
    { value: 'notify_instructor',      label: 'Notify instructor',         icon: 'bell',       fields: ['message'] },
    { value: 'enroll_student',         label: 'Enroll in course',          icon: 'book-open',  fields: ['course_id'] },
    { value: 'add_role',               label: 'Assign role',               icon: 'user',       fields: ['role'] },
]

// ── Form state ───────────────────────────────────────────────────────────────

const form = useForm({
    name:         props.workflow?.name ?? '',
    trigger_type: props.workflow?.trigger_type ?? '',
    is_active:    props.workflow?.is_active ?? true,
    conditions:   props.workflow?.conditions ?? { operator: 'and', rules: [] },
    actions:      props.workflow?.actions ?? [],
})

const isEditing = computed(() => !!props.workflow)

// ── Conditions helpers ───────────────────────────────────────────────────────

function addCondition() {
    const fields = CONDITION_FIELDS[form.trigger_type] ?? []
    form.conditions.rules.push({
        field: fields[0] ?? '',
        operator: 'equals',
        value: '',
    })
}

function removeCondition(i) {
    form.conditions.rules.splice(i, 1)
}

function fieldsForTrigger() {
    return CONDITION_FIELDS[form.trigger_type] ?? []
}

// ── Actions helpers ──────────────────────────────────────────────────────────

function addAction(type) {
    const def = ACTIONS.find(a => a.value === type)
    if (!def) return
    const action = { type }
    def.fields.forEach(f => { action[f] = '' })
    form.actions.push(action)
}

function removeAction(i) {
    form.actions.splice(i, 1)
}

function actionDef(type) {
    return ACTIONS.find(a => a.value === type) ?? { label: type, fields: [] }
}

function moveAction(i, dir) {
    const j = i + dir
    if (j < 0 || j >= form.actions.length) return
    const tmp = form.actions[i]
    form.actions[i] = form.actions[j]
    form.actions[j] = tmp
}

// ── Trigger icon helpers ─────────────────────────────────────────────────────

const ICONS = {
    'user-plus':   'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 018.624 21 12.317 12.317 0 014 19.235z',
    'book-open':   'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    'credit-card': 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
    'badge-check': 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    'clipboard':   'M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184',
    'paper-clip':  'M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13',
    'mail':        'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    'bell':        'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
    'user':        'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
}

// ── Save ─────────────────────────────────────────────────────────────────────

function save() {
    if (isEditing.value) {
        form.patch(route('admin.automation.update', props.workflow.id))
    } else {
        form.post(route('admin.automation.store'))
    }
}

// new action picker state
const showActionPicker = ref(false)
</script>

<template>
    <AdminLayout :title="isEditing ? 'Edit Workflow' : 'New Workflow'">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Back -->
            <a :href="route('admin.automation.index')"
                class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to workflows
            </a>

            <!-- Name + Active -->
            <div class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-muted mb-1">Workflow Name *</label>
                        <input v-model="form.name" type="text" placeholder="e.g. Welcome new students"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="form.errors.name" class="mt-0.5 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2 mt-5">
                        <button @click="form.is_active = !form.is_active"
                            :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.is_active ? 'bg-primary-600' : 'bg-surface-300 dark:bg-surface-2']">
                            <span :class="['inline-block h-4 w-4 rounded-full bg-white shadow transition-transform', form.is_active ? 'translate-x-6' : 'translate-x-1']" />
                        </button>
                        <span class="text-sm text-muted dark:text-default">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>

            <!-- Step 1: Trigger -->
            <div class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-base dark:border-base bg-surface-2 dark:bg-surface">
                    <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                        <span class="text-xs font-bold text-primary-700 dark:text-primary-300">1</span>
                    </div>
                    <div>
                        <h2 class="font-semibold text-default dark:text-default text-sm">Trigger</h2>
                        <p class="text-xs text-subtle">When this event occurs, run the workflow</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button v-for="t in TRIGGERS" :key="t.value" @click="form.trigger_type = t.value; form.conditions.rules = []"
                        :class="['flex items-start gap-3 rounded-xl border-2 p-4 text-left transition-colors', form.trigger_type === t.value ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-900/20' : 'border-base dark:border-base hover:border-primary-300 hover:bg-surface-2 dark:hover:bg-surface-700/40']">
                        <div :class="['w-9 h-9 shrink-0 rounded-xl flex items-center justify-center', form.trigger_type === t.value ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-surface-3 dark:bg-surface-2']">
                            <svg class="w-4.5 h-4.5" :class="form.trigger_type === t.value ? 'text-primary-600 dark:text-primary-400' : 'text-muted'" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="ICONS[t.icon] ?? ICONS.bell" />
                            </svg>
                        </div>
                        <div>
                            <p :class="['text-sm font-medium', form.trigger_type === t.value ? 'text-primary-700 dark:text-primary-300' : 'text-default dark:text-default']">{{ t.label }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ t.desc }}</p>
                        </div>
                    </button>
                </div>
                <p v-if="form.errors.trigger_type" class="px-6 pb-4 text-xs text-red-500">{{ form.errors.trigger_type }}</p>
            </div>

            <!-- Step 2: Conditions -->
            <div v-if="form.trigger_type" class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-base dark:border-base bg-surface-2 dark:bg-surface">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-300">2</span>
                        </div>
                        <div>
                            <h2 class="font-semibold text-default dark:text-default text-sm">Conditions <span class="text-subtle font-normal">(optional)</span></h2>
                            <p class="text-xs text-subtle">Only run if these conditions are met</p>
                        </div>
                    </div>
                    <div v-if="form.conditions.rules.length > 1" class="flex items-center gap-2">
                        <span class="text-xs text-muted">Match</span>
                        <select v-model="form.conditions.operator"
                            class="rounded-lg border border-base dark:border-base bg-surface-2 px-2 py-1 text-xs text-muted dark:text-default">
                            <option value="and">ALL (and)</option>
                            <option value="or">ANY (or)</option>
                        </select>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <div v-for="(rule, i) in form.conditions.rules" :key="i"
                        class="flex items-center gap-2 flex-wrap">
                        <select v-model="rule.field"
                            class="rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option v-for="f in fieldsForTrigger()" :key="f" :value="f">{{ f }}</option>
                        </select>
                        <select v-model="rule.operator"
                            class="rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option v-for="op in OPERATORS" :key="op.value" :value="op.value">{{ op.label }}</option>
                        </select>
                        <input v-if="!['is_empty','is_not_empty'].includes(rule.operator)"
                            v-model="rule.value" type="text" placeholder="Value"
                            class="flex-1 min-w-24 rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <button @click="removeCondition(i)" class="p-2 text-subtle hover:text-red-500 rounded">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <button @click="addCondition"
                        class="inline-flex items-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add condition
                    </button>
                </div>
            </div>

            <!-- Step 3: Actions -->
            <div v-if="form.trigger_type" class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-base dark:border-base bg-surface-2 dark:bg-surface">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">3</span>
                    </div>
                    <div>
                        <h2 class="font-semibold text-default dark:text-default text-sm">Actions *</h2>
                        <p class="text-xs text-subtle">Run these in order when triggered</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">

                    <!-- Action cards -->
                    <div v-for="(action, i) in form.actions" :key="i"
                        class="rounded-xl border border-base dark:border-base bg-surface-2 dark:bg-surface p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="ICONS[actionDef(action.type).icon] ?? ICONS.bell" />
                                </svg>
                                <span class="text-sm font-medium text-default dark:text-default">{{ actionDef(action.type).label }}</span>
                                <span class="text-xs text-subtle bg-surface-3 dark:bg-surface-2 rounded-full px-2 py-0.5">#{{ i + 1 }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="moveAction(i, -1)" :disabled="i === 0" class="p-1 rounded text-subtle hover:text-muted disabled:opacity-30">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button @click="moveAction(i, 1)" :disabled="i === form.actions.length - 1" class="p-1 rounded text-subtle hover:text-muted disabled:opacity-30">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button @click="removeAction(i)" class="p-1 rounded text-subtle hover:text-red-500 ml-1">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic fields per action type -->
                        <div v-if="actionDef(action.type).fields.length" class="grid grid-cols-1 gap-2">
                            <div v-for="field in actionDef(action.type).fields" :key="field">
                                <label class="block text-xs font-medium text-muted mb-0.5 capitalize">{{ field.replace('_', ' ') }}</label>
                                <textarea v-if="field === 'message'" v-model="action[field]" rows="2"
                                    class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none" />
                                <input v-else v-model="action[field]" type="text"
                                    class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Add action picker -->
                    <div class="relative">
                        <button @click="showActionPicker = !showActionPicker"
                            class="inline-flex items-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add action
                        </button>
                        <div v-if="showActionPicker"
                            class="absolute z-10 mt-1 w-72 rounded-xl border border-base dark:border-base bg-surface shadow-xl overflow-hidden">
                            <button v-for="a in ACTIONS" :key="a.value"
                                @click="addAction(a.value); showActionPicker = false"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-left hover:bg-surface-2 dark:hover:bg-surface-700">
                                <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="ICONS[a.icon] ?? ICONS.bell" />
                                </svg>
                                <span class="text-sm text-muted dark:text-default">{{ a.label }}</span>
                            </button>
                        </div>
                    </div>

                    <p v-if="form.errors.actions" class="text-xs text-red-500">{{ form.errors.actions }}</p>
                </div>
            </div>

            <!-- Save bar -->
            <div class="sticky bottom-0 flex items-center justify-end gap-3 rounded-xl border border-base dark:border-base bg-surface px-6 py-4">
                <a :href="route('admin.automation.index')"
                    class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2">
                    Cancel
                </a>
                <button @click="save" :disabled="form.processing || !form.trigger_type || !form.actions.length"
                    class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                    {{ form.processing ? 'Saving…' : (isEditing ? 'Save Changes' : 'Create Workflow') }}
                </button>
            </div>

        </div>
    </AdminLayout>
</template>
