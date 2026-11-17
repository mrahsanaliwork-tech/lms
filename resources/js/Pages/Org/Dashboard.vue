<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>

<template>
    <AdminLayout title="Org Dashboard">
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-semibold text-default">Organization Dashboard</h1>
                <p class="text-sm text-muted mt-0.5">Overview of your organization's activity</p>
            </div>
            <div class="rounded-xl border border-base bg-surface p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-muted mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
                <p class="text-sm font-medium text-default">Analytics coming soon</p>
                <p class="text-xs text-muted mt-1">Organization analytics and reporting tools are being prepared.</p>
            </div>
        </div>
    </AdminLayout>
</template>
