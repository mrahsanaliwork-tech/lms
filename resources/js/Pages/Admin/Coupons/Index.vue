<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    coupons: Object,
    courses: Array,
})

const showModal = ref(false)
const editing   = ref(null)

function blankForm() {
    return {
        code: '', description: '', type: 'percent', value: '', max_discount: '',
        min_order_amount: 0, usage_limit: '', per_user_limit: 1,
        course_id: '', is_active: true, starts_at: '', expires_at: '',
    }
}

const form = useForm(blankForm())

function openCreate() {
    editing.value = null
    form.reset()
    Object.assign(form, blankForm())
    showModal.value = true
}

function openEdit(c) {
    editing.value = c
    form.code             = c.code
    form.description      = c.description ?? ''
    form.type             = c.type
    form.value            = c.value
    form.max_discount     = c.max_discount ?? ''
    form.min_order_amount = c.min_order_amount
    form.usage_limit      = c.usage_limit ?? ''
    form.per_user_limit   = c.per_user_limit
    form.course_id        = c.course_id ?? ''
    form.is_active        = c.is_active
    form.starts_at        = c.starts_at ? c.starts_at.substring(0, 16) : ''
    form.expires_at       = c.expires_at ? c.expires_at.substring(0, 16) : ''
    form.clearErrors()
    showModal.value = true
}

function save() {
    if (editing.value) {
        form.patch(route('admin.coupons.update', editing.value.id), {
            onSuccess: () => { showModal.value = false },
        })
    } else {
        form.post(route('admin.coupons.store'), {
            onSuccess: () => { showModal.value = false },
        })
    }
}

function destroy(id) {
    if (!confirm('Delete this coupon?')) return
    router.delete(route('admin.coupons.destroy', id))
}
</script>

<template>
    <AdminLayout title="Coupons">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Coupons</h1>
                    <p class="text-sm text-muted dark:text-muted">Discount codes for course purchases</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Coupon
                </button>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 overflow-y-auto py-8">
                <div class="w-full max-w-lg rounded-2xl bg-surface p-6 shadow-xl space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-default dark:text-default">{{ editing ? 'Edit Coupon' : 'New Coupon' }}</h2>
                        <button @click="showModal = false" class="text-subtle hover:text-muted">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Code *</label>
                            <input v-model="form.code" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm uppercase font-mono tracking-widest text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            <p v-if="form.errors.code" class="mt-0.5 text-xs text-red-500">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Type *</label>
                            <select v-model="form.type" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed ($)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Value *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-subtle">{{ form.type === 'percent' ? '%' : '$' }}</span>
                                <input v-model="form.value" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 pl-7 pr-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Max Discount Cap ($)</label>
                            <input v-model="form.max_discount" type="number" min="0" placeholder="No cap" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Min Order ($)</label>
                            <input v-model="form.min_order_amount" type="number" min="0" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Usage Limit</label>
                            <input v-model="form.usage_limit" type="number" min="1" placeholder="Unlimited" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Per-user Limit</label>
                            <input v-model="form.per_user_limit" type="number" min="0" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Course (blank = all)</label>
                            <select v-model="form.course_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                                <option value="">All courses</option>
                                <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Starts At</label>
                            <input v-model="form.starts_at" type="datetime-local" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">Expires At</label>
                            <input v-model="form.expires_at" type="datetime-local" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-muted mb-1">Description</label>
                            <input v-model="form.description" type="text" placeholder="Internal note" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input id="is_active" type="checkbox" v-model="form.is_active" class="h-4 w-4 rounded border-base text-primary-600" />
                            <label for="is_active" class="text-sm text-muted dark:text-default cursor-pointer">Active</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="showModal = false" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default">Cancel</button>
                        <button @click="save" :disabled="form.processing" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving…' : (editing ? 'Save Changes' : 'Create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Code</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Discount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Usage</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Expires</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!coupons.data.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-subtle">No coupons yet.</td>
                        </tr>
                        <tr v-for="c in coupons.data" :key="c.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/40">
                            <td class="px-5 py-3">
                                <span class="font-mono text-sm font-semibold text-default dark:text-default">{{ c.code }}</span>
                                <p v-if="c.description" class="text-xs text-subtle mt-0.5 truncate max-w-32">{{ c.description }}</p>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-default">
                                {{ c.type === 'percent' ? `${c.value}%` : `$${c.value}` }}
                                <span v-if="c.max_discount" class="text-xs text-subtle"> (max ${{ c.max_discount }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted tabular-nums">
                                {{ c.used_count }} / {{ c.usage_limit ?? '∞' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted max-w-[140px] truncate">{{ c.course ?? 'All courses' }}</td>
                            <td class="px-5 py-3 text-sm text-muted dark:text-muted">
                                {{ c.expires_at ? new Date(c.expires_at).toLocaleDateString() : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', c.is_active ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-surface-3 dark:bg-surface-2 text-muted']">
                                    {{ c.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(c)" class="rounded p-1.5 text-subtle hover:text-amber-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="destroy(c.id)" class="rounded p-1.5 text-subtle hover:text-red-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="coupons.last_page > 1" class="flex items-center justify-between border-t border-base dark:border-base px-5 py-3">
                    <p class="text-sm text-muted">{{ coupons.from }}–{{ coupons.to }} of {{ coupons.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in coupons.links" :key="link.label" :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
