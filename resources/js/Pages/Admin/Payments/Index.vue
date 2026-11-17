<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    payments: Object,
    stats:    Object,
})

const search = ref('')
const status = ref('')

let timer = null
watch([search, status], () => {
    clearTimeout(timer)
    timer = setTimeout(() => router.get(route('admin.payments.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true }), 400)
})

const statusClass = {
    completed: 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
    pending:   'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
    failed:    'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
    refunded:  'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted',
}
</script>

<template>
    <AdminLayout title="Payments">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Payments</h1>
                <p class="text-sm text-muted dark:text-muted">Transaction history across all students</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="item in [
                    { label: 'Total Revenue',    value: '$' + Number(stats.total_revenue).toFixed(2), color: 'text-emerald-600 dark:text-emerald-400' },
                    { label: 'Completed',         value: stats.total_count,    color: 'text-default dark:text-default' },
                    { label: 'Pending',           value: stats.pending_count,  color: 'text-amber-600 dark:text-amber-400' },
                    { label: 'Refunded',          value: '$' + Number(stats.refunded).toFixed(2), color: 'text-red-600 dark:text-red-400' },
                ]" :key="item.label"
                    class="rounded-xl border border-base dark:border-base bg-surface p-4 text-center">
                    <p :class="['text-2xl font-bold tabular-nums', item.color]">{{ item.value }}</p>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ item.label }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input v-model="search" type="search" placeholder="Search by student or transaction ID…" class="w-full rounded-lg border border-base dark:border-base bg-surface py-2 pl-9 pr-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <select v-model="status" class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Student</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Coupon</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!payments.data.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-subtle">No payments found.</td>
                        </tr>
                        <tr v-for="p in payments.data" :key="p.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40">
                            <td class="px-5 py-3 text-sm font-medium text-default dark:text-default">{{ p.student ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted max-w-[180px] truncate">{{ p.course ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm tabular-nums">
                                <span class="font-medium text-default dark:text-default">{{ p.currency }} ${{ p.amount.toFixed(2) }}</span>
                                <span v-if="p.discount_amount > 0" class="ml-1 text-xs text-emerald-600 dark:text-emerald-400">(-${{ p.discount_amount.toFixed(2) }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">
                                <span v-if="p.coupon_code" class="rounded-full bg-violet-100 dark:bg-violet-900/40 px-2 py-0.5 text-xs text-violet-700 dark:text-violet-300">{{ p.coupon_code }}</span>
                                <span v-else class="text-subtle dark:text-muted">—</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted tabular-nums">
                                {{ p.paid_at ? new Date(p.paid_at).toLocaleDateString() : new Date(p.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusClass[p.status] ?? statusClass.pending]">
                                    {{ p.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="payments.last_page > 1" class="flex items-center justify-between border-t border-base dark:border-base px-5 py-3">
                    <p class="text-sm text-muted">{{ payments.from }}–{{ payments.to }} of {{ payments.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in payments.links" :key="link.label" :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
