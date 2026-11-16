<template>
    <header class="h-16 border-b border-base bg-surface flex items-center gap-4 px-5 shrink-0">
        <!-- Mobile sidebar toggle -->
        <button
            @click="$emit('menu-toggle')"
            class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-muted hover:bg-surface-3 hover:text-default transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Page title / breadcrumb slot -->
        <div class="flex-1 min-w-0">
            <slot name="title">
                <h1 class="text-sm font-semibold font-display text-default truncate">{{ title }}</h1>
            </slot>
        </div>

        <!-- Right-side actions -->
        <div class="flex items-center gap-2">
            <!-- Dark mode toggle -->
            <button
                @click="toggleDark"
                class="w-9 h-9 flex items-center justify-center rounded-lg text-muted hover:bg-surface-3 hover:text-default transition-colors"
                :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <svg v-if="isDark" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            <!-- Notifications -->
            <div class="relative">
                <button
                    @click.stop="notifOpen = !notifOpen; userOpen = false"
                    class="w-9 h-9 flex items-center justify-center rounded-lg text-muted hover:bg-surface-3 hover:text-default transition-colors relative"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span v-if="unreadCount" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500" />
                </button>

                <!-- Notifications dropdown -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-1 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="notifOpen" v-click-outside="() => notifOpen = false"
                         class="absolute right-0 top-11 w-80 bg-surface rounded-xl shadow-xl border border-base overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-base">
                            <span class="text-sm font-semibold text-default">Notifications</span>
                            <div class="flex items-center gap-2">
                                <Badge v-if="unreadCount" variant="danger" size="xs">{{ unreadCount }}</Badge>
                                <button v-if="unreadCount" @click="markAllRead"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                    Mark all read
                                </button>
                            </div>
                        </div>
                        <div class="divide-y divide-[var(--color-border)] max-h-80 overflow-y-auto">
                            <component :is="n.url ? 'a' : 'div'" v-for="n in notifications" :key="n.id"
                                 :href="n.url || undefined"
                                 @click="markRead(n)"
                                 :class="['block px-4 py-3 hover:bg-surface-2 transition-colors cursor-pointer', !n.read_at && 'bg-primary-50/50 dark:bg-primary-900/10']">
                                <p class="text-sm font-medium text-default">{{ n.title }}</p>
                                <p class="text-xs text-muted mt-0.5 line-clamp-2">{{ n.message }}</p>
                                <p class="text-xs text-subtle mt-1">{{ formatTime(n.created_at) }}</p>
                            </component>
                            <div v-if="!notifications.length" class="px-4 py-8 text-center text-sm text-muted">
                                All caught up!
                            </div>
                        </div>
                        <div class="px-4 py-2 border-t border-base">
                            <a :href="route('notifications.index')" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- User menu -->
            <div class="relative">
                <button
                    @click.stop="userOpen = !userOpen; notifOpen = false"
                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-surface-3 transition-colors"
                >
                    <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center overflow-hidden">
                        <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                        <span v-else class="text-xs font-bold text-primary-700 dark:text-primary-300 select-none">
                            {{ initials }}
                        </span>
                    </div>
                    <span class="text-sm font-medium text-default hidden sm:block max-w-[120px] truncate">{{ user?.name }}</span>
                    <svg class="w-3.5 h-3.5 text-subtle hidden sm:block" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-1 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="userOpen" v-click-outside="() => userOpen = false"
                         class="absolute right-0 top-11 w-56 bg-surface rounded-xl shadow-xl border border-base overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-base">
                            <p class="text-sm font-semibold text-default truncate">{{ user?.name }}</p>
                            <p class="text-xs text-muted truncate">{{ user?.email }}</p>
                        </div>
                        <div class="p-1">
                            <Link :href="route('profile.edit')" @click="userOpen=false"
                                  class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-default hover:bg-surface-3 transition-colors">
                                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                Profile
                            </Link>
                            <div class="border-t border-base my-1" />
                            <Link :href="route('logout')" method="post" as="button"
                                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Sign out
                            </Link>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Badge from './Badge.vue';

const props = defineProps({
    title:         { type: String, default: '' },
    notifications: { type: Array, default: () => [] },
});

defineEmits(['menu-toggle']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const initials = computed(() => {
    const name = user.value?.name ?? '';
    return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
});
const unreadCount = computed(() => props.notifications.filter(n => !n.read_at).length);

const notifOpen = ref(false);
const userOpen  = ref(false);
const isDark    = ref(document.documentElement.classList.contains('dark'));

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function markRead(n) {
    if (n.read_at) return;
    router.post(route('notifications.read', n.id), {}, {
        preserveState: true, preserveScroll: true, only: ['notifications'],
    });
}

function markAllRead() {
    router.post(route('notifications.read-all'), {}, {
        preserveState: true, preserveScroll: true, only: ['notifications'],
    });
}

function formatTime(iso) {
    if (!iso) return '';
    const d   = new Date(iso);
    const now  = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

// Real-time: listen on the user's private channel for new notifications
let echoChannel = null;

onMounted(() => {
    const userId = page.props.auth?.user?.id;
    if (!userId || !window.Echo) return;

    echoChannel = window.Echo.private(`App.Models.User.${userId}`)
        .notification(() => {
            // Reload only the shared notifications prop
            router.reload({ only: ['notifications'] });
        });
});

onUnmounted(() => {
    echoChannel?.stopListening('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated');
    echoChannel = null;
});

</script>
