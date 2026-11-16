<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/Badge.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    course: Object,
    stats: Object,
    modules: Array,
})

// ── Local mutable tree for optimistic DnD ────────────────────────
const tree = ref(props.modules.map(m => ({ ...m, lessons: [...m.lessons] })))

// ── Status actions ────────────────────────────────────────────────
function changeStatus(action) {
    router.post(route(`admin.courses.${action}`, props.course.id))
}

// ── Approval ─────────────────────────────────────────────────────
const showRejectModal = ref(false)
const rejectReason = ref('')

function approve() {
    router.post(route('admin.courses.approve', props.course.id))
}
function rejectSubmit() {
    router.post(route('admin.courses.reject', props.course.id), { reason: rejectReason.value }, {
        onSuccess: () => { showRejectModal.value = false; rejectReason.value = '' }
    })
}

// ── Module CRUD ───────────────────────────────────────────────────
const showModuleModal = ref(false)
const editingModule = ref(null)
const moduleForm = useForm({ title: '', description: '', status: 'active' })

function openAddModule() { editingModule.value = null; moduleForm.reset(); showModuleModal.value = true }
function openEditModule(mod) {
    editingModule.value = mod
    moduleForm.title = mod.title
    moduleForm.description = mod.description ?? ''
    moduleForm.status = mod.status
    showModuleModal.value = true
}
function submitModule() {
    if (editingModule.value) {
        moduleForm.patch(route('admin.modules.update', editingModule.value.id), {
            onSuccess: () => { showModuleModal.value = false; router.reload() }
        })
    } else {
        moduleForm.post(route('admin.modules.store', props.course.id), {
            onSuccess: () => { showModuleModal.value = false; router.reload() }
        })
    }
}
function deleteModule(mod) {
    if (!confirm(`Delete module "${mod.title}" and all its lessons?`)) return
    router.delete(route('admin.modules.destroy', mod.id), { onSuccess: () => router.reload() })
}

// ── Lesson CRUD ───────────────────────────────────────────────────
const showLessonModal = ref(false)
const editingLesson = ref(null)
const lessonModuleId = ref(null)
const lessonForm = useForm({ module_id: '', title: '', type: 'video', file: null, duration: '', is_free: false, status: 'active' })

function openAddLesson(mod) {
    editingLesson.value = null
    lessonModuleId.value = mod.id
    lessonForm.reset()
    lessonForm.module_id = mod.id
    lessonForm.status = 'active'
    showLessonModal.value = true
}
function openEditLesson(les) {
    editingLesson.value = les
    lessonForm.module_id = les.module_id ?? ''
    lessonForm.title = les.title
    lessonForm.type = les.type
    lessonForm.duration = les.duration ?? ''
    lessonForm.is_free = les.is_free
    lessonForm.status = les.status
    showLessonModal.value = true
}
function submitLesson() {
    if (editingLesson.value) {
        lessonForm.patch(route('admin.lessons.update', editingLesson.value.id), {
            onSuccess: () => { showLessonModal.value = false; router.reload() }
        })
    } else {
        lessonForm.post(route('admin.lessons.store'), {
            onSuccess: () => { showLessonModal.value = false; router.reload() }
        })
    }
}
function deleteLesson(les) {
    if (!confirm(`Delete lesson "${les.title}"?`)) return
    router.delete(route('admin.lessons.destroy', les.id), { onSuccess: () => router.reload() })
}

// ── Drag-to-reorder modules ───────────────────────────────────────
const dragModIdx = ref(null)

function onModDragStart(i) { dragModIdx.value = i }
function onModDragOver(e, i) {
    e.preventDefault()
    if (dragModIdx.value === null || dragModIdx.value === i) return
    const list = tree.value
    const item = list.splice(dragModIdx.value, 1)[0]
    list.splice(i, 0, item)
    dragModIdx.value = i
}
function onModDragEnd() {
    const payload = tree.value.map((m, i) => ({ id: m.id, order: i + 1 }))
    router.post(route('admin.modules.reorder', props.course.id), { modules: payload }, { preserveState: true })
    dragModIdx.value = null
}

// ── Drag-to-reorder lessons ───────────────────────────────────────
const dragLesModIdx = ref(null)
const dragLesIdx = ref(null)

function onLesDragStart(mi, li) { dragLesModIdx.value = mi; dragLesIdx.value = li }
function onLesDragOver(e, mi, li) {
    e.preventDefault()
    if (dragLesModIdx.value !== mi || dragLesIdx.value === null || dragLesIdx.value === li) return
    const list = tree.value[mi].lessons
    const item = list.splice(dragLesIdx.value, 1)[0]
    list.splice(li, 0, item)
    dragLesIdx.value = li
}
function onLesDragEnd(mi) {
    const mod = tree.value[mi]
    const payload = mod.lessons.map((l, i) => ({ id: l.id, order: i + 1 }))
    router.post(route('admin.lessons.reorder', mod.id), { lessons: payload }, { preserveState: true })
    dragLesIdx.value = null
}

const statusVariant = { draft: 'default', published: 'success', archived: 'danger' }
const approvalVariant = { pending: 'warning', approved: 'success', rejected: 'danger' }
const typeColor = { video: 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300', pdf: 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300', text: 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted' }
</script>

<template>
    <AdminLayout :title="course.title">
        <div class="space-y-6">
            <!-- Back -->
            <a :href="route('admin.courses.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Courses
            </a>

            <!-- Header Card -->
            <div class="rounded-xl border border-base dark:border-base bg-surface overflow-hidden">
                <div class="flex flex-col sm:flex-row">
                    <!-- Thumbnail -->
                    <div class="h-48 sm:h-auto sm:w-64 shrink-0 bg-surface-3 dark:bg-surface-2 flex items-center justify-center">
                        <img v-if="course.thumbnail" :src="course.thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                        <svg v-else class="h-16 w-16 text-subtle dark:text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.869v6.262a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                    </div>

                    <div class="flex-1 p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-xl font-bold text-default dark:text-default">{{ course.title }}</h1>
                                <p class="text-sm text-muted dark:text-muted mt-0.5">{{ course.instructor }} · {{ course.category }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Badge :variant="statusVariant[course.status]">{{ course.status }}</Badge>
                                <Badge :variant="approvalVariant[course.approval_status]">{{ course.approval_status }}</Badge>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div v-for="item in [
                                { label: 'Enrollments', value: stats.enrollments },
                                { label: 'Completed', value: stats.completed },
                                { label: 'Modules', value: stats.modules },
                                { label: 'Lessons', value: stats.lessons },
                            ]" :key="item.label" class="text-center">
                                <p class="text-xl font-bold text-default dark:text-default">{{ item.value }}</p>
                                <p class="text-xs text-subtle">{{ item.label }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2 pt-1 border-t border-surface-100 dark:border-base">
                            <a :href="route('admin.courses.edit', course.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-base dark:border-base px-3 py-1.5 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                                Edit Course
                            </a>
                            <button v-if="course.status === 'published'" @click="changeStatus('unpublish')"
                                class="rounded-lg border border-amber-200 dark:border-amber-800 px-3 py-1.5 text-sm text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20">
                                Unpublish
                            </button>
                            <button v-else-if="course.status !== 'archived'" @click="changeStatus('publish')"
                                class="rounded-lg border border-emerald-200 dark:border-emerald-800 px-3 py-1.5 text-sm text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                                Publish
                            </button>
                            <button v-if="course.status !== 'archived'" @click="changeStatus('archive')"
                                class="rounded-lg border border-base dark:border-base px-3 py-1.5 text-sm text-muted hover:bg-surface-2 dark:hover:bg-surface-700">
                                Archive
                            </button>

                            <!-- Approval actions -->
                            <template v-if="course.approval_status === 'pending'">
                                <button @click="approve" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">Approve</button>
                                <button @click="showRejectModal = true" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                            </template>
                        </div>

                        <!-- Rejection reason -->
                        <div v-if="course.rejection_reason" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300">
                            <span class="font-semibold">Rejection reason:</span> {{ course.rejection_reason }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Curriculum Tree -->
            <div class="rounded-xl border border-base dark:border-base bg-surface">
                <div class="flex items-center justify-between px-6 py-4 border-b border-base dark:border-base">
                    <h2 class="font-semibold text-default dark:text-default">Curriculum</h2>
                    <button @click="openAddModule" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Module
                    </button>
                </div>

                <div v-if="!tree.length" class="px-6 py-10 text-center text-sm text-subtle">No modules yet. Add the first module.</div>

                <div class="divide-y divide-surface-100 dark:divide-surface-700">
                    <div v-for="(mod, mi) in tree" :key="mod.id"
                        draggable="true"
                        @dragstart="onModDragStart(mi)"
                        @dragover="onModDragOver($event, mi)"
                        @dragend="onModDragEnd">

                        <!-- Module row -->
                        <div class="flex items-center gap-3 px-6 py-3 bg-surface-2 dark:bg-surface-2/50">
                            <svg class="h-4 w-4 cursor-grab text-subtle shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="flex-1 font-medium text-default dark:text-default">{{ mod.title }}</span>
                            <span class="text-xs text-subtle">{{ mod.lessons.length }} lessons</span>
                            <Badge :variant="mod.status === 'active' ? 'success' : 'default'" size="sm">{{ mod.status }}</Badge>
                            <div class="flex gap-1">
                                <button @click="openAddLesson(mod)" class="rounded p-1 text-subtle hover:text-primary-600" title="Add lesson">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <button @click="openEditModule(mod)" class="rounded p-1 text-subtle hover:text-amber-600" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="deleteModule(mod)" class="rounded p-1 text-subtle hover:text-red-600" title="Delete">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Lessons -->
                        <div v-if="mod.lessons.length" class="divide-y divide-surface-50 dark:divide-surface-700/50">
                            <div v-for="(les, li) in mod.lessons" :key="les.id"
                                draggable="true"
                                @dragstart="onLesDragStart(mi, li)"
                                @dragover="onLesDragOver($event, mi, li)"
                                @dragend="onLesDragEnd(mi)"
                                class="flex items-center gap-3 pl-12 pr-6 py-2.5">
                                <svg class="h-3.5 w-3.5 cursor-grab text-subtle shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span :class="['text-xs rounded px-1.5 py-0.5 font-medium shrink-0', typeColor[les.type]]">{{ les.type.toUpperCase() }}</span>
                                <span class="flex-1 text-sm text-muted dark:text-muted">{{ les.title }}</span>
                                <span v-if="les.is_free" class="text-xs text-emerald-600 dark:text-emerald-400">Free</span>
                                <span v-if="les.duration" class="text-xs text-subtle">{{ Math.floor(les.duration / 60) }}m {{ les.duration % 60 }}s</span>
                                <div class="flex gap-1">
                                    <button @click="openEditLesson({ ...les, module_id: mod.id })" class="rounded p-1 text-subtle hover:text-amber-600">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="deleteLesson(les)" class="rounded p-1 text-subtle hover:text-red-600">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Modal -->
        <Modal v-model="showModuleModal" @close="showModuleModal = false" :title="editingModule ? 'Edit Module' : 'Add Module'">
            <form @submit.prevent="submitModule" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Title</label>
                    <input v-model="moduleForm.title" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    <p v-if="moduleForm.errors.title" class="mt-1 text-xs text-red-500">{{ moduleForm.errors.title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Description</label>
                    <textarea v-model="moduleForm.description" rows="2" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Status</label>
                    <select v-model="moduleForm.status" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="showModuleModal = false" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default">Cancel</button>
                    <button type="submit" :disabled="moduleForm.processing" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                        {{ moduleForm.processing ? 'Saving…' : (editingModule ? 'Save' : 'Add Module') }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Lesson Modal -->
        <Modal v-model="showLessonModal" @close="showLessonModal = false" :title="editingLesson ? 'Edit Lesson' : 'Add Lesson'">
            <form @submit.prevent="submitLesson" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Title</label>
                    <input v-model="lessonForm.title" type="text" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    <p v-if="lessonForm.errors.title" class="mt-1 text-xs text-red-500">{{ lessonForm.errors.title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Type</label>
                        <select v-model="lessonForm.type" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="video">Video</option>
                            <option value="pdf">PDF</option>
                            <option value="text">Text</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Duration (seconds)</label>
                        <input v-model="lessonForm.duration" type="number" min="0" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>
                <div v-if="lessonForm.type !== 'text'">
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">File {{ editingLesson ? '(leave blank to keep current)' : '' }}</label>
                    <input type="file" :accept="lessonForm.type === 'video' ? '.mp4,.webm' : '.pdf'"
                        @change="e => lessonForm.file = e.target.files[0]"
                        class="block w-full text-sm text-muted dark:text-default file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary-50 dark:file:bg-primary-900 file:text-primary-700 dark:file:text-primary-300 hover:file:bg-primary-100" />
                    <p class="mt-0.5 text-xs text-subtle">Max 100 MB</p>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-muted dark:text-default cursor-pointer">
                        <input type="checkbox" v-model="lessonForm.is_free" class="rounded border-base text-primary-600 focus:ring-primary-500" />
                        Free preview
                    </label>
                    <select v-model="lessonForm.status" class="rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-1.5 text-sm text-muted dark:text-default">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="showLessonModal = false" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default">Cancel</button>
                    <button type="submit" :disabled="lessonForm.processing" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50">
                        {{ lessonForm.processing ? 'Saving…' : (editingLesson ? 'Save' : 'Add Lesson') }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Reject Modal -->
        <Modal v-model="showRejectModal" @close="showRejectModal = false" title="Reject Course">
            <div class="space-y-4">
                <p class="text-sm text-muted dark:text-muted">Provide a reason so the instructor can improve the course.</p>
                <textarea v-model="rejectReason" rows="3" placeholder="e.g. Content quality below standards…"
                    class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                <div class="flex justify-end gap-3">
                    <button @click="showRejectModal = false" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default">Cancel</button>
                    <button @click="rejectSubmit" :disabled="!rejectReason.trim()" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">Reject</button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
