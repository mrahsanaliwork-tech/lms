<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>

<template>
    <AdminLayout title="Org Students">
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-semibold text-default">Students</h1>
                <p class="text-sm text-muted mt-0.5">Students enrolled in your organization</p>
            </div>
            <div class="rounded-xl border border-base bg-surface p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-muted mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <p class="text-sm font-medium text-default">No students yet</p>
                <p class="text-xs text-muted mt-1">Students in your organization will appear here.</p>
            </div>
        </div>
    </AdminLayout>
</template>
