<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    course: Object,
    instructors: Array,
    categories: Array,
})

const form = useForm({
    title: props.course.title ?? '',
    description: props.course.description ?? '',
    instructor_id: props.course.instructor_id ?? '',
    category_id: props.course.category_id ?? '',
    thumbnail: null,
    price: props.course.price ?? 0,
    is_free: props.course.is_free ?? false,
    level: props.course.level ?? 'beginner',
    language: props.course.language ?? 'en',
    prerequisites: props.course.prerequisites ?? [],
    duration: props.course.duration ?? '',
    status: props.course.status ?? 'draft',
})

const thumbnailPreview = ref(props.course.thumbnail)
function onThumbnail(e) {
    const file = e.target.files[0]
    form.thumbnail = file
    if (file) thumbnailPreview.value = URL.createObjectURL(file)
}

const prereqInput = ref('')
function addPrereq() {
    const v = prereqInput.value.trim()
    if (v && !form.prerequisites.includes(v)) form.prerequisites.push(v)
    prereqInput.value = ''
}
function removePrereq(i) { form.prerequisites.splice(i, 1) }

function submit() {
    form.post(route('admin.courses.update', props.course.id), {
        headers: { 'X-HTTP-Method-Override': 'PATCH' },
    })
}
</script>

<template>
    <AdminLayout :title="`Edit — ${course.title}`">
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="flex items-center gap-3">
                <a :href="route('admin.courses.show', course.id)" class="rounded p-1 text-subtle hover:text-muted dark:hover:text-surface-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-semibold text-default dark:text-default">Edit Course</h1>
            </div>

            <form @submit.prevent="submit" class="rounded-xl border border-base dark:border-base bg-surface p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Title <span class="text-red-500">*</span></label>
                    <input v-model="form.title" type="text" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :class="form.errors.title ? 'border-red-400' : 'border-base dark:border-base bg-surface-2 text-default dark:text-default'" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Description</label>
                    <textarea v-model="form.description" rows="4" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Instructor <span class="text-red-500">*</span></label>
                        <select v-model="form.instructor_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Select instructor</option>
                            <option v-for="i in instructors" :key="i.id" :value="i.id">{{ i.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Category <span class="text-red-500">*</span></label>
                        <select v-model="form.category_id" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Select category</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Level</label>
                        <select v-model="form.level" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Language</label>
                        <select v-model="form.language" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="en">English</option>
                            <option value="ur">Urdu</option>
                            <option value="ar">Arabic</option>
                            <option value="fr">French</option>
                            <option value="es">Spanish</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Duration (minutes)</label>
                        <input v-model="form.duration" type="number" min="1" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <!-- Pricing -->
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm text-muted dark:text-default cursor-pointer">
                        <input type="checkbox" v-model="form.is_free" class="rounded border-base text-primary-600 focus:ring-primary-500" />
                        Free course
                    </label>
                    <div v-if="!form.is_free">
                        <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Price (USD)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-subtle">$</span>
                            <input v-model="form.price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-base dark:border-base bg-surface-2 pl-7 pr-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
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
                        <input type="file" accept="image/*" @change="onThumbnail" class="block text-sm text-muted dark:text-default file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary-50 dark:file:bg-primary-900 file:text-primary-700 dark:file:text-primary-300 hover:file:bg-primary-100" />
                    </div>
                </div>

                <!-- Prerequisites -->
                <div>
                    <label class="block text-sm font-medium text-muted dark:text-muted mb-1">Prerequisites</label>
                    <div class="flex gap-2 mb-2">
                        <input v-model="prereqInput" @keydown.enter.prevent="addPrereq" type="text" placeholder="Add prerequisite…" class="flex-1 rounded-lg border border-base dark:border-base bg-surface-2 px-3 py-2 text-sm text-default dark:text-default focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <button type="button" @click="addPrereq" class="rounded-lg bg-primary-50 dark:bg-primary-900 px-3 py-2 text-sm text-primary-700 dark:text-primary-300">Add</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(p, i) in form.prerequisites" :key="i" class="inline-flex items-center gap-1 rounded-full bg-surface-3 dark:bg-surface-2 px-3 py-1 text-xs text-muted dark:text-default">
                            {{ p }}
                            <button @click="removePrereq(i)" class="ml-1 text-subtle hover:text-red-500">×</button>
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a :href="route('admin.courses.show', course.id)" class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-700">Cancel</a>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
