<script setup>
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler } from 'chart.js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ReportFilterBar from '@/Components/ReportFilterBar.vue'

ChartJS.register(LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler)

const props = defineProps({
    byDay:   Array,
    totals:  Object,
    filters: Object,
})

const lineData = computed(() => ({
    labels: props.byDay.map(r => r.day),
    datasets: [
        {
            label: 'Tokens Used',
            data: props.byDay.map(r => r.tokens),
            borderColor: 'rgba(139,92,246,1)',
            backgroundColor: 'rgba(139,92,246,0.08)',
            fill: true, tension: 0.4, yAxisID: 'y',
            pointBackgroundColor: 'rgba(139,92,246,1)', pointRadius: 3,
        },
        {
            label: 'Cost (USD)',
            data: props.byDay.map(r => Number(r.cost || 0).toFixed(4)),
            borderColor: 'rgba(245,158,11,1)',
            backgroundColor: 'transparent',
            fill: false, tension: 0.4, yAxisID: 'y1',
            pointBackgroundColor: 'rgba(245,158,11,1)', pointRadius: 3,
        },
    ],
}))

const lineOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'top' } },
    scales: {
        y:  { beginAtZero: true, position: 'left' },
        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
    },
}
</script>

<template>
    <AdminLayout title="AI Usage">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">AI Usage Report</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Tokens consumed and estimated cost</p>
            </div>

            <ReportFilterBar :filters="filters" route-name="admin.reports.ai" />

            <!-- Stat tiles -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-5 text-center shadow-sm">
                    <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ (totals?.tokens ?? 0).toLocaleString() }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Total Tokens</p>
                </div>
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-5 text-center shadow-sm">
                    <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">${{ Number(totals?.cost ?? 0).toFixed(4) }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Est. Cost (USD)</p>
                </div>
                <div class="rounded-2xl border border-base dark:border-base bg-surface p-5 text-center shadow-sm">
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ totals?.conversations ?? 0 }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-1 font-medium uppercase tracking-wide">Conversations</p>
                </div>
            </div>

            <!-- Line chart -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface p-6">
                <h2 class="text-sm font-semibold text-muted dark:text-muted mb-4">Daily Tokens &amp; Cost</h2>
                <div class="h-64">
                    <Line v-if="byDay.length" :data="lineData" :options="lineOptions" />
                    <div v-else class="h-full flex items-center justify-center text-subtle text-sm">No AI usage data for this period</div>
                </div>
            </div>

            <!-- Daily breakdown table -->
            <div class="rounded-2xl border border-base dark:border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-base dark:border-base">
                        <tr class="text-left text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3 text-right">Conversations</th>
                            <th class="px-4 py-3 text-right">Tokens Used</th>
                            <th class="px-4 py-3 text-right">Est. Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!byDay.length">
                            <td colspan="4" class="px-4 py-10 text-center text-subtle">No AI usage in this period</td>
                        </tr>
                        <tr v-for="r in byDay" :key="r.day" class="hover:bg-surface-2 dark:hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-default dark:text-default">{{ r.day }}</td>
                            <td class="px-4 py-3 text-right">{{ r.conversations }}</td>
                            <td class="px-4 py-3 text-right font-mono text-violet-600 dark:text-violet-400">{{ Number(r.tokens).toLocaleString() }}</td>
                            <td class="px-4 py-3 text-right font-mono text-amber-600 dark:text-amber-400">${{ Number(r.cost || 0).toFixed(4) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
