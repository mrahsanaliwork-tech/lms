<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    student: Object,
    organizations: Array,
})

const form = useForm({
    name: props.student.name ?? '',
    email: props.student.email ?? '',
    phone: props.student.phone ?? '',
    organization_id: props.student.organization_id ?? '',
    batch: props.student.batch ?? '',
    group: props.student.group ?? '',
    enrollment_date: props.student.enrollment_date ?? '',
    status: props.student.status ?? 'active',
})

function submit() {
    form.patch(route('admin.students.update', props.student.id))
}
</script>

<template>
    <AdminLayout :title="`Edit — ${student.name}`">
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="flex items-center gap-3">
                <a :href="route('admin.students.show', student.id)" class="rounded p-1 text-subtle hover:text-muted dark:hover:text-surface-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">Edit Student</h1>
                    <p class="text-sm text-muted dark:text-muted">{{ student.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submit"
                class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">

                <!-- Account -->
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Account</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="form.errors.name ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Email <span class="text-red-500">*</span></label>
                        <input v-model="form.email" type="email"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="form.errors.email ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Phone</label>
                        <input v-model="form.phone" type="tel"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <hr class="border-surface-100 dark:border-base" />

                <!-- Academic Info -->
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Academic Info</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Organization</label>
                        <select v-model="form.organization_id"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">None</option>
                            <option v-for="org in organizations" :key="org.id" :value="org.id">{{ org.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Status <span class="text-red-500">*</span></label>
                        <select v-model="form.status"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="graduated">Graduated</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Batch</label>
                        <input v-model="form.batch" type="text"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Group</label>
                        <input v-model="form.group" type="text"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Enrollment Date</label>
                        <input v-model="form.enrollment_date" type="date"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-2">
                    <a :href="route('admin.students.show', student.id)"
                        class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                        Cancel
                    </a>
                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
