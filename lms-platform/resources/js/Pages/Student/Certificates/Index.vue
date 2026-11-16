<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'

defineProps({ certificates: Array })
</script>

<template>
    <StudentLayout title="My Certificates">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">My Certificates</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Download and share your earned certificates</p>
            </div>

            <!-- Empty state -->
            <div v-if="!certificates.length"
                class="rounded-2xl border border-dashed border-base dark:border-base bg-surface p-16 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-default dark:text-default mb-1">No certificates yet</h2>
                <p class="text-sm text-muted dark:text-muted">Complete a course to earn your first certificate.</p>
                <a :href="route('student.courses.index')"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
                    Browse Courses
                </a>
            </div>

            <!-- Certificate cards -->
            <div v-else class="grid gap-5 sm:grid-cols-2">
                <div v-for="cert in certificates" :key="cert.id"
                    class="group relative rounded-2xl border border-base dark:border-base bg-surface overflow-hidden hover:shadow-lg transition-shadow">

                    <!-- Gold top bar -->
                    <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500"></div>

                    <!-- Course thumbnail or placeholder -->
                    <div class="relative h-32 overflow-hidden bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20">
                        <img v-if="cert.course_thumbnail" :src="cert.course_thumbnail" :alt="cert.course_title"
                            class="w-full h-full object-cover opacity-60" />
                        <div v-else class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-amber-300 dark:text-amber-600" fill="none" stroke="currentColor" stroke-width="0.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                            </svg>
                        </div>
                        <!-- Verified badge -->
                        <div class="absolute top-3 right-3 flex items-center gap-1.5 rounded-full bg-white/90 dark:bg-surface/90 px-2.5 py-1 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Verified</span>
                        </div>
                    </div>

                    <!-- Card content -->
                    <div class="p-5">
                        <h3 class="font-semibold text-default dark:text-default leading-tight mb-1">
                            {{ cert.course_title }}
                        </h3>
                        <p class="text-xs text-muted dark:text-muted mb-1">Issued: {{ cert.issued_at }}</p>
                        <p class="text-xs font-mono text-subtle dark:text-subtle mb-4">{{ cert.certificate_number }}</p>

                        <div class="flex items-center gap-2">
                            <a :href="cert.download_url"
                                :class="['flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-medium transition-colors', cert.has_file ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-surface-3 dark:bg-surface-2 text-muted cursor-not-allowed']"
                                :download="cert.has_file ? undefined : false">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download PDF
                            </a>
                            <a :href="cert.verify_url" target="_blank"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-base dark:border-base px-3 py-2 text-sm text-muted dark:text-muted hover:bg-surface-2 dark:hover:bg-surface-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Verify
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </StudentLayout>
</template>
