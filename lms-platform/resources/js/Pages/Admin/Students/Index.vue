<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import Badge from '@/Components/Badge.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    students: Object,
    batches: Array,
    filters: Object,
})

const search = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? '')
const batchFilter = ref(props.filters.batch ?? '')
const showImportModal = ref(false)
const importForm = useForm({ file: null })

let searchTimer = null
watch([search, statusFilter, batchFilter], () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(), 400)
})

function applyFilters() {
    router.get(route('admin.students.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        batch: batchFilter.value || undefined,
    }, { preserveState: true, replace: true })
}

function handleImport() {
    importForm.post(route('admin.students.import'), {
        onSuccess: () => { showImportModal.value = false; importForm.reset() },
    })
}

function exportCsv() {
    window.location.href = route('admin.students.export')
}

function destroy(student) {
    if (!confirm(`Remove ${student.name}? This can be restored.`)) return
    router.delete(route('admin.students.destroy', student.id))
}

const statusVariant = {
    active: 'success',
    inactive: 'default',
    graduated: 'info',
    suspended: 'danger',
}

const columns = [
    { key: 'student_id', label: 'ID', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'organization', label: 'Organization', sortable: true },
    { key: 'batch', label: 'Batch', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'enrolled_at', label: 'Enrolled', sortable: true },
]
</script>

<template>
    <AdminLayout title="Students">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Students</h1>
                    <p class="text-sm text-muted dark:text-muted">Manage all enrolled students</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="showImportModal = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import CSV
                    </button>
                    <button @click="exportCsv"
                        class="inline-flex items-center gap-2 rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                    </button>
                    <a :href="route('admin.students.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Student
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input v-model="search" type="search" placeholder="Search name, email, ID…"
                        class="w-full rounded-lg border border-base dark:border-base bg-surface py-2 pl-9 pr-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <select v-model="statusFilter"
                    class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="graduated">Graduated</option>
                    <option value="suspended">Suspended</option>
                </select>
                <select v-model="batchFilter"
                    class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Batches</option>
                    <option v-for="b in batches" :key="b" :value="b">{{ b }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-base dark:border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
                    <thead class="bg-surface-2 dark:bg-surface">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Organization</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted">Enrolled</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                        <tr v-if="!students.data.length">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-subtle">No students found.</td>
                        </tr>
                        <tr v-for="s in students.data" :key="s.id" class="hover:bg-surface-2 dark:hover:bg-surface-700/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 text-sm font-semibold text-primary-700 dark:text-primary-300">
                                        {{ s.name?.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-default dark:text-default">{{ s.name }}</p>
                                        <p class="text-xs text-muted dark:text-muted">{{ s.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-muted dark:text-muted">{{ s.student_id }}</td>
                            <td class="px-4 py-3 text-sm text-muted dark:text-muted">{{ s.organization ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-muted dark:text-muted">{{ s.batch ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant[s.status] ?? 'default'" dot>{{ s.status }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted dark:text-muted">{{ s.enrolled_at ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a :href="route('admin.students.show', s.id)" class="rounded p-1 text-subtle hover:text-primary-600 dark:hover:text-primary-400" title="View">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a :href="route('admin.students.edit', s.id)" class="rounded p-1 text-subtle hover:text-amber-600 dark:hover:text-amber-400" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button @click="destroy(s)" class="rounded p-1 text-subtle hover:text-red-600 dark:hover:text-red-400" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="students.last_page > 1" class="flex items-center justify-between border-t border-base dark:border-base px-4 py-3">
                    <p class="text-sm text-muted dark:text-muted">
                        Showing {{ students.from }}–{{ students.to }} of {{ students.total }}
                    </p>
                    <div class="flex gap-1">
                        <a v-for="link in students.links" :key="link.label"
                            :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <Modal v-model="showImportModal" @close="showImportModal = false" title="Import Students via CSV">
            <form @submit.prevent="handleImport" class="space-y-4">
                <p class="text-sm text-muted dark:text-muted">
                    Upload a CSV with columns: <code class="rounded bg-surface-3 dark:bg-surface-2 px-1 py-0.5 text-xs">name, email, password, phone, batch, group, enrollment_date, status</code>
                </p>
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">CSV File</label>
                    <input type="file" accept=".csv,.txt"
                        @change="e => importForm.file = e.target.files[0]"
                        class="block w-full text-sm text-muted dark:text-default file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-primary-50 dark:file:bg-primary-900 file:text-primary-700 dark:file:text-primary-300 file:text-sm file:font-medium hover:file:bg-primary-100" />
                    <p v-if="importForm.errors.file" class="mt-1 text-xs text-red-500">{{ importForm.errors.file }}</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showImportModal = false"
                        class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                        Cancel
                    </button>
                    <button type="submit" :disabled="importForm.processing || !importForm.file"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                        {{ importForm.processing ? 'Importing…' : 'Import' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
