<template>
    <div class="flex h-screen overflow-hidden bg-surface-2" :class="{ dark: isDark }">
        <!-- Sidebar (desktop) -->
        <Sidebar
            :nav="adminNav"
            :collapsed="sidebarCollapsed"
            class="hidden lg:flex"
            @toggle="sidebarCollapsed = !sidebarCollapsed"
        />

        <!-- Mobile sidebar overlay -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileSidebar" class="fixed inset-0 z-40 lg:hidden" @click="mobileSidebar = false">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />
                <div class="absolute left-0 top-0 h-full" @click.stop>
                    <Sidebar :nav="adminNav" :collapsed="false" class="flex h-full" @toggle="mobileSidebar = false" />
                </div>
            </div>
        </Transition>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <TopNavbar
                :title="title"
                :notifications="notifications"
                @menu-toggle="mobileSidebar = true"
            >
                <template v-if="$slots.title" #title><slot name="title" /></template>
            </TopNavbar>

            <!-- Flash messages -->
            <FlashMessage />

            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import TopNavbar from '@/Components/TopNavbar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({
    title: { type: String, default: 'Dashboard' },
});

const page          = usePage();
const notifications = computed(() => page.props.notifications ?? []);

const sidebarCollapsed = ref(false);
const mobileSidebar    = ref(false);
const isDark           = ref(false);

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark.value);
});

const adminNav = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard',     route: 'admin.dashboard',          icon: iconHome },
            { label: 'Reports',       route: 'admin.reports.index',      icon: iconChart },
        ],
    },
    {
        label: 'Management',
        items: [
            { label: 'Users',         route: 'admin.users.index',        icon: iconUsers },
            { label: 'Courses',       route: 'admin.courses.index',      icon: iconBook },
            { label: 'Organizations', route: 'admin.organizations.index', icon: iconBuilding },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'Settings',       route: 'admin.settings.index',    icon: iconCog },
            { label: 'Notifications',  route: 'notifications.index',     icon: mk('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0') },
        ],
    },
];
</script>

<script>
// Inline icon components (simple SVG wrappers)
const mk = (d) => ({ template: `<svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" v-bind="$attrs"><path stroke-linecap="round" stroke-linejoin="round" d="${d}" /></svg>` });
const iconHome     = mk('m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25');
const iconChart    = mk('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z');
const iconUsers    = mk('M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z');
const iconBook     = mk('M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25');
const iconBuilding = mk('M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z');
const iconCog      = mk('M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z');
</script>
