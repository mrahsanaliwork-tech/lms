<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
const props = defineProps({ payments: Object })

const statusClass = {
    completed: 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
    pending:   'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
    failed:    'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
    refunded:  'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted',
}
</script>

<template>
    <StudentLayout title="Payment History">
        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Payment History</h1>
                <p class="text-sm text-muted dark:text-muted mt-0.5">Your course purchases and transactions</p>
            </div>

            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface overflow-hidden">
                <div v-if="!payments.data.length" class="px-6 py-12 text-center text-sm text-subtle">
                    No payment history yet.
                </div>
                <div v-else class="divide-y divide-surface-100 dark:divide-surface-800">
                    <div v-for="p in payments.data" :key="p.id" class="px-5 py-4 flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-default dark:text-default truncate">{{ p.course ?? '—' }}</p>
                            <p class="text-xs text-subtle mt-0.5">
                                {{ p.paid_at ? new Date(p.paid_at).toLocaleDateString() : new Date(p.created_at).toLocaleDateString() }}
                                <span v-if="p.coupon_code" class="ml-2 text-emerald-600 dark:text-emerald-400">🏷 {{ p.coupon_code }}</span>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-default dark:text-default">
                                {{ p.currency }} ${{ p.amount.toFixed(2) }}
                            </p>
                            <p v-if="p.discount_amount > 0" class="text-xs text-emerald-600 dark:text-emerald-400">
                                -${{ p.discount_amount.toFixed(2) }} saved
                            </p>
                        </div>
                        <span :class="['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusClass[p.status] ?? statusClass.pending]">
                            {{ p.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted">{{ payments.from }}–{{ payments.to }} of {{ payments.total }}</p>
                <div class="flex gap-1">
                    <a v-for="link in payments.links" :key="link.label" :href="link.url ?? '#'"
                        :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                        v-html="link.label" />
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
