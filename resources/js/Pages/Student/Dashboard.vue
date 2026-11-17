<script setup>
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
    stats:               Object,
    courses:             Array,
    continueLesson:      Object,
    upcomingAssignments: Array,
    upcomingQuizzes:     Array,
    recentQuizResults:   Array,
    certificates:        Array,
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

function greeting() {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 17) return 'Good afternoon'
    return 'Good evening'
}

function daysUntil(isoDate) {
    const diff = new Date(isoDate) - new Date()
    const days = Math.ceil(diff / 86400000)
    if (days === 0) return 'Due today'
    if (days === 1) return 'Due tomorrow'
    return `Due in ${days} days`
}

function dueUrgency(isoDate) {
    const days = Math.ceil((new Date(isoDate) - new Date()) / 86400000)
    if (days <= 1) return 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20'
    if (days <= 3) return 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20'
    return 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
}

function scoreColor(passed) {
    return passed
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-red-500 dark:text-red-400'
}

function timeAgo(isoDate) {
    if (!isoDate) return ''
    const diff = Date.now() - new Date(isoDate)
    const mins = Math.floor(diff / 60000)
    if (mins < 60) return `${mins}m ago`
    const hrs = Math.floor(mins / 60)
    if (hrs < 24) return `${hrs}h ago`
    return `${Math.floor(hrs / 24)}d ago`
}
</script>

<template>
    <StudentLayout title="Dashboard">
        <div class="max-w-6xl mx-auto space-y-8">

            <!-- Welcome banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 via-primary-500 to-violet-500 px-8 py-7 text-white shadow-lg">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-2xl" />
                <div class="absolute -bottom-8 right-32 h-32 w-32 rounded-full bg-white/10 blur-xl" />
                <p class="text-primary-100 text-sm font-medium mb-1">{{ greeting() }},</p>
                <h1 class="text-2xl font-bold">{{ user?.name ?? 'Student' }} 👋</h1>
                <p class="mt-1 text-primary-100 text-sm">
                    You've completed <strong class="text-white">{{ stats.avg_progress }}%</strong> of your learning journey. Keep it up!
                </p>
                <div v-if="continueLesson" class="mt-5">
                    <Link :href="route('student.courses.learn', continueLesson.course_id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-semibold backdrop-blur transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                        Continue: {{ continueLesson.lesson_title }}
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-surface border border-base dark:border-base p-5 text-center shadow-sm">
                    <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ stats.total_courses }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Courses</p>
                </div>
                <div class="rounded-2xl bg-surface border border-base dark:border-base p-5 text-center shadow-sm">
                    <p class="text-3xl font-bold text-violet-600 dark:text-violet-400">{{ stats.avg_progress }}%</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Avg Progress</p>
                </div>
                <div class="rounded-2xl bg-surface border border-base dark:border-base p-5 text-center shadow-sm">
                    <p class="text-3xl font-bold text-amber-500 dark:text-amber-400">{{ stats.total_assignments }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Due Soon</p>
                </div>
                <div class="rounded-2xl bg-surface border border-base dark:border-base p-5 text-center shadow-sm">
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.total_certificates }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Certificates</p>
                </div>
            </div>

            <!-- Continue Learning -->
            <section v-if="courses.length">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-default dark:text-default">Continue Learning</h2>
                    <Link :href="route('student.courses.index')" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View all</Link>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="c in courses.slice(0, 3)" :key="c.enrollment_id"
                        class="group rounded-2xl border border-base dark:border-base bg-surface overflow-hidden hover:shadow-md transition-shadow">
                        <div class="relative h-32 bg-gradient-to-br from-primary-50 to-violet-50 dark:from-primary-900/20 dark:to-violet-900/20">
                            <img v-if="c.thumbnail" :src="c.thumbnail" :alt="c.title" class="w-full h-full object-cover" />
                            <div v-else class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-12 h-12 text-primary-200 dark:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            </div>
                            <span v-if="c.status === 'completed'" class="absolute top-2 right-2 rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-medium text-white">Done</span>
                        </div>
                        <div class="p-4">
                            <p class="font-semibold text-default dark:text-default leading-tight line-clamp-2 mb-3">{{ c.title }}</p>
                            <div class="mb-3">
                                <div class="flex justify-between text-xs text-muted dark:text-muted mb-1">
                                    <span>Progress</span>
                                    <span>{{ c.progress }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-surface-3 dark:bg-surface-2">
                                    <div class="h-1.5 rounded-full bg-primary-500 transition-all"
                                        :style="{ width: c.progress + '%' }" />
                                </div>
                            </div>
                            <Link :href="route('student.courses.learn', c.course_id)"
                                class="block w-full rounded-lg bg-primary-600 py-2 text-center text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                                {{ c.progress > 0 ? 'Continue' : 'Start' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Two-column: Deadlines + Recent Quiz Results -->
            <div class="grid gap-6 lg:grid-cols-2">

                <!-- Upcoming deadlines -->
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                    <h2 class="text-base font-semibold text-default dark:text-default mb-4">Upcoming Deadlines</h2>
                    <div v-if="!upcomingAssignments.length && !upcomingQuizzes.length"
                        class="py-8 text-center text-sm text-subtle">
                        No deadlines in the next 7 days 🎉
                    </div>
                    <ul v-else class="space-y-3">
                        <li v-for="a in upcomingAssignments" :key="'a'+a.id"
                            class="flex items-start gap-3">
                            <span class="mt-0.5 flex-shrink-0 rounded-lg bg-amber-100 dark:bg-amber-900/30 p-1.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default dark:text-default truncate">{{ a.title }}</p>
                                <p class="text-xs text-muted dark:text-muted">{{ a.course_title }}</p>
                            </div>
                            <span :class="['shrink-0 rounded-full px-2 py-0.5 text-xs font-medium', dueUrgency(a.due_date)]">
                                {{ daysUntil(a.due_date) }}
                            </span>
                        </li>
                        <li v-for="q in upcomingQuizzes" :key="'q'+q.id"
                            class="flex items-start gap-3">
                            <span class="mt-0.5 flex-shrink-0 rounded-lg bg-violet-100 dark:bg-violet-900/30 p-1.5">
                                <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default dark:text-default truncate">{{ q.title }}</p>
                                <p class="text-xs text-muted dark:text-muted">{{ q.course_title }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-violet-50 dark:bg-violet-900/20 px-2 py-0.5 text-xs font-medium text-violet-600 dark:text-violet-400">
                                Quiz
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Recent quiz results -->
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                    <h2 class="text-base font-semibold text-default dark:text-default mb-4">Recent Quiz Results</h2>
                    <div v-if="!recentQuizResults.length"
                        class="py-8 text-center text-sm text-subtle">
                        No quiz attempts yet
                    </div>
                    <ul v-else class="space-y-3">
                        <li v-for="r in recentQuizResults" :key="r.id"
                            class="flex items-center gap-3">
                            <div :class="['flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold',
                                r.passed ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                         : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400']">
                                {{ r.passed ? '✓' : '✗' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default dark:text-default truncate">{{ r.quiz_title }}</p>
                                <p class="text-xs text-muted dark:text-muted">{{ r.course_title }} · {{ timeAgo(r.completed_at) }}</p>
                            </div>
                            <span :class="['text-sm font-semibold', scoreColor(r.passed)]">
                                {{ r.score }}/{{ r.total_marks }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Certificates -->
            <section v-if="certificates.length">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-default dark:text-default">Recent Certificates</h2>
                    <Link :href="route('student.certificates.index')" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View all</Link>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div v-for="c in certificates" :key="c.id"
                        class="flex items-center gap-3 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10 p-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-default dark:text-default truncate">{{ c.course_title }}</p>
                            <p class="text-xs text-muted dark:text-muted">{{ c.issued_at }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick links -->
            <div class="grid gap-4 sm:grid-cols-2">
                <Link :href="route('ai.tutor')"
                    class="group flex items-center gap-4 rounded-2xl border border-base dark:border-base bg-surface p-5 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm transition">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.698-1.4 2.698H4.198c-1.43 0-2.4-1.698-1.4-2.698L4.2 15.3"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-default dark:text-default group-hover:text-primary-600 dark:group-hover:text-primary-400">AI Tutor</p>
                        <p class="text-sm text-muted dark:text-muted">Get instant help on anything</p>
                    </div>
                </Link>
                <Link :href="route('student.courses.index')"
                    class="group flex items-center gap-4 rounded-2xl border border-base dark:border-base bg-surface p-5 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm transition">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-default dark:text-default group-hover:text-primary-600 dark:group-hover:text-primary-400">My Courses</p>
                        <p class="text-sm text-muted dark:text-muted">{{ stats.total_courses }} enrolled courses</p>
                    </div>
                </Link>
            </div>

        </div>
    </StudentLayout>
</template>
