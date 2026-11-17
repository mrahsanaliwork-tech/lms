<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ users: Object })

const roleColors = {
    SuperAdmin: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    Admin: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    Instructor: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    Student: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
}

function assignRole(userId, role) {
    router.post(route('admin.users.assign-role', userId), { role }, { preserveScroll: true })
}
</script>

<template>
    <AdminLayout title="Users">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default">Users</h1>
                    <p class="text-sm text-muted">All platform users</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-base bg-surface">
                <table class="min-w-full divide-y divide-surface-200">
                    <thead class="bg-surface-2">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Roles</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Joined</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Change Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100">
                        <tr v-if="!users.data.length">
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-subtle">No users found.</td>
                        </tr>
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-surface-2">
                            <td class="px-5 py-3 font-medium text-default">{{ u.name }}</td>
                            <td class="px-5 py-3 text-sm text-muted">{{ u.email }}</td>
                            <td class="px-5 py-3">
                                <span v-for="r in u.roles" :key="r"
                                    :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mr-1', roleColors[r] ?? 'bg-surface-3 text-muted']">
                                    {{ r }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-muted">{{ u.created_at }}</td>
                            <td class="px-5 py-3">
                                <select @change="assignRole(u.id, $event.target.value)"
                                    class="rounded-lg border border-base bg-surface-2 px-2 py-1 text-xs text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Select role…</option>
                                    <option value="Student">Student</option>
                                    <option value="Instructor">Instructor</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="users.last_page > 1" class="flex items-center justify-between border-t border-base px-5 py-3">
                    <p class="text-sm text-muted">Showing {{ users.from }}–{{ users.to }} of {{ users.total }}</p>
                    <div class="flex gap-1">
                        <a v-for="link in users.links" :key="link.label" :href="link.url ?? '#'"
                            :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm',
                                link.active ? 'bg-primary-600 text-white' : 'text-muted hover:bg-surface-3',
                                !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
