<template>
    <StudentLayout title="My Progress">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold font-display text-default">My Progress</h1>
                <p class="text-sm text-muted mt-1">Track your learning journey across all enrolled courses.</p>
            </div>

            <!-- Summary stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.label"
                     class="bg-surface rounded-2xl border border-base p-4 text-center">
                    <div class="text-2xl font-bold font-display text-default">{{ stat.value }}</div>
                    <div class="text-xs text-muted mt-0.5">{{ stat.label }}</div>
                </div>
            </div>

            <!-- Course progress list -->
            <div v-if="enrollments.length" class="bg-surface rounded-2xl border border-base divide-y divide-base overflow-hidden">
                <div v-for="enr in enrollments" :key="enr.id" class="p-5">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-default truncate">{{ enr.course.title }}</h3>
                            <p class="text-xs text-muted mt-0.5">{{ enr.course.instructor?.user?.name }}</p>
                        </div>
                        <span :class="statusClass(enr.status)"
                              class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                            {{ statusLabel(enr.status) }}
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-2 bg-surface-3 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 :class="enr.progress === 100 ? 'bg-green-500' : 'bg-primary-500'"
                                 :style="{ width: enr.progress + '%' }" />
                        </div>
                        <span class="text-xs font-mono font-semibold text-default w-10 text-right">
                            {{ enr.progress }}%
                        </span>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-muted">
                            Enrolled {{ formatDate(enr.enrolled_at) }}
                        </span>
                        <Link v-if="enr.progress < 100"
                              :href="route('student.courses.learn', { course: enr.course.id })"
                              class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            {{ enr.progress === 0 ? 'Start course →' : 'Continue →' }}
                        </Link>
                        <span v-else class="text-xs font-medium text-green-600 dark:text-green-400">
                            ✓ Completed
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <EmptyState v-else
                title="No courses yet"
                description="Enroll in a course to start tracking your progress here."
            >
                <template #action>
                    <Link :href="route('student.courses.index')"
                          class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                        Browse courses
                    </Link>
                </template>
            </EmptyState>
        </div>
    </StudentLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    enrollments: { type: Array, default: () => [] },
});

const stats = computed(() => {
    const total     = props.enrollments.length;
    const completed = props.enrollments.filter(e => e.status === 'completed').length;
    const inProg    = props.enrollments.filter(e => e.status === 'active' && e.progress > 0).length;
    const avgProg   = total
        ? Math.round(props.enrollments.reduce((s, e) => s + e.progress, 0) / total)
        : 0;
    return [
        { label: 'Enrolled',    value: total },
        { label: 'Completed',   value: completed },
        { label: 'In Progress', value: inProg },
        { label: 'Avg Progress', value: avgProg + '%' },
    ];
});

function statusLabel(s) {
    return { active: 'In Progress', completed: 'Completed', expired: 'Expired' }[s] ?? s;
}
function statusClass(s) {
    return {
        active:    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        completed: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        expired:   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    }[s] ?? 'bg-surface-3 text-muted';
}
function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
}
</script>
