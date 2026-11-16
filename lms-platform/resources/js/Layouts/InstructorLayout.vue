<template>
    <div class="flex h-screen overflow-hidden bg-surface-2" :class="{ dark: isDark }">
        <Sidebar
            :nav="instructorNav"
            :collapsed="sidebarCollapsed"
            class="hidden lg:flex"
            @toggle="sidebarCollapsed = !sidebarCollapsed"
        />

        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="mobileSidebar" class="fixed inset-0 z-40 lg:hidden" @click="mobileSidebar = false">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />
                <div class="absolute left-0 top-0 h-full" @click.stop>
                    <Sidebar :nav="instructorNav" :collapsed="false" class="flex h-full" @toggle="mobileSidebar = false" />
                </div>
            </div>
        </Transition>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <TopNavbar :title="title" :notifications="notifications" @menu-toggle="mobileSidebar = true">
                <template v-if="$slots.title" #title><slot name="title" /></template>
            </TopNavbar>
            <FlashMessage />
            <main class="flex-1 overflow-y-auto p-6"><slot /></main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import TopNavbar from '@/Components/TopNavbar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({ title: { type: String, default: 'Instructor' } });

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

const mk = (d) => ({ template: `<svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" v-bind="$attrs"><path stroke-linecap="round" stroke-linejoin="round" d="${d}" /></svg>` });
const instructorNav = [
    { label: 'Overview', items: [
        { label: 'Dashboard',   route: 'instructor.dashboard',          icon: mk('m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25') },
    ]},
    { label: 'Teaching', items: [
        { label: 'My Courses',  route: 'instructor.courses.index',      icon: mk('M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25') },
        { label: 'Assignments', route: 'instructor.assignments.index',  icon: mk('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z') },
        { label: 'Quizzes',     route: 'instructor.quizzes.index',      icon: mk('M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z') },
        { label: 'Attendance',     route: 'instructor.attendance.index',   icon: mk('M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5') },
        { label: 'Notifications',  route: 'notifications.index',           icon: mk('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0') },
    ]},
];
</script>
