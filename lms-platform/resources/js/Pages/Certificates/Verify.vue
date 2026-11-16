<script setup>
const props = defineProps({
    certificate: Object,  // null if not found
    cert_number: String,
})
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-amber-50 to-white dark:from-surface-950 dark:to-surface-900 flex flex-col items-center justify-center px-4 py-12"
        style="font-family: system-ui, sans-serif;">

        <!-- Header -->
        <div class="mb-10 text-center">
            <div class="inline-flex items-center gap-2 text-sm text-muted dark:text-muted mb-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Certificate Verification
            </div>
        </div>

        <!-- Valid certificate -->
        <div v-if="certificate"
            class="w-full max-w-lg rounded-3xl border-2 border-amber-300 dark:border-amber-700 bg-surface shadow-2xl overflow-hidden">

            <!-- Gold header -->
            <div class="bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 px-8 py-6 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/90 mb-3 shadow-sm">
                    <svg class="w-8 h-8 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-amber-900">Certificate Verified</h1>
                <p class="text-amber-800 text-sm mt-0.5">This is an authentic certificate</p>
            </div>

            <!-- Details -->
            <div class="p-8 space-y-5">
                <div class="text-center">
                    <p class="text-xs text-subtle uppercase tracking-widest mb-1">Awarded to</p>
                    <p class="text-2xl font-bold text-default dark:text-default">{{ certificate.student_name }}</p>
                </div>

                <div class="text-center">
                    <p class="text-xs text-subtle uppercase tracking-widest mb-1">For completing</p>
                    <p class="text-lg font-semibold text-default dark:text-default">{{ certificate.course_title }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-surface-100 dark:border-base">
                    <div class="text-center">
                        <p class="text-xs text-subtle uppercase tracking-widest mb-1">Date Issued</p>
                        <p class="text-sm font-semibold text-muted dark:text-default">{{ certificate.issued_at }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-subtle uppercase tracking-widest mb-1">Certificate No.</p>
                        <p class="text-sm font-mono font-semibold text-muted dark:text-default">{{ certificate.certificate_number }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300">
                        This certificate is <strong>authentic and valid</strong>. It was issued by our learning platform and cannot be forged.
                    </p>
                </div>
            </div>
        </div>

        <!-- Invalid certificate -->
        <div v-else class="w-full max-w-lg rounded-3xl border-2 border-red-200 dark:border-red-800 bg-surface shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-400 to-red-500 px-8 py-6 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/90 mb-3">
                    <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h1 class="text-xl font-bold text-white">Certificate Not Found</h1>
                <p class="text-red-100 text-sm mt-0.5">This certificate could not be verified</p>
            </div>
            <div class="p-8 space-y-4">
                <p class="text-sm text-muted dark:text-muted text-center">
                    No certificate matching <span class="font-mono font-semibold text-default dark:text-default">{{ cert_number }}</span> was found in our system.
                </p>
                <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        This certificate number is <strong>invalid</strong> or may have been entered incorrectly.
                    </p>
                </div>
            </div>
        </div>

        <p class="mt-8 text-xs text-subtle">Powered by LMS Platform · Certificate Verification System</p>
    </div>
</template>
