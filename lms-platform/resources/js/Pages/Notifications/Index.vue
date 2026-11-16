<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ notifications: Object })

const page = usePage()
const role = computed(() => {
    const roles = page.props.auth?.roles ?? []
    if (roles.includes('SuperAdmin') || roles.includes('Admin')) return 'admin'
    if (roles.includes('Instructor')) return 'instructor'
    return 'student'
})
const Layout = computed(() => {
    if (role.value === 'admin') return AdminLayout
    if (role.value === 'instructor') return InstructorLayout
    return StudentLayout
})

const iconMap = {
    sparkles:        'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',
    'academic-cap':  'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    'clipboard-check':'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'badge-check':   'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    'credit-card':   'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
    bell:            'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
}

function getIcon(icon) { return iconMap[icon] ?? iconMap.bell }

function markRead(id) {
    router.post(route('notifications.read', id), {}, {
        preserveState: true, preserveScroll: true, only: ['notifications'],
    })
}

function markAllRead() {
    router.post(route('notifications.read-all'), {}, {
        preserveState: true, preserveScroll: true, only: ['notifications'],
    })
}

function dismiss(id) {
    router.delete(route('notifications.destroy', id), {
        preserveScroll: true, only: ['notifications'],
    })
}

function formatTime(iso) {
    if (!iso) return ''
    const d   = new Date(iso)
    const now  = new Date()
    const diff = Math.floor((now - d) / 1000)
    if (diff < 60) return 'just now'
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago'
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
    return d.toLocaleDateString()
}

const unreadCount = () => props.notifications.data?.filter(n => !n.read_at).length ?? 0
</script>

<template>
    <component :is="Layout" title="Notifications">
        <div class="max-w-2xl mx-auto space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-default dark:text-default">Notifications</h1>
                    <p class="text-sm text-muted dark:text-muted">Stay up to date with your learning activity</p>
                </div>
                <button v-if="unreadCount() > 0" @click="markAllRead"
                    class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    Mark all as read
                </button>
            </div>

            <div class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden divide-y divide-surface-100 dark:divide-surface-700">
                <div v-if="!notifications.data?.length" class="px-6 py-12 text-center text-sm text-subtle">
                    No notifications yet.
                </div>

                <div v-for="n in notifications.data" :key="n.id"
                    :class="['group flex items-start gap-4 px-5 py-4 transition-colors hover:bg-surface-2 dark:hover:bg-surface-700/40', !n.read_at && 'bg-primary-50/50 dark:bg-primary-900/10']">

                    <!-- Icon -->
                    <div :class="['shrink-0 w-9 h-9 rounded-full flex items-center justify-center mt-0.5', n.read_at ? 'bg-surface-3 dark:bg-surface-2' : 'bg-primary-100 dark:bg-primary-900/40']">
                        <svg class="w-4.5 h-4.5" :class="n.read_at ? 'text-subtle' : 'text-primary-600 dark:text-primary-400'" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(n.icon)" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <component :is="n.url ? 'a' : 'p'" :href="n.url || undefined" @click="markRead(n.id)"
                            class="text-sm font-medium text-default dark:text-default hover:text-primary-600 dark:hover:text-primary-400 cursor-pointer">
                            {{ n.title }}
                        </component>
                        <p class="text-xs text-muted dark:text-muted mt-0.5 line-clamp-2">{{ n.message }}</p>
                        <p class="text-xs text-subtle dark:text-subtle mt-1">{{ formatTime(n.created_at) }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button v-if="!n.read_at" @click="markRead(n.id)"
                            title="Mark as read"
                            class="p-1.5 rounded text-subtle hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button @click="dismiss(n.id)"
                            title="Dismiss"
                            class="p-1.5 rounded text-subtle hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted">{{ notifications.from }}–{{ notifications.to }} of {{ notifications.total }}</p>
                <div class="flex gap-1">
                    <a v-for="link in notifications.links" :key="link.label" :href="link.url ?? '#'"
                        :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                        v-html="link.label" />
                </div>
            </div>
        </div>
    </component>
</template>
