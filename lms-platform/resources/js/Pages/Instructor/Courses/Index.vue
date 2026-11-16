<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import InstructorLayout from '@/Layouts/InstructorLayout.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    courses: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? '')

let timer = null
watch([search, statusFilter], () => {
    clearTimeout(timer)
    timer = setTimeout(() => router.get(route('instructor.courses.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true }), 400)
})

function destroy(course) {
    if (!confirm(`Delete "${course.title}"?`)) return
    router.delete(route('instructor.courses.destroy', course.id))
}

const statusVariant = { draft: 'default', published: 'success', archived: 'danger' }
const approvalVariant = { pending: 'warning', approved: 'success', rejected: 'danger' }
</script>

<template>
    <InstructorLayout title="My Courses">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-default dark:text-default">My Courses</h1>
                    <p class="text-sm text-muted dark:text-muted">Manage your course catalog</p>
                </div>
                <a :href="route('instructor.courses.create')" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Course
                </a>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input v-model="search" type="search" placeholder="Search courses…" class="w-full rounded-lg border border-base dark:border-base bg-surface py-2 pl-9 pr-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <select v-model="statusFilter" class="rounded-lg border border-base dark:border-base bg-surface px-3 py-2 text-sm text-muted dark:text-default">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-if="!courses.data.length" class="col-span-full text-center py-16 text-subtle text-sm">No courses yet. Create your first course!</div>
                <div v-for="course in courses.data" :key="course.id" class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden flex flex-col">
                    <div class="relative h-40 bg-surface-3 dark:bg-surface-2 flex items-center justify-center">
                        <img v-if="course.thumbnail" :src="course.thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                        <svg v-else class="h-12 w-12 text-subtle dark:text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.869v6.262a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                        <div class="absolute top-2 left-2 flex gap-1">
                            <Badge :variant="statusVariant[course.status]" size="sm">{{ course.status }}</Badge>
                            <Badge :variant="approvalVariant[course.approval_status]" size="sm">{{ course.approval_status }}</Badge>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-1 gap-2">
                        <h3 class="font-semibold text-default dark:text-default leading-snug line-clamp-2">{{ course.title }}</h3>
                        <div class="flex gap-4 text-xs text-muted dark:text-muted">
                            <span class="font-semibold" :class="course.is_free ? 'text-emerald-600' : 'text-muted dark:text-muted'">{{ course.price }}</span>
                            <span>{{ course.level }}</span>
                            <span>{{ course.enrollments }} enrolled</span>
                        </div>
                        <div class="flex items-center justify-end gap-1 mt-auto pt-3 border-t border-surface-100 dark:border-base">
                            <a :href="route('instructor.courses.show', course.id)" class="rounded p-1.5 text-subtle hover:text-primary-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a :href="route('instructor.courses.edit', course.id)" class="rounded p-1.5 text-subtle hover:text-amber-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button @click="destroy(course)" class="rounded p-1.5 text-subtle hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="courses.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted dark:text-muted">Showing {{ courses.from }}–{{ courses.to }} of {{ courses.total }}</p>
                <div class="flex gap-1">
                    <a v-for="link in courses.links" :key="link.label" :href="link.url ?? '#'"
                        :class="['inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'text-muted dark:text-muted hover:bg-surface-3 dark:hover:bg-surface-700', !link.url ? 'opacity-40 cursor-default pointer-events-none' : '']"
                        v-html="link.label" />
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>
