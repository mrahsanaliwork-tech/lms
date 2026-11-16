<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
})

const activeTab = ref('general')

const tabs = [
    { key: 'general',  label: 'General' },
    { key: 'email',    label: 'Email' },
    { key: 'payment',  label: 'Payment' },
    { key: 'ai',       label: 'AI / OpenAI' },
]

// General
const general = ref({
    site_name:    props.settings.site_name    ?? 'LMS Platform',
    site_tagline: props.settings.site_tagline ?? 'Learn anything, anywhere.',
    contact_email:props.settings.contact_email ?? '',
    timezone:     props.settings.timezone     ?? 'UTC',
    currency:     props.settings.currency     ?? 'USD',
    allow_registration: props.settings.allow_registration ?? true,
    maintenance_mode:   props.settings.maintenance_mode   ?? false,
})

// Email
const email = ref({
    mail_driver:   props.settings.mail_driver   ?? 'smtp',
    mail_host:     props.settings.mail_host     ?? '',
    mail_port:     props.settings.mail_port     ?? '587',
    mail_username: props.settings.mail_username ?? '',
    mail_password: props.settings.mail_password ?? '',
    mail_from:     props.settings.mail_from     ?? '',
    mail_from_name:props.settings.mail_from_name ?? 'LMS Platform',
})

// Payment
const payment = ref({
    stripe_key:    props.settings.stripe_key    ?? '',
    stripe_secret: props.settings.stripe_secret ?? '',
    currency:      props.settings.currency      ?? 'USD',
    enable_free_courses: props.settings.enable_free_courses ?? true,
})

// AI
const ai = ref({
    openai_key:   props.settings.openai_key   ?? '',
    openai_model: props.settings.openai_model ?? 'gpt-4o-mini',
    ai_enabled:   props.settings.ai_enabled   ?? true,
})

const saved = ref(false)
function save() {
    saved.value = true
    setTimeout(() => saved.value = false, 2500)
}
</script>

<template>
    <AdminLayout title="Settings">
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-default">Settings</h1>
                <p class="text-sm text-muted mt-0.5">Configure your platform preferences</p>
            </div>

            <div class="flex gap-6">
                <!-- Sidebar tabs -->
                <div class="w-44 shrink-0 space-y-1">
                    <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
                        :class="['w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                            activeTab === t.key
                                ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-medium'
                                : 'text-muted hover:bg-surface-3 dark:hover:bg-surface-2']">
                        {{ t.label }}
                    </button>
                </div>

                <!-- Panel -->
                <div class="flex-1 bg-surface rounded-xl border border-base p-6 space-y-5">

                    <!-- General -->
                    <div v-if="activeTab === 'general'" class="space-y-4">
                        <h2 class="text-sm font-semibold text-default border-b border-base pb-3">General Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Site Name</label>
                                <input v-model="general.site_name" type="text" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Tagline</label>
                                <input v-model="general.site_tagline" type="text" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Contact Email</label>
                                <input v-model="general.contact_email" type="email" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Currency</label>
                                <select v-model="general.currency" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="USD">USD — US Dollar</option>
                                    <option value="EUR">EUR — Euro</option>
                                    <option value="GBP">GBP — British Pound</option>
                                    <option value="PKR">PKR — Pakistani Rupee</option>
                                    <option value="INR">INR — Indian Rupee</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Timezone</label>
                                <select v-model="general.timezone" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="UTC">UTC</option>
                                    <option value="Asia/Karachi">Asia/Karachi (PKT)</option>
                                    <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                    <option value="America/New_York">America/New_York (EST)</option>
                                    <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                    <option value="Europe/London">Europe/London (GMT)</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-3 pt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="general.allow_registration" class="rounded border-base text-primary-600 focus:ring-primary-500 w-4 h-4" />
                                <div>
                                    <p class="text-sm font-medium text-default">Allow Public Registration</p>
                                    <p class="text-xs text-muted">New users can register on the platform</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="general.maintenance_mode" class="rounded border-base text-primary-600 focus:ring-primary-500 w-4 h-4" />
                                <div>
                                    <p class="text-sm font-medium text-default">Maintenance Mode</p>
                                    <p class="text-xs text-muted">Only admins can access the platform</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Email -->
                    <div v-if="activeTab === 'email'" class="space-y-4">
                        <h2 class="text-sm font-semibold text-default border-b border-base pb-3">Email / SMTP Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Mail Host</label>
                                <input v-model="email.mail_host" type="text" placeholder="smtp.gmail.com" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Mail Port</label>
                                <input v-model="email.mail_port" type="text" placeholder="587" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Username</label>
                                <input v-model="email.mail_username" type="text" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Password</label>
                                <input v-model="email.mail_password" type="password" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">From Email</label>
                                <input v-model="email.mail_from" type="email" placeholder="noreply@example.com" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">From Name</label>
                                <input v-model="email.mail_from_name" type="text" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div v-if="activeTab === 'payment'" class="space-y-4">
                        <h2 class="text-sm font-semibold text-default border-b border-base pb-3">Payment / Stripe Settings</h2>
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-xs text-amber-700 dark:text-amber-300">
                            Never store raw card details. Use Stripe tokens only. Set keys in your <code class="font-mono">.env</code> file for production.
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Stripe Publishable Key</label>
                                <input v-model="payment.stripe_key" type="text" placeholder="pk_live_..." class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Stripe Secret Key</label>
                                <input v-model="payment.stripe_secret" type="password" placeholder="sk_live_..." class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer pt-1">
                            <input type="checkbox" v-model="payment.enable_free_courses" class="rounded border-base text-primary-600 focus:ring-primary-500 w-4 h-4" />
                            <div>
                                <p class="text-sm font-medium text-default">Allow Free Courses</p>
                                <p class="text-xs text-muted">Students can enroll in $0 courses without payment</p>
                            </div>
                        </label>
                    </div>

                    <!-- AI -->
                    <div v-if="activeTab === 'ai'" class="space-y-4">
                        <h2 class="text-sm font-semibold text-default border-b border-base pb-3">AI / OpenAI Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-muted mb-1">OpenAI API Key</label>
                                <input v-model="ai.openai_key" type="password" placeholder="sk-..." class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1">Model</label>
                                <select v-model="ai.openai_model" class="w-full rounded-lg border border-base bg-surface-2 px-3 py-2 text-sm text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="gpt-4o-mini">gpt-4o-mini (fast, cheap)</option>
                                    <option value="gpt-4o">gpt-4o (best quality)</option>
                                    <option value="gpt-3.5-turbo">gpt-3.5-turbo (legacy)</option>
                                </select>
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer pt-1">
                            <input type="checkbox" v-model="ai.ai_enabled" class="rounded border-base text-primary-600 focus:ring-primary-500 w-4 h-4" />
                            <div>
                                <p class="text-sm font-medium text-default">Enable AI Features</p>
                                <p class="text-xs text-muted">AI Tutor, quiz generation, and content suggestions</p>
                            </div>
                        </label>
                    </div>

                    <!-- Save button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-base">
                        <transition name="fade">
                            <span v-if="saved" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Settings saved!</span>
                        </transition>
                        <button @click="save" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
