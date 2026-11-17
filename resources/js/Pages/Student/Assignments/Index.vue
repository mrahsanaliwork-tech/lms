<script setup>
import { ref } from 'vue'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
    upcoming:  Array,
    submitted: Array,
    missed:    Array,
})

const tab = ref('upcoming')

function daysUntil(dateStr) {
    if (!dateStr) return null
    const diff = new Date(dateStr) - Date.now()
    const days = Math.ceil(diff / 86400000)
    return days
}
</script>

<template>
    <StudentLayout title="Assignments">
        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Assignments</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Your upcoming and completed assignments</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 rounded-xl bg-surface-3 dark:bg-surface p-1">
                <button v-for="t in [
                    { key: 'upcoming',  label: 'Upcoming',  count: upcoming.length  },
                    { key: 'submitted', label: 'Submitted', count: submitted.length },
                    { key: 'missed',    label: 'Missed',    count: missed.length    },
                ]" :key="t.key" @click="tab = t.key"
                    :class="['flex-1 rounded-lg py-1.5 text-sm font-medium transition-colors flex items-center justify-center gap-1.5', tab === t.key ? 'bg-surface-2 text-default dark:text-default shadow-sm' : 'text-muted dark:text-muted hover:text-muted dark:hover:text-surface-200']">
                    {{ t.label }}
                    <span v-if="t.count > 0" :class="['text-xs rounded-full px-1.5', tab === t.key ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted']">{{ t.count }}</span>
                </button>
            </div>

            <!-- Upcoming -->
            <div v-if="tab === 'upcoming'" class="space-y-3">
                <div v-if="!upcoming.length" class="rounded-xl border border-base dark:border-surface-800 bg-surface p-10 text-center text-sm text-subtle">
                    No upcoming assignments.
                </div>
                <a v-for="a in upcoming" :key="a.id" :href="route('student.assignments.show', a.id)"
                    class="block rounded-xl border border-base dark:border-surface-800 bg-surface p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-default dark:text-default truncate">{{ a.title }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ a.course }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p :class="['text-xs font-semibold', daysUntil(a.due_date) !== null && daysUntil(a.due_date) <= 2 ? 'text-red-600 dark:text-red-400' : 'text-muted dark:text-muted']">
                                {{ daysUntil(a.due_date) === 0 ? 'Due today' : daysUntil(a.due_date) === 1 ? 'Due tomorrow' : `${daysUntil(a.due_date)}d left` }}
                            </p>
                            <p class="text-xs text-subtle mt-0.5">{{ a.max_marks }} marks</p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="rounded-full bg-primary-100 dark:bg-primary-900/40 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">Submit assignment</span>
                        <span v-if="a.allow_late" class="text-xs text-subtle">Late submissions allowed</span>
                    </div>
                </a>
            </div>

            <!-- Submitted -->
            <div v-if="tab === 'submitted'" class="space-y-3">
                <div v-if="!submitted.length" class="rounded-xl border border-base dark:border-surface-800 bg-surface p-10 text-center text-sm text-subtle">
                    No submitted assignments yet.
                </div>
                <a v-for="a in submitted" :key="a.id" :href="route('student.assignments.show', a.id)"
                    class="block rounded-xl border border-base dark:border-surface-800 bg-surface p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-default dark:text-default truncate">{{ a.title }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ a.course }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span v-if="a.graded" class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ a.grade }}<span class="text-xs font-normal text-subtle">/{{ a.max_marks }}</span></span>
                            <span v-else class="text-xs text-amber-600 dark:text-amber-400 font-medium">Awaiting grade</span>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <span v-if="a.graded" class="rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">Graded</span>
                        <span v-else class="rounded-full bg-surface-3 dark:bg-surface-2 px-2.5 py-0.5 text-xs text-muted dark:text-muted">Submitted</span>
                    </div>
                </a>
            </div>

            <!-- Missed -->
            <div v-if="tab === 'missed'" class="space-y-3">
                <div v-if="!missed.length" class="rounded-xl border border-base dark:border-surface-800 bg-surface p-10 text-center text-sm text-subtle">
                    No missed assignments.
                </div>
                <div v-for="a in missed" :key="a.id"
                    class="rounded-xl border border-red-100 dark:border-red-900/40 bg-surface p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-default dark:text-default truncate">{{ a.title }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ a.course }}</p>
                        </div>
                        <span class="rounded-full bg-red-100 dark:bg-red-900/40 px-2.5 py-0.5 text-xs font-medium text-red-600 dark:text-red-400 shrink-0">Missed</span>
                    </div>
                    <div class="mt-2">
                        <span v-if="a.allow_late">
                            <a :href="route('student.assignments.show', a.id)" class="text-xs text-primary-600 hover:underline">Late submission still accepted →</a>
                        </span>
                        <p v-else class="text-xs text-subtle">Due {{ a.due_date ? new Date(a.due_date).toLocaleDateString() : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
