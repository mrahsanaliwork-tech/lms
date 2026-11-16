<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    courses: Array,
})

// ── State ──────────────────────────────────────────────────────────
const courseId   = ref('')
const messages   = ref([])   // { role: 'user'|'assistant', content: string, error?: boolean }
const input      = ref('')
const thinking   = ref(false)
const error      = ref('')
const chatEl     = ref(null)
const inputEl    = ref(null)

const courseName = computed(() =>
    props.courses.find(c => c.id === courseId.value)?.title ?? ''
)

const canSend = computed(() => input.value.trim().length > 0 && !thinking.value)

onMounted(() => {
    messages.value.push({
        role: 'assistant',
        content: "Hi! I'm your AI tutor. Ask me anything — a concept you're struggling with, a question about your course material, or anything else you'd like to explore.",
    })
    inputEl.value?.focus()
})

// ── Send ───────────────────────────────────────────────────────────
async function send() {
    const text = input.value.trim()
    if (!text || thinking.value) return

    error.value = ''
    messages.value.push({ role: 'user', content: text })
    input.value = ''
    thinking.value = true
    await scrollBottom()

    // Build message history for API (strip error flag, keep role/content only)
    const history = messages.value
        .filter(m => !m.error)
        .map(({ role, content }) => ({ role, content }))

    try {
        const { data } = await axios.post(route('ai.chat'), {
            messages:  history,
            course_id: courseId.value || null,
        })
        messages.value.push({ role: 'assistant', content: data.result })
    } catch (err) {
        const msg = err.response?.data?.error ?? 'Something went wrong. Please try again.'
        messages.value.push({ role: 'assistant', content: msg, error: true })
    } finally {
        thinking.value = false
        await scrollBottom()
        inputEl.value?.focus()
    }
}

function onKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        send()
    }
}

function clearChat() {
    messages.value = [{
        role: 'assistant',
        content: "Chat cleared. What would you like to explore next?",
    }]
}

async function scrollBottom() {
    await nextTick()
    if (chatEl.value) {
        chatEl.value.scrollTop = chatEl.value.scrollHeight
    }
}

// ── Markdown-lite renderer ─────────────────────────────────────────
// Converts **bold**, `code`, and newlines — no library needed
function renderContent(text) {
    return text
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>')
        .replace(/\n/g, '<br>')
}
</script>

<template>
    <!-- Full-screen chat layout — no sidebar -->
    <div class="flex h-screen flex-col bg-surface-2 dark:bg-surface-950" style="font-family: system-ui, sans-serif;">

        <!-- Header -->
        <header class="flex items-center gap-3 border-b border-base dark:border-surface-800 bg-surface px-4 py-3 shadow-sm">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 shrink-0">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div class="flex-1">
                <h1 class="text-sm font-semibold text-default dark:text-default leading-none">AI Tutor</h1>
                <p class="text-xs text-subtle dark:text-subtle mt-0.5">Powered by Qwen 3.8 · Groq</p>
            </div>

            <!-- Course context selector -->
            <div class="flex items-center gap-2">
                <label class="text-xs text-muted dark:text-muted hidden sm:block">Context:</label>
                <select v-model="courseId"
                    class="rounded-lg border border-base dark:border-base bg-surface px-2 py-1.5 text-xs text-muted dark:text-default focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="">No course</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
            </div>

            <button @click="clearChat" title="Clear chat"
                class="rounded-lg p-1.5 text-subtle hover:text-muted dark:hover:text-surface-200 hover:bg-surface-3 dark:hover:bg-surface-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>

            <a :href="route('student.dashboard')" class="rounded-lg p-1.5 text-subtle hover:text-muted dark:hover:text-surface-200 hover:bg-surface-3 dark:hover:bg-surface-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </header>

        <!-- Course context banner -->
        <div v-if="courseName" class="bg-violet-50 dark:bg-violet-900/20 border-b border-violet-100 dark:border-violet-800 px-4 py-2 text-xs text-violet-700 dark:text-violet-300 flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Tutor has context from: <strong>{{ courseName }}</strong>
        </div>

        <!-- Messages area -->
        <div ref="chatEl" class="flex-1 overflow-y-auto px-4 py-6 space-y-4">
            <div v-for="(msg, i) in messages" :key="i"
                :class="['flex gap-3', msg.role === 'user' ? 'justify-end' : 'justify-start']">

                <!-- AI avatar -->
                <div v-if="msg.role === 'assistant'"
                    :class="['flex h-7 w-7 shrink-0 items-center justify-center rounded-full mt-1', msg.error ? 'bg-red-100 dark:bg-red-900/40' : 'bg-gradient-to-br from-violet-500 to-indigo-600']">
                    <svg v-if="!msg.error" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <svg v-else class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/></svg>
                </div>

                <!-- Bubble -->
                <div :class="['max-w-prose rounded-2xl px-4 py-3 text-sm leading-relaxed',
                    msg.role === 'user'
                        ? 'bg-violet-600 text-white rounded-br-sm'
                        : msg.error
                            ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-bl-sm'
                            : 'bg-surface border border-base dark:border-base text-default dark:text-default rounded-bl-sm shadow-sm'
                ]">
                    <span v-if="msg.role === 'user'">{{ msg.content }}</span>
                    <span v-else v-html="renderContent(msg.content)"></span>
                </div>

                <!-- User avatar -->
                <div v-if="msg.role === 'user'"
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/40 mt-1 text-xs font-semibold text-violet-700 dark:text-violet-300">
                    You
                </div>
            </div>

            <!-- Typing indicator -->
            <div v-if="thinking" class="flex justify-start gap-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 mt-1">
                    <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div class="rounded-2xl rounded-bl-sm bg-surface border border-base dark:border-base px-4 py-3 shadow-sm flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay:0ms"></span>
                    <span class="h-2 w-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay:150ms"></span>
                    <span class="h-2 w-2 rounded-full bg-violet-400 animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>

        <!-- Input area -->
        <div class="border-t border-base dark:border-surface-800 bg-surface px-4 py-3">
            <div class="mx-auto max-w-2xl flex items-end gap-2">
                <div class="flex-1 rounded-xl border border-base dark:border-base bg-surface-2 dark:bg-surface focus-within:border-violet-500 dark:focus-within:border-violet-500 transition-colors">
                    <textarea ref="inputEl" v-model="input" @keydown="onKeydown" rows="1"
                        placeholder="Ask anything…"
                        class="block w-full resize-none bg-transparent px-4 py-3 text-sm text-default dark:text-default placeholder-surface-400 focus:outline-none max-h-32 overflow-y-auto leading-relaxed"
                        style="field-sizing: content; min-height: 44px;"></textarea>
                </div>
                <button @click="send" :disabled="!canSend"
                    :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors', canSend ? 'bg-violet-600 hover:bg-violet-700 text-white' : 'bg-surface-3 dark:bg-surface text-subtle dark:text-muted cursor-not-allowed']">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
            <p class="mt-1.5 text-center text-xs text-subtle dark:text-subtle">
                Enter to send · Shift+Enter for new line · AI can make mistakes — always verify important information
            </p>
        </div>
    </div>
</template>

<style scoped>
.inline-code {
    background: rgba(139, 92, 246, 0.1);
    color: #7c3aed;
    padding: 0 4px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.875em;
}
:deep(.dark) .inline-code {
    background: rgba(139, 92, 246, 0.2);
    color: #a78bfa;
}
</style>
