<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page  = usePage()
const user  = computed(() => page.props.auth?.user)
const roles = computed(() => page.props.auth?.roles ?? [])

function dashboardRoute() {
    if (roles.value.includes('SuperAdmin') || roles.value.includes('Admin')) return route('admin.dashboard')
    if (roles.value.includes('Instructor')) return route('instructor.dashboard')
    if (roles.value.includes('Student')) return route('student.dashboard')
    return route('login')
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-indigo-50 dark:from-surface-950 dark:via-surface-900 dark:to-surface-950 flex flex-col">

        <!-- Navbar -->
        <header class="w-full border-b border-base bg-surface/80 backdrop-blur-sm sticky top-0 z-10">
            <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <span class="font-semibold text-default font-display">LMS Platform</span>
                </div>
                <div class="flex items-center gap-3">
                    <template v-if="user">
                        <span class="text-sm text-muted hidden sm:block">{{ user.name }}</span>
                        <Link :href="dashboardRoute()" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                            Go to Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-medium text-muted hover:text-default transition-colors">Login</Link>
                        <Link :href="route('register')" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex-1 flex items-center justify-center px-6 py-20">
            <div class="max-w-3xl mx-auto text-center space-y-8">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 px-4 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                    Laravel + Vue 3 + Inertia.js + Tailwind CSS v4
                </div>

                <h1 class="text-5xl sm:text-6xl font-bold font-display text-default leading-tight">
                    Learn anything,<br>
                    <span class="text-primary-600 dark:text-primary-400">anywhere.</span>
                </h1>

                <p class="text-lg text-muted max-w-xl mx-auto">
                    A complete Learning Management System for students, instructors, and administrators.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <template v-if="user">
                        <Link :href="dashboardRoute()" class="rounded-xl bg-primary-600 px-8 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                            Go to Dashboard →
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('register')" class="rounded-xl bg-primary-600 px-8 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                            Start Learning Free →
                        </Link>
                        <Link :href="route('login')" class="rounded-xl border border-base bg-surface px-8 py-3 text-sm font-semibold text-default hover:bg-surface-2 transition-colors">
                            Login
                        </Link>
                    </template>
                </div>

                <!-- Feature tiles -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-12 text-left">
                    <div class="bg-surface rounded-xl border border-base p-5">
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <p class="text-sm font-semibold text-default">Rich Courses</p>
                        <p class="text-xs text-muted mt-1">Video lessons, PDFs, quizzes and assignments in one place.</p>
                    </div>
                    <div class="bg-surface rounded-xl border border-base p-5">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                        </div>
                        <p class="text-sm font-semibold text-default">AI Tutor</p>
                        <p class="text-xs text-muted mt-1">Get instant help from an AI tutor, any time of day.</p>
                    </div>
                    <div class="bg-surface rounded-xl border border-base p-5">
                        <div class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                        </div>
                        <p class="text-sm font-semibold text-default">Certificates</p>
                        <p class="text-xs text-muted mt-1">Earn verified certificates on course completion.</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="text-center py-6 text-xs text-subtle border-t border-base">
            © {{ new Date().getFullYear() }} LMS Platform. All rights reserved.
        </footer>
    </div>
</template>
