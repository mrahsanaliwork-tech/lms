<script setup>
const props = defineProps({ payment: Object })
</script>

<template>
    <div class="min-h-screen bg-surface-2 dark:bg-surface-950 flex items-center justify-center px-4" style="font-family: system-ui, sans-serif;">
        <div class="w-full max-w-md text-center space-y-6">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                <svg class="h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-default dark:text-default">Payment Successful!</h1>
                <p class="mt-2 text-muted dark:text-muted">
                    {{ payment ? `You've been enrolled in "${payment.course_title}".` : "You're now enrolled. Happy learning!" }}
                </p>
                <p v-if="payment?.amount" class="mt-1 text-sm text-subtle dark:text-subtle">
                    Amount charged: ${{ Number(payment.amount).toFixed(2) }}
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <a :href="route('student.courses.index')"
                    class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700">
                    Go to My Courses
                </a>
                <a :href="route('student.payment.history')"
                    class="rounded-xl border border-base dark:border-base px-6 py-3 text-sm text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-800">
                    View Payment History
                </a>
            </div>
        </div>
    </div>
</template>
