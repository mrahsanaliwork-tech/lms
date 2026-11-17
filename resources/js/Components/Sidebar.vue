<template>
    <aside
        :class="[
            'flex flex-col h-full bg-surface border-r border-base transition-all duration-300 ease-in-out overflow-hidden',
            collapsed ? 'w-16' : 'w-60'
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 h-16 border-b border-base shrink-0">
            <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
            </div>
            <span v-if="!collapsed" class="font-display font-bold text-sm text-default tracking-tight">LMS Platform</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">
            <template v-for="section in visibleNav" :key="section.label">
                <!-- Section label -->
                <p v-if="!collapsed && section.label" class="text-[10px] font-semibold text-subtle uppercase tracking-widest px-3 pt-4 pb-1">
                    {{ section.label }}
                </p>
                <div v-if="collapsed && section.label" class="border-t border-base my-2 mx-2" />

                <Link
                    v-for="item in section.items"
                    :key="item.route"
                    :href="route(item.route)"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group',
                        isActive(item.route)
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                            : 'text-muted hover:bg-surface-3 hover:text-default',
                        collapsed && 'justify-center',
                    ]"
                    :title="collapsed ? item.label : ''"
                >
                    <component :is="item.icon" :class="['w-5 h-5 shrink-0', isActive(item.route) ? 'text-primary-600 dark:text-primary-400' : 'text-subtle group-hover:text-default']" />
                    <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
                    <Badge v-if="!collapsed && item.badge" :variant="item.badgeVariant ?? 'primary'" size="xs" class="ml-auto">{{ item.badge }}</Badge>
                </Link>
            </template>
        </nav>

        <!-- Collapse toggle -->
        <div class="border-t border-base p-2 shrink-0">
            <button
                @click="$emit('toggle')"
                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-muted hover:bg-surface-3 hover:text-default transition-colors text-sm"
            >
                <svg :class="['w-4 h-4 transition-transform', collapsed && 'rotate-180']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 9l-3 3m0 0l3 3m-3-3h7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span v-if="!collapsed" class="text-xs">Collapse</span>
            </button>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Badge from './Badge.vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
    nav:       { type: Array, required: true },  // [{ label, items: [{ label, route, icon, badge?, badgeVariant? }] }]
});

defineEmits(['toggle']);

const page = usePage();

function isActive(routeName) {
    try { return route().current(routeName); }
    catch { return false; }
}

const visibleNav = computed(() => props.nav);
</script>
