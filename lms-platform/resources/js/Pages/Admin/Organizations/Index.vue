<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({ organizations: Object })

// ── Modal state ──────────────────────────────────────────────────
const showModal   = ref(false)
const editTarget  = ref(null)

const form = useForm({ name: '', status: 'active' })

function openAdd() {
    editTarget.value = null
    form.reset()
    form.name   = ''
    form.status = 'active'
    showModal.value = true
}

function openEdit(org) {
    editTarget.value = org
    form.name   = org.name
    form.status = org.status
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    form.reset()
    editTarget.value = null
}

function submit() {
    if (editTarget.value) {
        form.patch(route('admin.organizations.update', editTarget.value.id), {
            onSuccess: closeModal,
        })
    } else {
        form.post(route('admin.organizations.store'), {
            onSuccess: closeModal,
        })
    }
}

function destroy(org) {
    if (confirm(`Delete "${org.name}"? This cannot be undone.`)) {
        router.delete(route('admin.organizations.destroy', org.id))
    }
}

const statusColor = (s) => ({
    active:    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    inactive:  'bg-surface-100 text-muted dark:bg-surface-700 dark:text-muted',
    suspended: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
}[s] ?? '')
</script>

<template>
    <AdminLayout title="Organizations">
        <div class="space-y-5">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default">Organizations</h1>
                    <p class="text-sm text-muted mt-0.5">Manage organizations enrolled on the platform</p>
                </div>
                <button @click="openAdd"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Organization
                </button>
            </div>

            <!-- Table -->
            <div v-if="organizations.data.length" class="rounded-xl border border-base bg-surface overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-surface-2 border-b border-base">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-muted">Name</th>
                            <th class="text-left px-4 py-3 font-medium text-muted">Slug</th>
                            <th class="text-left px-4 py-3 font-medium text-muted">Students</th>
                            <th class="text-left px-4 py-3 font-medium text-muted">Status</th>
                            <th class="text-left px-4 py-3 font-medium text-muted">Created</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base">
                        <tr v-for="org in organizations.data" :key="org.id" class="hover:bg-surface-2 transition-colors">
                            <td class="px-4 py-3 font-medium text-default">{{ org.name }}</td>
                            <td class="px-4 py-3 text-muted font-mono text-xs">{{ org.slug }}</td>
                            <td class="px-4 py-3 text-default">{{ org.students_count }}</td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusColor(org.status)]">
                                    {{ org.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ org.created_at }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <button @click="openEdit(org)" class="text-xs text-primary-600 hover:underline">Edit</button>
                                    <button @click="destroy(org)" class="text-xs text-red-500 hover:underline">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-base bg-surface p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-muted mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
                <p class="text-sm font-medium text-default">No organizations yet</p>
                <p class="text-xs text-muted mt-1">Click "Add Organization" to create one.</p>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <Modal v-model="showModal" @close="closeModal" max-width="md">
            <template #default>
                <div class="p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-default">
                        {{ editTarget ? 'Edit Organization' : 'Add Organization' }}
                    </h2>

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-muted mb-1">Organization Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="e.g. ABC School"
                                class="w-full rounded-lg border border-base bg-transparent px-3 py-2 text-sm text-default placeholder-surface-400 focus:outline-none focus:border-primary-500" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-muted mb-1">Status</label>
                            <select v-model="form.status"
                                class="w-full rounded-lg border border-base bg-surface px-3 py-2 text-sm text-default focus:outline-none focus:border-primary-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="closeModal" type="button"
                            class="rounded-lg border border-base px-4 py-2 text-sm text-muted hover:bg-surface-2">
                            Cancel
                        </button>
                        <button @click="submit" :disabled="form.processing"
                            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving…' : (editTarget ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </AdminLayout>
</template>
