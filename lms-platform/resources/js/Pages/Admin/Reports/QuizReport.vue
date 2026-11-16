<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ReportFilterBar from '@/Components/ReportFilterBar.vue'

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
    rows:       Array,
    pagination: Object,
    filters:    Object,
})

const chartData = computed(() => ({
    labels: props.rows.slice(0, 12).map(r => r.title.substring(0, 20) + (r.title.length > 20 ? '…' : '')),
    datasets: [
        { label: 'Pass Rate %', data: props.rows.slice(0, 12).map(r => r.pass_rate), backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 4 },
        { label: 'Avg Score',   data: props.rows.slice(0, 12).map(r => r.avg_score), backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 4 },
    ],
}))
const chartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: true } },
}
</script>

<template>
    <AdminLayout title="Quiz Report">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Quiz Report</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Pass rates and average scores per quiz</p>
            </div>

            <ReportFilterBar :filters="filters" route-name="admin.reports.quiz" />

            <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Top 12 Quizzes — Pass Rate &amp; Avg Score</h2>
                <div class="h-64">
                    <Bar v-if="rows.length" :data="chartData" :options="chartOptions" />
                    <div v-else class="h-full flex items-center justify-center text-subtle text-sm">No quiz data</div>
                </div>
            </div>

            <div class="rounded-2xl border border-base dark:border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-base dark:border-base">
                        <tr class="text-left text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Quiz</th>
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3 text-right">Attempts</th>
                            <th class="px-4 py-3 text-right">Passed</th>
                            <th class="px-4 py-3 text-right">Pass Rate</th>
                            <th class="px-4 py-3 text-right">Avg Score</th>
                            <th class="px-4 py-3 text-right">Passing Marks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-4 py-10 text-center text-subtle">No quiz data for this period</td>
                        </tr>
                        <tr v-for="r in rows" :key="r.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-default dark:text-default">{{ r.title }}</td>
                            <td class="px-4 py-3 text-muted dark:text-muted">{{ r.course }}</td>
                            <td class="px-4 py-3 text-right">{{ r.attempts }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">{{ r.passed }}</td>
                            <td class="px-4 py-3 text-right">
                                <span :class="['font-semibold', r.pass_rate >= 70 ? 'text-emerald-600 dark:text-emerald-400' : r.pass_rate >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-red-500 dark:text-red-400']">
                                    {{ r.pass_rate }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-muted dark:text-muted">{{ r.avg_score }}</td>
                            <td class="px-4 py-3 text-right text-muted">{{ r.passing_marks }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-surface-100 dark:border-base flex items-center justify-between text-sm text-muted">
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
