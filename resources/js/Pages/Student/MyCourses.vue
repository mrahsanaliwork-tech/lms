<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({ courses: Array })

const filter = ref('all') // all | in_progress | completed

const filtered = computed(() => {
    if (filter.value === 'in_progress') return props.courses.filter(c => c.status !== 'completed' && c.progress > 0)
    if (filter.value === 'completed')   return props.courses.filter(c => c.status === 'completed')
    return props.courses
})

const filters = [
    { key: 'all',         label: 'All Courses' },
    { key: 'in_progress', label: 'In Progress' },
    { key: 'completed',   label: 'Completed' },
]

function levelColor(level) {
    const map = {
        beginner:     'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
        intermediate: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
        advanced:     'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    }
    return map[level] ?? 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted'
}
</script>

<template>
    <StudentLayout title="My Courses">
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">My Courses</h1>
                    <p class="text-sm text-muted dark:text-muted mt-0.5">{{ courses.length }} enrolled course{{ courses.length !== 1 ? 's' : '' }}</p>
                </div>
                <!-- Filter tabs -->
                <div class="flex gap-1 rounded-xl bg-surface-3 dark:bg-surface p-1">
                    <button v-for="f in filters" :key="f.key"
                        @click="filter = f.key"
                        :class="['rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                            filter === f.key
                                ? 'bg-surface-2 text-default dark:text-default shadow-sm'
                                : 'text-muted dark:text-muted hover:text-muted dark:hover:text-subtle']">
                        {{ f.label }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!filtered.length"
                class="rounded-2xl border border-dashed border-base dark:border-base p-16 text-center">
                <svg class="mx-auto w-14 h-14 text-subtle dark:text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <p class="text-muted dark:text-muted text-sm">No {{ filter === 'all' ? '' : filter.replace('_', ' ') + ' ' }}courses found.</p>
            </div>

            <!-- Course grid -->
            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="c in filtered" :key="c.enrollment_id"
                    class="group rounded-2xl border border-base dark:border-base bg-surface overflow-hidden hover:shadow-md transition-shadow flex flex-col">

                    <!-- Thumbnail -->
                    <div class="relative h-40 bg-gradient-to-br from-primary-50 to-violet-50 dark:from-primary-900/20 dark:to-violet-900/20 overflow-hidden">
                        <img v-if="c.thumbnail" :src="c.thumbnail" :alt="c.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div v-else class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-14 h-14 text-primary-200 dark:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        </div>
                        <!-- Progress overlay badge -->
                        <div class="absolute bottom-2 right-2 rounded-full bg-black/50 backdrop-blur px-2.5 py-0.5 text-xs font-semibold text-white">
                            {{ c.progress }}%
                        </div>
                        <!-- Completed checkmark -->
                        <div v-if="c.status === 'completed'" class="absolute top-2 left-2 rounded-full bg-emerald-500 p-1.5 shadow">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <!-- Tags -->
                        <div class="flex gap-1.5 mb-2 flex-wrap">
                            <span v-if="c.level" :class="['rounded-full px-2 py-0.5 text-xs font-medium capitalize', levelColor(c.level)]">
                                {{ c.level }}
                            </span>
                            <span v-if="c.language" class="rounded-full bg-surface-3 dark:bg-surface-2 px-2 py-0.5 text-xs font-medium text-muted dark:text-muted capitalize">
                                {{ c.language }}
                            </span>
                        </div>

                        <h3 class="font-semibold text-default dark:text-default leading-snug mb-1 line-clamp-2">{{ c.title }}</h3>
                        <p v-if="c.instructor" class="text-xs text-muted dark:text-muted mb-3">By {{ c.instructor }}</p>

                        <!-- Progress bar -->
                        <div class="mb-4">
                            <div class="flex justify-between text-xs text-muted dark:text-muted mb-1.5">
                                <span>{{ c.progress === 100 ? 'Completed!' : 'Progress' }}</span>
                                <span>{{ c.progress }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-surface-3 dark:bg-surface-2 overflow-hidden">
                                <div :class="['h-2 rounded-full transition-all', c.progress === 100 ? 'bg-emerald-500' : 'bg-primary-500']"
                                    :style="{ width: c.progress + '%' }" />
                            </div>
                        </div>

                        <div class="mt-auto flex gap-2">
                            <Link :href="route('student.courses.learn', c.course_id)"
                                :class="['flex-1 rounded-lg py-2.5 text-center text-sm font-semibold transition-colors',
                                    c.status === 'completed'
                                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                        : 'bg-primary-600 hover:bg-primary-700 text-white']">
                                {{ c.status === 'completed' ? 'Review' : c.progress > 0 ? 'Continue' : 'Start' }}
                            </Link>
                        </div>

                        <p class="text-xs text-subtle dark:text-subtle mt-2 text-center">Enrolled {{ c.enrolled_at }}</p>
                    </div>
                </div>
            </div>

        </div>
    </StudentLayout>
</template>
