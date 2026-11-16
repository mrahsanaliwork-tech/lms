<script setup>
import { Link } from '@inertiajs/vue3'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'

const props = defineProps({
    stats:              { type: Object, default: () => ({}) },
    courses:            { type: Array,  default: () => [] },
    recentEnrollments:  { type: Array,  default: () => [] },
})

const statusColors = {
    published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    pending:   'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    draft:     'bg-surface-3 text-muted dark:bg-surface-2 dark:text-muted',
    approved:  'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    rejected:  'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
}
</script>

<template>
    <InstructorLayout title="Dashboard">
        <div class="space-y-6">

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface rounded-xl border border-base p-4 flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                    </div>
                    <p class="text-2xl font-bold text-default tabular-nums">{{ stats.totalCourses ?? 0 }}</p>
                    <p class="text-[11px] font-medium text-muted uppercase">Total Courses</p>
                </div>
                <div class="bg-surface rounded-xl border border-base p-4 flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    </div>
                    <p class="text-2xl font-bold text-default tabular-nums">{{ stats.totalStudents ?? 0 }}</p>
                    <p class="text-[11px] font-medium text-muted uppercase">Total Students</p>
                </div>
                <div class="bg-surface rounded-xl border border-base p-4 flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                    </div>
                    <p class="text-2xl font-bold text-default tabular-nums">{{ stats.pendingGrades ?? 0 }}</p>
                    <p class="text-[11px] font-medium text-muted uppercase">Pending Grades</p>
                </div>
                <div class="bg-surface rounded-xl border border-base p-4 flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                    </div>
                    <p class="text-2xl font-bold text-default tabular-nums">{{ stats.publishedCount ?? 0 }}</p>
                    <p class="text-[11px] font-medium text-muted uppercase">Published</p>
                </div>
            </div>

            <!-- My Courses + Recent Enrollments -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

                <!-- My Courses -->
                <div class="bg-surface rounded-xl border border-base overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-base">
                        <h2 class="text-sm font-semibold text-default">My Courses</h2>
                        <Link :href="route('instructor.courses.index')" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">View all</Link>
                    </div>
                    <div v-if="!courses.length" class="py-10 text-center text-sm text-muted">
                        No courses yet.
                        <Link :href="route('instructor.courses.create')" class="block mt-2 text-primary-600 dark:text-primary-400 hover:underline">Create your first course →</Link>
                    </div>
                    <div v-else class="divide-y divide-surface-100 dark:divide-surface-800">
                        <div v-for="c in courses" :key="c.id" class="px-5 py-3 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default truncate">{{ c.title }}</p>
                                <p class="text-xs text-muted mt-0.5">{{ c.enrollments }} students · {{ c.lessons }} lessons</p>
                            </div>
                            <span :class="['text-xs font-medium rounded-full px-2.5 py-0.5 capitalize', statusColors[c.status] ?? statusColors.draft]">
                                {{ c.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Enrollments -->
                <div class="bg-surface rounded-xl border border-base overflow-hidden">
                    <div class="px-5 py-4 border-b border-base">
                        <h2 class="text-sm font-semibold text-default">Recent Enrollments</h2>
                    </div>
                    <div v-if="!recentEnrollments.length" class="py-10 text-center text-sm text-muted">
                        No enrollments yet.
                    </div>
                    <div v-else class="divide-y divide-surface-100 dark:divide-surface-800">
                        <div v-for="(e, i) in recentEnrollments" :key="i" class="px-5 py-3 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-xs font-bold text-primary-700 dark:text-primary-300 shrink-0">
                                {{ e.student?.[0]?.toUpperCase() ?? '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default truncate">{{ e.student }}</p>
                                <p class="text-xs text-muted truncate">{{ e.course }}</p>
                            </div>
                            <span class="text-xs text-subtle shrink-0">{{ e.date }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </InstructorLayout>
</template>
