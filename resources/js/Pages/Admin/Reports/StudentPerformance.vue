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
    courses:    Array,
    students:   Array,
})

const chartData = computed(() => ({
    labels: props.rows.slice(0, 15).map(r => r.name.split(' ')[0]),
    datasets: [
        {
            label: 'Avg Progress %',
            data: props.rows.slice(0, 15).map(r => r.avg_progress),
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 6,
        },
        {
            label: 'Completed Courses',
            data: props.rows.slice(0, 15).map(r => r.completed),
            backgroundColor: 'rgba(16,185,129,0.7)',
            borderRadius: 6,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: true, max: 100 } },
}
</script>

<template>
    <AdminLayout title="Student Performance">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Student Performance</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Progress, completion and activity per student</p>
            </div>

            <!-- Filters -->
            <ReportFilterBar :filters="filters" route-name="admin.reports.students">
                <div>
                    <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Course</label>
                    <select v-model="filters.course_id"
                        class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">All courses</option>
                        <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>
                </div>
            </ReportFilterBar>

            <!-- Bar chart -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Top 15 Students — Progress vs Completions</h2>
                <div class="h-64">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-base dark:border-base">
                        <tr class="text-left text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3 text-right">Courses</th>
                            <th class="px-4 py-3 text-right">Completed</th>
                            <th class="px-4 py-3 text-right">Avg Progress</th>
                            <th class="px-4 py-3 text-right">Quizzes</th>
                            <th class="px-4 py-3 text-right">Submissions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-4 py-10 text-center text-subtle dark:text-subtle">No data for the selected filters</td>
                        </tr>
                        <tr v-for="r in rows" :key="r.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-default dark:text-default">{{ r.name }}</td>
                            <td class="px-4 py-3 text-muted dark:text-muted">{{ r.email }}</td>
                            <td class="px-4 py-3 text-right">{{ r.enrollments }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-medium">{{ r.completed }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 h-1.5 rounded-full bg-surface-3 dark:bg-surface-2 overflow-hidden">
                                        <div class="h-full rounded-full bg-primary-500" :style="{ width: r.avg_progress + '%' }" />
                                    </div>
                                    <span class="font-medium text-muted dark:text-muted">{{ r.avg_progress }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ r.quiz_attempts }}</td>
                            <td class="px-4 py-3 text-right">{{ r.submissions }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-surface-100 dark:border-base flex items-center justify-between text-sm text-muted dark:text-muted">
                    <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                    <div class="flex gap-1">
                        <Link v-if="pagination.prev_page_url" :href="pagination.prev_page_url"
                            class="rounded px-3 py-1 hover:bg-surface-3 dark:hover:bg-surface-700">Prev</Link>
                        <Link v-if="pagination.next_page_url" :href="pagination.next_page_url"
                            class="rounded px-3 py-1 hover:bg-surface-3 dark:hover:bg-surface-700">Next</Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
