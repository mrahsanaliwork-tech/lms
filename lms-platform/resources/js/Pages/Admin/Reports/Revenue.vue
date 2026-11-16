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
    byMonth:    Array,
    totals:     Object,
    filters:    Object,
    courses:    Array,
})

const chartData = computed(() => ({
    labels: props.byMonth.map(r => r.month),
    datasets: [{
        label: 'Revenue',
        data: props.byMonth.map(r => Number(r.total)),
        backgroundColor: 'rgba(16,185,129,0.75)',
        borderColor: 'rgba(16,185,129,1)',
        borderWidth: 2,
        borderRadius: 6,
    }],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { callback: v => '$' + v.toLocaleString() },
        },
    },
}

function fmt(n) {
    return '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
    <AdminLayout title="Revenue Report">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Revenue Report</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Payment and income breakdown</p>
            </div>

            <!-- Filters -->
            <ReportFilterBar :filters="filters" route-name="admin.reports.revenue">
                <div>
                    <label class="block text-xs font-medium text-muted dark:text-muted mb-1">Course</label>
                    <select v-model="filters.course_id"
                        class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">All courses</option>
                        <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>
                </div>
            </ReportFilterBar>

            <!-- Stat tiles -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div v-for="tile in [
                    { label: 'Total Revenue',    value: fmt(totals.revenue),      color: 'text-emerald-600 dark:text-emerald-400' },
                    { label: 'Transactions',     value: totals.transactions,       color: 'text-primary-600 dark:text-primary-400' },
                    { label: 'Avg Order Value',  value: fmt(totals.avg_order),     color: 'text-violet-600 dark:text-violet-400' },
                    { label: 'Total Discounts',  value: fmt(totals.discounts),     color: 'text-amber-600 dark:text-amber-400' },
                ]" :key="tile.label"
                class="rounded-2xl border border-base dark:border-base bg-surface p-5 text-center shadow-sm">
                    <p :class="['text-2xl font-bold', tile.color]">{{ tile.value }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">{{ tile.label }}</p>
                </div>
            </div>

            <!-- Bar chart -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Monthly Revenue</h2>
                <div class="h-64">
                    <Bar v-if="byMonth.length" :data="chartData" :options="chartOptions" />
                    <div v-else class="h-full flex items-center justify-center text-subtle dark:text-subtle text-sm">No revenue data for this period</div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-base dark:border-base">
                        <tr class="text-left text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Discount</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Paid At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!rows.length">
                            <td colspan="6" class="px-4 py-10 text-center text-subtle dark:text-subtle">No payments in this period</td>
                        </tr>
                        <tr v-for="r in rows" :key="r.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-default dark:text-default">{{ r.student }}</td>
                            <td class="px-4 py-3 text-muted dark:text-muted max-w-xs truncate">{{ r.course }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ fmt(r.amount) }}</td>
                            <td class="px-4 py-3 text-right text-muted">{{ r.discount > 0 ? fmt(r.discount) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-surface-3 dark:bg-surface-2 px-2 py-0.5 text-xs font-medium capitalize">{{ r.method }}</span>
                            </td>
                            <td class="px-4 py-3 text-muted dark:text-muted">{{ r.paid_at }}</td>
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
