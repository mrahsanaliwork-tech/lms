<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const reports = [
    {
        title: 'Student Performance',
        description: 'Progress, completion rates and activity per student',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        route: 'admin.reports.students',
        color: 'from-primary-500 to-violet-600',
        badge: 'Students',
    },
    {
        title: 'Revenue',
        description: 'Payment history, monthly income and discount analysis',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        route: 'admin.reports.revenue',
        color: 'from-emerald-500 to-teal-600',
        badge: 'Revenue',
    },
    {
        title: 'Course Analytics',
        description: 'Enrollment trends and completion distribution by course',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        route: 'admin.reports.courses',
        color: 'from-amber-400 to-orange-500',
        badge: 'Courses',
    },
    {
        title: 'Quiz Analytics',
        description: 'Pass rates, average scores and attempt counts per quiz',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        route: 'admin.reports.quiz',
        color: 'from-sky-500 to-blue-600',
        badge: 'Quizzes',
    },
    {
        title: 'AI Usage',
        description: 'Token consumption and cost estimates for AI Tutor usage',
        icon: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.698-1.4 2.698H4.198c-1.43 0-2.4-1.698-1.4-2.698L4.2 15.3',
        route: 'admin.reports.ai',
        color: 'from-violet-500 to-purple-700',
        badge: 'AI',
    },
]
</script>

<template>
    <AdminLayout title="Reports">
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Reports &amp; Analytics</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Choose a report to drill into — all support date filters, CSV and PDF export</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <Link v-for="r in reports" :key="r.route"
                    :href="route(r.route)"
                    class="group flex items-start gap-4 rounded-2xl border border-base dark:border-base bg-surface p-6 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition">

                    <div :class="['flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center', r.color]">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="r.icon"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="font-semibold text-default dark:text-default group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ r.title }}</h2>
                            <span class="rounded-full bg-surface-3 dark:bg-surface-2 px-2 py-0.5 text-xs font-medium text-muted dark:text-muted">{{ r.badge }}</span>
                        </div>
                        <p class="text-sm text-muted dark:text-muted leading-snug">{{ r.description }}</p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-subtle dark:text-subtle">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                CSV export
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                PDF export
                            </span>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-subtle dark:text-muted group-hover:text-primary-500 transition-colors mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
