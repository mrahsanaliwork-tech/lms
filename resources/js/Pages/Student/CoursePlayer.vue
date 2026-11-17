<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    course:         Object,
    modules:        Array,
    currentLesson:  Object,
    prevLessonId:   Number,
    nextLessonId:   Number,
    progress:       Number,
    completedIds:   Array,
    completeRoute:  String,
})

const sidebarOpen  = ref(true)
const completing   = ref(false)
const completedSet = ref(new Set(props.completedIds))
const localProgress = ref(props.progress)

// Track expanded modules
const expandedModules = ref(new Set(
    props.modules
        .filter(m => m.lessons.some(l => l.id === props.currentLesson.id))
        .map(m => m.id)
))

function toggleModule(id) {
    expandedModules.value.has(id) ? expandedModules.value.delete(id) : expandedModules.value.add(id)
}

function goToLesson(lessonId) {
    router.get(route('student.courses.learn.lesson', {
        course: props.course.id,
        lesson: lessonId,
    }), {}, { preserveScroll: false })
}

async function markComplete() {
    if (completing.value || completedSet.value.has(props.currentLesson.id)) return
    completing.value = true
    router.post(props.completeRoute, { time_spent: 0 }, {
        preserveState: true,
        onSuccess: (page) => {
            completedSet.value.add(props.currentLesson.id)
            // Go to next lesson automatically if available
            if (props.nextLessonId) {
                setTimeout(() => goToLesson(props.nextLessonId), 600)
            }
        },
        onFinish: () => { completing.value = false },
    })
}

const isCompleted = computed(() => completedSet.value.has(props.currentLesson.id))

// Content type helpers
const isVideo = computed(() => {
    const t = props.currentLesson.type
    return t === 'video' || t === 'youtube' || t === 'vimeo'
})
const isPdf = computed(() => props.currentLesson.type === 'pdf')
const isText = computed(() => props.currentLesson.type === 'text' || props.currentLesson.type === 'article')

function youtubeEmbed(url) {
    if (!url) return null
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/]+)/)
    return m ? `https://www.youtube.com/embed/${m[1]}?rel=0&modestbranding=1` : url
}

function lessonIcon(type) {
    const icons = {
        video:   'M15 10l4.553-2.07A1 1 0 0121 8.845v6.31a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z',
        youtube: 'M15 10l4.553-2.07A1 1 0 0121 8.845v6.31a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z',
        pdf:     'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        text:    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        article: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    }
    return icons[type] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
}

function formatDuration(mins) {
    if (!mins) return ''
    if (mins < 60) return `${mins}m`
    return `${Math.floor(mins / 60)}h ${mins % 60}m`
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-surface-2 dark:bg-surface-950">

        <!-- Sidebar -->
        <aside :class="['flex flex-col bg-surface border-r border-base dark:border-base transition-all duration-300 overflow-hidden',
            sidebarOpen ? 'w-72 min-w-72' : 'w-0']">

            <!-- Course title header -->
            <div class="p-4 border-b border-base dark:border-base flex-shrink-0">
                <Link :href="route('student.courses.index')"
                    class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 hover:underline mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    My Courses
                </Link>
                <h2 class="text-sm font-semibold text-default dark:text-default leading-tight line-clamp-2">{{ course.title }}</h2>
                <!-- Overall progress bar -->
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-muted dark:text-muted mb-1">
                        <span>Course progress</span>
                        <span>{{ localProgress }}%</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-surface-3 dark:bg-surface-2">
                        <div class="h-1.5 rounded-full bg-primary-500 transition-all duration-500"
                            :style="{ width: localProgress + '%' }" />
                    </div>
                </div>
            </div>

            <!-- Lesson tree -->
            <div class="flex-1 overflow-y-auto py-2">
                <div v-for="mod in modules" :key="mod.id">
                    <!-- Module header -->
                    <button @click="toggleModule(mod.id)"
                        class="w-full flex items-center justify-between px-4 py-2.5 text-left hover:bg-surface-2 dark:hover:bg-surface-800 transition-colors">
                        <span class="text-xs font-semibold text-muted dark:text-muted uppercase tracking-wide">{{ mod.title }}</span>
                        <svg :class="['w-4 h-4 text-subtle transition-transform', expandedModules.has(mod.id) ? 'rotate-180' : '']"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Lessons list -->
                    <div v-show="expandedModules.has(mod.id)">
                        <button v-for="lesson in mod.lessons" :key="lesson.id"
                            @click="goToLesson(lesson.id)"
                            :class="['w-full flex items-start gap-3 px-4 py-2.5 text-left transition-colors',
                                lesson.id === currentLesson.id
                                    ? 'bg-primary-50 dark:bg-primary-900/20 border-r-2 border-primary-500'
                                    : 'hover:bg-surface-2 dark:hover:bg-surface-800']">

                            <!-- Completed/active indicator -->
                            <div class="mt-0.5 flex-shrink-0">
                                <div v-if="completedSet.has(lesson.id)"
                                    class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div v-else-if="lesson.id === currentLesson.id"
                                    class="w-5 h-5 rounded-full border-2 border-primary-500 bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-primary-500" />
                                </div>
                                <div v-else class="w-5 h-5 rounded-full border-2 border-base dark:border-base" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <p :class="['text-xs leading-snug', lesson.id === currentLesson.id
                                    ? 'font-semibold text-primary-700 dark:text-primary-400'
                                    : 'text-muted dark:text-muted']">
                                    {{ lesson.title }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-3 h-3 text-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="lessonIcon(lesson.type)"/>
                                    </svg>
                                    <span class="text-xs text-subtle dark:text-subtle capitalize">{{ lesson.type }}</span>
                                    <span v-if="lesson.duration" class="text-xs text-subtle dark:text-subtle">· {{ formatDuration(lesson.duration) }}</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Top bar -->
            <div class="flex items-center gap-3 px-4 py-3 border-b border-base dark:border-base bg-surface flex-shrink-0">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="rounded-lg p-2 text-muted hover:bg-surface-3 dark:hover:bg-surface-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <h1 class="text-sm font-semibold text-default dark:text-default truncate">{{ currentLesson.title }}</h1>
                    <p class="text-xs text-muted dark:text-muted capitalize">{{ currentLesson.type }}</p>
                </div>

                <!-- Mark complete button -->
                <button v-if="!isCompleted"
                    @click="markComplete"
                    :disabled="completing"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 px-4 py-2 text-sm font-medium text-white transition-colors">
                    <svg v-if="!completing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ completing ? 'Saving…' : 'Mark Complete' }}
                </button>
                <div v-else class="inline-flex items-center gap-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Completed
                </div>
            </div>

            <!-- Lesson content -->
            <div class="flex-1 overflow-y-auto">

                <!-- Video -->
                <div v-if="isVideo && currentLesson.content_url"
                    class="w-full bg-black aspect-video">
                    <iframe
                        :src="currentLesson.type === 'youtube' || currentLesson.type === 'video'
                            ? youtubeEmbed(currentLesson.content_url)
                            : currentLesson.content_url"
                        class="w-full h-full"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    />
                </div>

                <!-- PDF viewer -->
                <div v-else-if="isPdf && currentLesson.content_url"
                    class="w-full h-full min-h-[70vh]">
                    <iframe :src="currentLesson.content_url + '#toolbar=1'"
                        class="w-full h-full min-h-[70vh]"
                        type="application/pdf" />
                </div>

                <!-- Text / article -->
                <div v-else-if="isText && currentLesson.content_url" class="max-w-3xl mx-auto p-8">
                    <div class="prose dark:prose-invert max-w-none" v-html="currentLesson.content_url" />
                </div>

                <!-- Fallback placeholder -->
                <div v-else class="max-w-2xl mx-auto p-12 text-center">
                    <div class="rounded-2xl border-2 border-dashed border-base dark:border-base p-12">
                        <svg class="mx-auto w-16 h-16 text-subtle dark:text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="lessonIcon(currentLesson.type)"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-muted dark:text-muted mb-1">{{ currentLesson.title }}</h3>
                        <p class="text-sm text-muted dark:text-muted capitalize">{{ currentLesson.type }} lesson</p>
                        <p v-if="!currentLesson.content_url" class="text-sm text-subtle mt-2">Content coming soon.</p>
                    </div>
                </div>

                <!-- Lesson title + description area -->
                <div class="max-w-4xl mx-auto px-6 py-6 border-t border-surface-100 dark:border-surface-800">
                    <h2 class="text-xl font-semibold text-default dark:text-default mb-2">{{ currentLesson.title }}</h2>
                    <div class="flex items-center gap-3 text-sm text-muted dark:text-muted">
                        <span class="capitalize">{{ currentLesson.type }}</span>
                        <span v-if="currentLesson.duration">· {{ formatDuration(currentLesson.duration) }}</span>
                    </div>
                </div>
            </div>

            <!-- Bottom navigation -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-base dark:border-base bg-surface flex-shrink-0">
                <button v-if="prevLessonId"
                    @click="goToLesson(prevLessonId)"
                    class="inline-flex items-center gap-2 rounded-lg border border-base dark:border-base px-4 py-2 text-sm font-medium text-muted dark:text-muted hover:bg-surface-2 dark:hover:bg-surface-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Previous
                </button>
                <div v-else />

                <button v-if="nextLessonId"
                    @click="goToLesson(nextLessonId)"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 px-5 py-2 text-sm font-medium text-white transition-colors">
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div v-else-if="isCompleted"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                    Course Complete!
                </div>
            </div>
        </div>
    </div>
</template>
