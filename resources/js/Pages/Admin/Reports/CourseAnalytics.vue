<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Line, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS, LineElement, PointElement, ArcElement,
    CategoryScale, LinearScale, Tooltip, Legend, Filler,
} from 'chart.js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ReportFilterBar from '@/Components/ReportFilterBar.vue'

ChartJS.register(LineElement, PointElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend, Filler)

const props = defineProps({
    rows:                   Array,
    pagination:             Object,
    enrollmentTrend:        Array,
    completionDistribution: Array,
    filters:                Object,
})

const lineData = computed(() => ({
    labels: props.enrollmentTrend.map(r => r.month),
    datasets: [{
        label: 'Enrollments',
        data: props.enrollmentTrend.map(r => r.enrollments),
        borderColor: 'rgba(99,102,241,1)',
        backgroundColor: 'rgba(99,102,241,0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: 'rgba(99,102,241,1)',
        pointRadius: 4,
    }],
}))

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } },
}

const doughnutData = computed(() => ({
    labels: props.completionDistribution.map(d => d.label),
    datasets: [{
        data: props.completionDistribution.map(d => d.value),
        backgroundColor: [
            'rgba(239,68,68,0.8)',
            'rgba(245,158,11,0.8)',
            'rgba(16,185,129,0.8)',
        ],
        borderWidth: 2,
        borderColor: ['#ef4444','#f59e0b','#10b981'],
    }],
}))

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } },
    },
    cutout: '65%',
}
</script>

<template>
    <AdminLayout title="Course Analytics">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Course Analytics</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Enrollment trends and completion rates</p>
            </div>

            <ReportFilterBar :filters="filters" route-name="admin.reports.courses" />

            <!-- Charts -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Enrollment line chart (2/3 width) -->
                <div class="lg:col-span-2 rounded-2xl border border-base dark:border-base bg-surface p-6">
                    <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Enrollment Trend</h2>
                    <div class="h-56">
                        <Line v-if="enrollmentTrend.length" :data="lineData" :options="lineOptions" />
                        <div v-else class="h-full flex items-center justify-center text-subtle dark:text-subtle text-sm">No enrollment data for this period</div>
                    </div>
                </div>

                <!-- Completion doughnut (1/3 width) -->
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                    <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Completion Distribution</h2>
                    <div class="h-56">
                        <Doughnut :data="doughnutData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- Course table -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-base dark:border-base">
                        <tr class="text-left text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3">Instructor</th>
                            <th class="px-4 py-3 text-right">Enrollments</th>
                            <th class="px-4 py-3 text-right">Completed</th>
                            <th class="px-4 py-3 text-right">Completion Rate</th>
                            <th class="px-4 py-3 text-right">Avg Progress</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-4 py-10 text-center text-subtle dark:text-subtle">No courses found</td>
                        </tr>
                        <tr v-for="r in rows" :key="r.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-default dark:text-default max-w-xs">
                                <p class="truncate">{{ r.title }}</p>
                            </td>
                            <td class="px-4 py-3 text-muted dark:text-muted">{{ r.instructor }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ r.enrollments }}</td>
                            <td class="px-4 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ r.completed }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 h-1.5 rounded-full bg-surface-3 dark:bg-surface-2 overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                            :class="r.completion_rate >= 70 ? 'bg-emerald-500' : r.completion_rate >= 40 ? 'bg-amber-500' : 'bg-red-400'"
                                            :style="{ width: r.completion_rate + '%' }" />
                                    </div>
                                    <span class="font-semibold text-muted dark:text-muted w-9 text-right">{{ r.completion_rate }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-muted dark:text-muted">{{ r.avg_progress }}%</td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
                                    r.status === 'published' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                    : r.status === 'draft' ? 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted'
                                    : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400']">
                                    {{ r.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-surface-100 dark:border-base flex items-center justify-between text-sm text-muted dark:text-muted">
                    <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                    <div class="flex gap-1">
                        <Link v-if="pagination.prev_page_url" :href="pagination.prev_page_url" class="rounded px-3 py-1 hover:bg-surface-3 dark:hover:bg-surface-700">Prev</Link>
                        <Link v-if="pagination.next_page_url" :href="pagination.next_page_url" class="rounded px-3 py-1 hover:bg-surface-3 dark:hover:bg-surface-700">Next</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
