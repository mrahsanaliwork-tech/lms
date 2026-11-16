<template>
    <AdminLayout title="Dashboard">
        <template #title>
            <div>
                <h1 class="text-sm font-semibold font-display text-default">Dashboard</h1>
                <p class="text-xs text-muted">{{ today }}</p>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Stat cards ─────────────────────────────────────── -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                <StatCard title="Total Students"   :value="stats.totalStudents"   color="indigo"  :trend="stats.studentTrend">
                    <template #icon><UserGroupIcon /></template>
                </StatCard>
                <StatCard title="Active (30d)"     :value="stats.activeStudents"  color="emerald">
                    <template #icon><UserCheckIcon /></template>
                </StatCard>
                <StatCard title="Courses"          :value="stats.totalCourses"    color="sky">
                    <template #icon><BookOpenIcon /></template>
                </StatCard>
                <StatCard title="Enrollments"      :value="stats.totalEnrollments" color="violet" :trend="stats.enrollmentTrend">
                    <template #icon><AcademicCapIcon /></template>
                </StatCard>
                <StatCard title="Revenue"          :value="stats.totalRevenue"    color="amber"   prefix="$">
                    <template #icon><CurrencyDollarIcon /></template>
                </StatCard>
                <StatCard title="Completion Rate"  :value="stats.completionRate"  color="rose"    suffix="%">
                    <template #icon><CheckCircleIcon /></template>
                </StatCard>
            </div>

            <!-- ── Charts row ─────────────────────────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <!-- Revenue chart -->
                <div class="bg-surface rounded-xl border border-base p-5">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-sm font-semibold font-display text-default">Monthly Revenue</h2>
                            <p class="text-xs text-muted mt-0.5">Last 6 months</p>
                        </div>
                        <Badge variant="success" dot>Live</Badge>
                    </div>
                    <div class="h-52">
                        <Line :data="revenueChartData" :options="lineOptions('#6366f1')" />
                    </div>
                </div>

                <!-- Enrollment chart -->
                <div class="bg-surface rounded-xl border border-base p-5">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-sm font-semibold font-display text-default">Enrollment Trend</h2>
                            <p class="text-xs text-muted mt-0.5">Last 6 months</p>
                        </div>
                        <Badge variant="primary" dot>Tracking</Badge>
                    </div>
                    <div class="h-52">
                        <Bar :data="enrollmentChartData" :options="barOptions" />
                    </div>
                </div>
            </div>

            <!-- ── Bottom row ─────────────────────────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

                <!-- Recent enrollments -->
                <div class="xl:col-span-2 bg-surface rounded-xl border border-base overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-base">
                        <h2 class="text-sm font-semibold font-display text-default">Recent Enrollments</h2>
                        <Link :href="route('admin.courses.index')" class="text-xs text-primary-600 dark:text-primary-400 font-medium hover:underline">
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-[var(--color-border)]">
                        <div v-if="!recentEnrollments.length" class="py-10 text-center text-sm text-muted">
                            No enrollments yet.
                        </div>
                        <div v-for="e in recentEnrollments" :key="e.id"
                             class="flex items-center gap-3 px-5 py-3 hover:bg-surface-2 transition-colors">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-primary-700 dark:text-primary-300 select-none">
                                    {{ e.student.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-default truncate">{{ e.student }}</p>
                                <p class="text-xs text-muted truncate">{{ e.course }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <Badge :variant="statusVariant(e.status)" size="xs">{{ e.status }}</Badge>
                                <p class="text-xs text-subtle mt-1">{{ e.enrolled_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick actions + pending revenue -->
                <div class="space-y-5">
                    <!-- Pending revenue card -->
                    <div class="bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/50 rounded-xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-amber-700 dark:text-amber-400 uppercase tracking-wide">Pending</p>
                                <p class="text-xl font-bold font-display text-amber-900 dark:text-amber-200 tabular-nums">
                                    ${{ Number(stats.pendingRevenue).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-amber-700/70 dark:text-amber-400/60">Awaiting payment confirmation</p>
                    </div>

                    <!-- Quick actions -->
                    <div class="bg-surface rounded-xl border border-base p-5">
                        <h3 class="text-sm font-semibold font-display text-default mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <Link v-for="action in quickActions" :key="action.label"
                                  :href="route(action.route)"
                                  class="flex items-center gap-3 w-full p-3 rounded-xl border border-base hover:bg-surface-3 hover:border-primary-200 dark:hover:border-primary-800 transition-all group">
                                <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', action.iconBg]">
                                    <component :is="action.icon" :class="['w-4 h-4', action.iconColor]" />
                                </div>
                                <span class="text-sm font-medium text-default">{{ action.label }}</span>
                                <svg class="w-4 h-4 text-subtle ml-auto group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Activity log ───────────────────────────────────── -->
            <div class="bg-surface rounded-xl border border-base overflow-hidden">
                <div class="px-5 py-4 border-b border-base">
                    <h2 class="text-sm font-semibold font-display text-default">Recent Activity</h2>
                </div>
                <div class="divide-y divide-[var(--color-border)]">
                    <div v-if="!recentActivity.length" class="py-10 text-center text-sm text-muted">
                        No activity recorded yet.
                    </div>
                    <div v-for="log in recentActivity" :key="log.id"
                         class="flex items-center gap-4 px-5 py-3 hover:bg-surface-2 transition-colors text-sm">
                        <div class="w-7 h-7 rounded-full bg-surface-3 flex items-center justify-center shrink-0">
                            <span class="text-[10px] font-bold text-muted">{{ log.user.split(' ')[0][0] }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-medium text-default">{{ log.user }}</span>
                            <span class="text-muted mx-1">·</span>
                            <span class="text-muted">{{ log.action }}</span>
                            <span v-if="log.model_type" class="text-subtle ml-1">on {{ log.model_type }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-subtle">{{ log.created_at }}</p>
                            <p v-if="log.ip_address" class="text-[10px] text-subtle font-mono">{{ log.ip_address }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Line, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard    from '@/Components/StatCard.vue';
import Badge       from '@/Components/Badge.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    stats:             Object,
    monthlyRevenue:    Object,
    enrollmentTrend:   Object,
    recentEnrollments: Array,
    recentActivity:    Array,
});

const today = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

// ── Inline SVG icon components ────────────────────────────────────
const mk = (d) => ({ template: `<svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" v-bind="$attrs"><path stroke-linecap="round" stroke-linejoin="round" d="${d}" /></svg>` });
const UserGroupIcon      = mk('M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z');
const UserCheckIcon      = mk('M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
const BookOpenIcon       = mk('M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25');
const AcademicCapIcon    = mk('M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5');
const CurrencyDollarIcon = mk('M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
const CheckCircleIcon    = mk('M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z');

// ── Chart data ────────────────────────────────────────────────────
const isDark = () => document.documentElement.classList.contains('dark');

function lineOptions(color) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e2535', titleColor: '#94a3b8', bodyColor: '#f1f5f9', borderColor: '#2a3348', borderWidth: 1, padding: 10, cornerRadius: 8 } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } }, border: { display: false } },
            y: { grid: { color: isDark() ? '#1e2535' : '#f1f3f8', drawBorder: false }, ticks: { color: '#6b7280', font: { size: 11 }, callback: (v) => '$' + v.toLocaleString() }, border: { display: false } },
        },
    };
}

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e2535', titleColor: '#94a3b8', bodyColor: '#f1f5f9', borderColor: '#2a3348', borderWidth: 1, padding: 10, cornerRadius: 8 } },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } }, border: { display: false } },
        y: { grid: { color: '#f1f3f8', drawBorder: false }, ticks: { color: '#6b7280', font: { size: 11 }, stepSize: 1 }, border: { display: false } },
    },
};

const revenueChartData = computed(() => ({
    labels: props.monthlyRevenue.labels,
    datasets: [{
        data: props.monthlyRevenue.data,
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99,102,241,0.08)',
        borderWidth: 2.5,
        pointRadius: 4,
        pointBackgroundColor: '#6366f1',
        pointHoverRadius: 6,
        fill: true,
        tension: 0.4,
    }],
}));

const enrollmentChartData = computed(() => ({
    labels: props.enrollmentTrend.labels,
    datasets: [{
        data: props.enrollmentTrend.data,
        backgroundColor: (ctx) => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 180);
            gradient.addColorStop(0, 'rgba(99,102,241,0.85)');
            gradient.addColorStop(1, 'rgba(139,92,246,0.4)');
            return gradient;
        },
        borderRadius: 6,
        borderSkipped: false,
    }],
}));

// ── Helpers ───────────────────────────────────────────────────────
function statusVariant(status) {
    return { active: 'success', completed: 'primary', expired: 'warning', cancelled: 'danger' }[status] ?? 'default';
}

const quickActions = [
    { label: 'Manage Users',    route: 'admin.users.index',         iconBg: 'bg-indigo-50 dark:bg-indigo-900/20',  iconColor: 'text-indigo-600 dark:text-indigo-400', icon: UserGroupIcon },
    { label: 'All Courses',     route: 'admin.courses.index',       iconBg: 'bg-sky-50 dark:bg-sky-900/20',        iconColor: 'text-sky-600 dark:text-sky-400',       icon: BookOpenIcon },
    { label: 'Organizations',   route: 'admin.organizations.index', iconBg: 'bg-violet-50 dark:bg-violet-900/20',  iconColor: 'text-violet-600 dark:text-violet-400', icon: AcademicCapIcon },
    { label: 'Reports',         route: 'admin.reports.index',       iconBg: 'bg-emerald-50 dark:bg-emerald-900/20', iconColor: 'text-emerald-600 dark:text-emerald-400', icon: CheckCircleIcon },
];
</script>
