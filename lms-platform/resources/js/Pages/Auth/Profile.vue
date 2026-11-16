<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const page  = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])

// Pick correct layout based on role
import StudentLayout    from '@/Layouts/StudentLayout.vue'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'
import AdminLayout      from '@/Layouts/AdminLayout.vue'

const Layout = computed(() => {
    if (roles.value.includes('SuperAdmin') || roles.value.includes('Admin')) return AdminLayout
    if (roles.value.includes('Instructor')) return InstructorLayout
    return StudentLayout
})

const props = defineProps({ user: Object })

// Profile form
const profileForm = useForm({
    name:  props.user.name  ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
})

function saveProfile() {
    profileForm.patch(route('profile.update'))
}

// Password form
const passwordForm = useForm({
    current_password: '',
    password:         '',
    password_confirmation: '',
})

function savePassword() {
    passwordForm.patch(route('profile.password'), {
        onSuccess: () => passwordForm.reset(),
    })
}

// Delete account
const showDelete  = ref(false)
const deleteForm  = useForm({ password: '' })

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'))
}

const tab = ref('profile')
</script>

<template>
    <component :is="Layout" title="Profile">
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default">My Profile</h1>
                <p class="text-sm text-muted mt-0.5">Manage your account settings</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 rounded-xl bg-surface-3 dark:bg-surface p-1 w-fit">
                <button v-for="t in [{ key: 'profile', label: 'Profile' }, { key: 'password', label: 'Password' }, { key: 'danger', label: 'Danger Zone' }]"
                    :key="t.key" @click="tab = t.key"
                    :class="['px-4 py-1.5 rounded-lg text-sm font-medium transition-colors',
                        tab === t.key ? 'bg-surface text-default shadow-sm' : 'text-muted hover:text-default']">
                    {{ t.label }}
                </button>
            </div>

            <!-- Profile Info -->
            <div v-if="tab === 'profile'" class="bg-surface rounded-xl border border-base p-6 space-y-4">
                <h2 class="text-sm font-semibold text-default border-b border-base pb-3">Personal Information</h2>

                <!-- Avatar -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-2xl font-bold text-primary-700 dark:text-primary-300">
                        {{ user.name?.[0]?.toUpperCase() ?? '?' }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-default">{{ user.name }}</p>
                        <p class="text-xs text-muted">{{ user.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Full Name</label>
                        <input v-model="profileForm.name" type="text"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-500">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Email</label>
                        <input v-model="profileForm.email" type="email"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-500">{{ profileForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Phone</label>
                        <input v-model="profileForm.phone" type="text" placeholder="Optional"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-base">
                    <p v-if="profileForm.recentlySuccessful" class="text-xs text-emerald-600 dark:text-emerald-400">Saved!</p>
                    <div v-else></div>
                    <button @click="saveProfile" :disabled="profileForm.processing"
                        class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 transition-colors">
                        {{ profileForm.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </div>

            <!-- Password -->
            <div v-if="tab === 'password'" class="bg-surface rounded-xl border border-base p-6 space-y-4">
                <h2 class="text-sm font-semibold text-default border-b border-base pb-3">Change Password</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">New Password</label>
                        <input v-model="passwordForm.password" type="password"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Confirm New Password</label>
                        <input v-model="passwordForm.password_confirmation" type="password"
                            class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-base">
                    <p v-if="passwordForm.recentlySuccessful" class="text-xs text-emerald-600 dark:text-emerald-400">Password updated!</p>
                    <div v-else></div>
                    <button @click="savePassword" :disabled="passwordForm.processing"
                        class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 transition-colors">
                        {{ passwordForm.processing ? 'Updating…' : 'Update Password' }}
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div v-if="tab === 'danger'" class="bg-surface rounded-xl border border-red-200 dark:border-red-900/50 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-red-600 dark:text-red-400 border-b border-red-100 dark:border-red-900/40 pb-3">Danger Zone</h2>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-default">Delete Account</p>
                        <p class="text-xs text-muted mt-0.5">Permanently delete your account and all data. This cannot be undone.</p>
                    </div>
                    <button @click="showDelete = true"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors shrink-0 ml-4">
                        Delete Account
                    </button>
                </div>

                <!-- Confirm delete -->
                <div v-if="showDelete" class="rounded-lg border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/10 p-4 space-y-3">
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">Enter your password to confirm deletion:</p>
                    <input v-model="deleteForm.password" type="password" placeholder="Your password"
                        class="w-full rounded-lg border border-red-200 dark:border-red-800 bg-surface px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-red-500" />
                    <p v-if="deleteForm.errors.password" class="text-xs text-red-500">{{ deleteForm.errors.password }}</p>
                    <div class="flex gap-2 justify-end">
                        <button @click="showDelete = false" class="rounded-lg border border-base px-4 py-2 text-sm text-muted hover:bg-surface-2">Cancel</button>
                        <button @click="deleteAccount" :disabled="!deleteForm.password || deleteForm.processing"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                            Permanently Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
