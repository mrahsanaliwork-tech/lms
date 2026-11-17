<template>
    <div class="min-h-screen bg-surface-2 flex">
        <!-- Left brand panel -->
        <div class="hidden lg:flex lg:w-[420px] xl:w-[480px] bg-primary-600 flex-col justify-between p-10 shrink-0 relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-white/5" />
            <div class="absolute bottom-10 -left-16 w-64 h-64 rounded-full bg-white/5" />
            <div class="absolute top-1/2 right-10 w-32 h-32 rounded-full bg-white/10" />

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <span class="font-display font-bold text-white text-lg">LMS Platform</span>
                </div>

                <h2 class="text-3xl font-display font-bold text-white leading-snug text-balance mb-4">
                    Learn, teach,<br>and grow together.
                </h2>
                <p class="text-primary-200 text-sm leading-relaxed max-w-xs">
                    A modern learning management system built for institutions, instructors, and students.
                </p>
            </div>

            <!-- Feature list -->
            <div class="relative z-10 space-y-4">
                <div v-for="f in features" :key="f" class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <span class="text-sm text-primary-100">{{ f }}</span>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-sm">
                <!-- Mobile logo -->
                <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="font-display font-bold text-default">LMS Platform</span>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
const features = [
    'Role-based access for admins, instructors & students',
    'Live quizzes, assignments & progress tracking',
    'AI-powered learning assistant',
    'Real-time notifications via Reverb',
];
</script>
