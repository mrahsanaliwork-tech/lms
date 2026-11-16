<script setup>
import InstructorLayout from '@/Layouts/InstructorLayout.vue'
</script>

<template>
    <InstructorLayout title="Attendance">
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-semibold text-default">Attendance</h1>
                <p class="text-sm text-muted mt-0.5">Track student attendance for live sessions</p>
            </div>

            <div class="rounded-xl border border-base bg-surface p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-muted mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <p class="text-sm font-medium text-default">No live sessions yet</p>
                <p class="text-xs text-muted mt-1 max-w-xs mx-auto">Attendance records will appear here once you schedule and run live sessions with your students.</p>
            </div>
        </div>
    </InstructorLayout>
</template>
