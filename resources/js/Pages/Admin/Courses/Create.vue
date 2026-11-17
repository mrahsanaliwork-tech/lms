<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    instructors: Array,
    categories: Array,
})

// ── Step management ──────────────────────────────────────────────
const step = ref(1)
const totalSteps = 3

const stepLabels = ['Basic Info', 'Curriculum', 'Pricing']

// ── Form ─────────────────────────────────────────────────────────
const form = useForm({
    title: '',
    description: '',
    instructor_id: '',
    category_id: '',
    thumbnail: null,
    level: 'beginner',
    language: 'en',
    prerequisites: [],
    status: 'draft',
    price: 0,
    is_free: false,
    duration: '',
})

// ── Thumbnail preview ─────────────────────────────────────────────
const thumbnailPreview = ref(null)
function onThumbnail(e) {
    const file = e.target.files[0]
    form.thumbnail = file
    if (file) thumbnailPreview.value = URL.createObjectURL(file)
}

// ── Prerequisites list ────────────────────────────────────────────
const prereqInput = ref('')
function addPrereq() {
    const v = prereqInput.value.trim()
    if (v && !form.prerequisites.includes(v)) form.prerequisites.push(v)
    prereqInput.value = ''
}
function removePrereq(i) { form.prerequisites.splice(i, 1) }

// ── Curriculum state (local only — saved when course is created) ──
const modules = ref([])
let moduleIdSeq = 0

function addModule() {
    modules.value.push({
        _id: ++moduleIdSeq,
        title: 'New Module',
        description: '',
        lessons: [],
        editing: true,
    })
}

function removeModule(idx) { modules.value.splice(idx, 1) }

function addLesson(mod) {
    mod.lessons.push({
        _id: ++moduleIdSeq,
        title: 'New Lesson',
        type: 'video',
        is_free: false,
        editing: true,
    })
}

function removeLesson(mod, idx) { mod.lessons.splice(idx, 1) }

// ── Drag-to-reorder (native HTML5 DnD) ───────────────────────────
const dragModIdx = ref(null)
const dragLesIdx = ref(null)
const dragModRef = ref(null)

function onModDragStart(idx) { dragModIdx.value = idx }
function onModDragOver(e, idx) {
    e.preventDefault()
    if (dragModIdx.value === null || dragModIdx.value === idx) return
    const list = modules.value
    const item = list.splice(dragModIdx.value, 1)[0]
    list.splice(idx, 0, item)
    dragModIdx.value = idx
}

function onLesDragStart(modIdx, lesIdx) { dragModRef.value = modIdx; dragLesIdx.value = lesIdx }
function onLesDragOver(e, modIdx, lesIdx) {
    e.preventDefault()
    if (dragModRef.value !== modIdx || dragLesIdx.value === null || dragLesIdx.value === lesIdx) return
    const list = modules.value[modIdx].lessons
    const item = list.splice(dragLesIdx.value, 1)[0]
    list.splice(lesIdx, 0, item)
    dragLesIdx.value = lesIdx
}

// ── Submit ────────────────────────────────────────────────────────
function submit() {
    form.post(route('admin.courses.store'))
}

const canNext = computed(() => {
    if (step.value === 1) return form.title && form.instructor_id && form.category_id
    return true
})
</script>

<template>
    <AdminLayout title="New Course">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Back -->
            <div class="flex items-center gap-3">
                <a :href="route('admin.courses.index')" class="rounded p-1 text-subtle hover:text-muted dark:hover:text-surface-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Create Course</h1>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center gap-0">
                <template v-for="(label, i) in stepLabels" :key="i">
                    <div class="flex items-center gap-2">
                        <div :class="['flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-colors', step > i + 1 ? 'bg-primary-600 text-white' : step === i + 1 ? 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-900' : 'bg-surface-3 dark:bg-surface-2 text-muted']">
                            <svg v-if="step > i + 1" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span :class="['text-sm', step === i + 1 ? 'font-semibold text-default dark:text-default' : 'text-muted']">{{ label }}</span>
                    </div>
                    <div v-if="i < stepLabels.length - 1" class="flex-1 mx-3 h-px bg-surface-3 dark:bg-surface-2" />
                </template>
            </div>

            <!-- Step 1: Basic Info -->
            <div v-if="step === 1" class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Basic Info</h2>

                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Course Title <span class="text-red-500">*</span></label>
                    <input v-model="form.title" type="text" placeholder="e.g. Advanced Web Development"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :class="form.errors.title ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Description</label>
                    <textarea v-model="form.description" rows="4" placeholder="What will students learn?"
                        class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Instructor <span class="text-red-500">*</span></label>
                        <select v-model="form.instructor_id"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="form.errors.instructor_id ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'">
                            <option value="">Select instructor</option>
                            <option v-for="i in instructors" :key="i.id" :value="i.id">{{ i.name }}</option>
                        </select>
                        <p v-if="form.errors.instructor_id" class="mt-1 text-xs text-red-500">{{ form.errors.instructor_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Category <span class="text-red-500">*</span></label>
                        <select v-model="form.category_id"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="form.errors.category_id ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'">
                            <option value="">Select category</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Level</label>
                        <select v-model="form.level" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Language</label>
                        <select v-model="form.language" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="en">English</option>
                            <option value="ur">Urdu</option>
                            <option value="ar">Arabic</option>
                            <option value="fr">French</option>
                            <option value="es">Spanish</option>
                        </select>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Thumbnail</label>
                    <div class="flex items-start gap-4">
                        <div class="h-24 w-40 shrink-0 rounded-lg border-2 border-dashed border-base dark:border-base overflow-hidden flex items-center justify-center bg-surface-2 dark:bg-surface-2">
                            <img v-if="thumbnailPreview" :src="thumbnailPreview" class="h-full w-full object-cover" />
                            <svg v-else class="h-8 w-8 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <input type="file" accept="image/*" @change="onThumbnail" class="block text-sm text-muted dark:text-default file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary-50 dark:file:bg-primary-900 file:text-primary-700 dark:file:text-primary-300 file:text-sm hover:file:bg-primary-100" />
                            <p class="mt-1 text-xs text-subtle">JPG, PNG or WebP · max 2 MB</p>
                        </div>
                    </div>
                </div>

                <!-- Prerequisites -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Prerequisites</label>
                    <div class="flex gap-2 mb-2">
                        <input v-model="prereqInput" @keydown.enter.prevent="addPrereq" type="text" placeholder="Add prerequisite…"
                            class="flex-1 rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <button type="button" @click="addPrereq" class="rounded-lg bg-primary-50 dark:bg-primary-900 px-3 py-2 text-sm text-primary-700 dark:text-primary-300 hover:bg-primary-100">Add</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(p, i) in form.prerequisites" :key="i"
                            class="inline-flex items-center gap-1 rounded-full bg-surface-3 dark:bg-surface-2 px-3 py-1 text-xs text-muted dark:text-default">
                            {{ p }}
                            <button @click="removePrereq(i)" class="ml-1 text-subtle hover:text-red-500">×</button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Curriculum -->
            <div v-if="step === 2" class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Curriculum Builder</h2>
                    <button @click="addModule" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Module
                    </button>
                </div>

                <p v-if="!modules.length" class="rounded-lg border-2 border-dashed border-base dark:border-base p-8 text-center text-sm text-subtle">
                    No modules yet. Click "Add Module" to build your curriculum.
                </p>

                <div class="space-y-3">
                    <div v-for="(mod, mi) in modules" :key="mod._id"
                        draggable="true"
                        @dragstart="onModDragStart(mi)"
                        @dragover="onModDragOver($event, mi)"
                        class="rounded-lg border border-base dark:border-base bg-surface-2 dark:bg-surface-2/50">

                        <!-- Module header -->
                        <div class="flex items-center gap-3 p-3">
                            <svg class="h-4 w-4 cursor-grab text-subtle shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <div v-if="mod.editing" class="flex flex-1 items-center gap-2">
                                <input v-model="mod.title" @blur="mod.editing = false" @keydown.enter="mod.editing = false" type="text"
                                    class="flex-1 rounded border border-base dark:border-base bg-surface-2 px-2 py-1 text-sm text-default dark:text-default focus:outline-none focus:ring-1 focus:ring-primary-500" autofocus />
                            </div>
                            <span v-else @click="mod.editing = true" class="flex-1 text-sm font-medium text-default dark:text-default cursor-pointer hover:text-primary-600">{{ mod.title }}</span>
                            <span class="text-xs text-subtle">{{ mod.lessons.length }} lesson{{ mod.lessons.length !== 1 ? 's' : '' }}</span>
                            <button @click="addLesson(mod)" class="text-xs text-primary-600 hover:text-primary-700">+ Lesson</button>
                            <button @click="removeModule(mi)" class="text-subtle hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Lessons -->
                        <div v-if="mod.lessons.length" class="border-t border-base dark:border-base divide-y divide-surface-100 dark:divide-surface-600/50">
                            <div v-for="(les, li) in mod.lessons" :key="les._id"
                                draggable="true"
                                @dragstart="onLesDragStart(mi, li)"
                                @dragover="onLesDragOver($event, mi, li)"
                                class="flex items-center gap-3 pl-8 pr-3 py-2.5 bg-surface">
                                <svg class="h-3.5 w-3.5 cursor-grab text-subtle shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <!-- Type icon -->
                                <span class="text-xs rounded px-1.5 py-0.5"
                                    :class="les.type === 'video' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : les.type === 'pdf' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' : 'bg-surface-3 dark:bg-surface-2 text-muted dark:text-muted'">
                                    {{ les.type.toUpperCase() }}
                                </span>
                                <div v-if="les.editing" class="flex flex-1 items-center gap-2">
                                    <input v-model="les.title" @blur="les.editing = false" @keydown.enter="les.editing = false" type="text"
                                        class="flex-1 rounded border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-2 py-0.5 text-sm text-default dark:text-default focus:outline-none focus:ring-1 focus:ring-primary-500" autofocus />
                                    <select v-model="les.type" class="rounded border border-base dark:border-base bg-surface-2 dark:bg-surface-2 px-1 py-0.5 text-xs text-muted dark:text-default">
                                        <option value="video">Video</option>
                                        <option value="pdf">PDF</option>
                                        <option value="text">Text</option>
                                    </select>
                                </div>
                                <span v-else @click="les.editing = true" class="flex-1 text-sm text-muted dark:text-default cursor-pointer hover:text-primary-600">{{ les.title }}</span>
                                <label class="flex items-center gap-1 text-xs text-subtle cursor-pointer">
                                    <input type="checkbox" v-model="les.is_free" class="rounded border-base text-primary-600 focus:ring-primary-500" />
                                    Free
                                </label>
                                <button @click="removeLesson(mod, li)" class="text-subtle hover:text-red-500">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-subtle">Drag modules and lessons to reorder. After creating the course, you can upload lesson files from the course detail page.</p>
            </div>

            <!-- Step 3: Pricing -->
            <div v-if="step === 3" class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted">Pricing & Publication</h2>

                <div class="flex items-center gap-3 p-4 rounded-lg bg-surface-2 dark:bg-surface-2/50 border border-base dark:border-base">
                    <input id="is_free" type="checkbox" v-model="form.is_free" class="h-4 w-4 rounded border-base text-primary-600 focus:ring-primary-500" />
                    <label for="is_free" class="text-sm font-medium text-muted dark:text-default cursor-pointer">Make this course free</label>
                </div>

                <div v-if="!form.is_free">
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Price (USD) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-subtle">$</span>
                        <input v-model="form.price" type="number" min="0" step="0.01"
                            class="w-full rounded-lg border border-base dark:border-base bg-surface-2 pl-7 pr-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <p v-if="form.errors.price" class="mt-1 text-xs text-red-500">{{ form.errors.price }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Total Duration (minutes)</label>
                    <input v-model="form.duration" type="number" min="1" placeholder="120"
                        class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Publication Status</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label v-for="opt in [{ v: 'draft', label: 'Save as Draft', desc: 'Not visible to students' }, { v: 'published', label: 'Publish Now', desc: 'Visible immediately' }]"
                            :key="opt.v"
                            :class="['flex items-start gap-3 rounded-lg border-2 p-3 cursor-pointer transition-colors', form.status === opt.v ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-base dark:border-base hover:border-primary-300']">
                            <input type="radio" :value="opt.v" v-model="form.status" class="mt-0.5 text-primary-600" />
                            <div>
                                <p class="text-sm font-medium text-default dark:text-default">{{ opt.label }}</p>
                                <p class="text-xs text-subtle">{{ opt.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Summary -->
                <div class="rounded-lg bg-surface-2 dark:bg-surface-2/50 border border-base dark:border-base p-4 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-subtle">Summary</p>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <span class="text-muted">Title</span><span class="font-medium text-default dark:text-default truncate">{{ form.title || '—' }}</span>
                        <span class="text-muted">Modules</span><span class="font-medium text-default dark:text-default">{{ modules.length }}</span>
                        <span class="text-muted">Lessons</span><span class="font-medium text-default dark:text-default">{{ modules.reduce((s, m) => s + m.lessons.length, 0) }}</span>
                        <span class="text-muted">Price</span><span class="font-medium text-default dark:text-default">{{ form.is_free ? 'Free' : `$${form.price}` }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
                <button v-if="step > 1" @click="step--"
                    class="rounded-lg border border-base dark:border-base px-5 py-2 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">
                    ← Back
                </button>
                <div v-else></div>

                <div class="flex gap-3">
                    <button v-if="step < totalSteps" @click="step++" :disabled="!canNext"
                        class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        Continue →
                    </button>
                    <button v-else @click="submit" :disabled="form.processing"
                        class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Creating…' : 'Create Course' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
