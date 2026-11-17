<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
    course: Object,
})

const couponCode    = ref('')
const couponResult  = ref(null)
const couponError   = ref('')
const applyingCoupon= ref(false)
const processing    = ref(false)
const error         = ref('')

const finalPrice = computed(() => {
    if (couponResult.value) return couponResult.value.final_amount
    return props.course.price
})

const savings = computed(() => couponResult.value?.discount ?? 0)

async function applyCoupon() {
    couponError.value  = ''
    couponResult.value = null
    applyingCoupon.value = true
    try {
        const { data } = await axios.post(route('student.checkout.coupon', props.course.id), {
            code: couponCode.value,
        })
        couponResult.value = data
    } catch (e) {
        couponError.value = e.response?.data?.error ?? 'Failed to apply coupon.'
    } finally {
        applyingCoupon.value = false
    }
}

function removeCoupon() {
    couponResult.value = null
    couponCode.value   = ''
    couponError.value  = ''
}

async function checkout() {
    error.value     = ''
    processing.value = true
    try {
        const { data } = await axios.post(route('student.checkout.session', props.course.id), {
            coupon_id: couponResult.value?.coupon_id ?? null,
        })
        window.location.href = data.url
    } catch (e) {
        error.value     = e.response?.data?.error ?? 'Could not start checkout. Please try again.'
        processing.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-surface-2 dark:bg-surface-950" style="font-family: system-ui, sans-serif;">
        <div class="mx-auto max-w-xl px-4 py-10 space-y-6">

            <!-- Back -->
            <a :href="route('student.courses.index')" class="inline-flex items-center gap-1 text-sm text-muted hover:text-primary-600 dark:text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to courses
            </a>

            <!-- Course card -->
            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface overflow-hidden">
                <div v-if="course.thumbnail" class="aspect-video w-full overflow-hidden">
                    <img :src="course.thumbnail" :alt="course.title" class="w-full h-full object-cover" />
                </div>
                <div class="p-6 space-y-2">
                    <h1 class="text-xl font-bold text-default dark:text-default">{{ course.title }}</h1>
                    <p v-if="course.instructor" class="text-sm text-muted dark:text-muted">by {{ course.instructor }}</p>
                    <p v-if="course.description" class="text-sm text-muted dark:text-muted leading-relaxed line-clamp-3">{{ course.description }}</p>
                </div>
            </div>

            <!-- Order summary -->
            <div class="rounded-2xl border border-base dark:border-surface-800 bg-surface p-6 space-y-5">
                <h2 class="font-semibold text-default dark:text-default">Order Summary</h2>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted dark:text-muted">Course price</span>
                        <span class="text-default dark:text-default">${{ course.price.toFixed(2) }}</span>
                    </div>
                    <div v-if="savings > 0" class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>Discount ({{ couponResult.code }})</span>
                        <span>-${{ savings.toFixed(2) }}</span>
                    </div>
                    <div class="border-t border-surface-100 dark:border-surface-800 pt-2 flex justify-between font-semibold text-base">
                        <span class="text-default dark:text-default">Total</span>
                        <span class="text-default dark:text-default">${{ finalPrice.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Coupon input -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-muted dark:text-muted">Coupon Code</label>
                    <div v-if="!couponResult" class="flex gap-2">
                        <input v-model="couponCode" type="text" placeholder="Enter code" @keyup.enter="applyCoupon"
                            class="flex-1 rounded-lg border border-base dark:border-base bg-transparent px-3 py-2 text-sm text-default dark:text-default placeholder-surface-400 uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <button @click="applyCoupon" :disabled="!couponCode.trim() || applyingCoupon"
                            class="rounded-lg border border-base dark:border-base px-4 py-2 text-sm font-medium text-muted dark:text-default hover:bg-surface-2 dark:hover:bg-surface-800 disabled:opacity-50">
                            {{ applyingCoupon ? 'Checking…' : 'Apply' }}
                        </button>
                    </div>
                    <div v-else class="flex items-center gap-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-3 py-2">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="flex-1 text-sm text-emerald-700 dark:text-emerald-300 font-medium">{{ couponResult.code }} — ${{ savings.toFixed(2) }} off</span>
                        <button @click="removeCoupon" class="text-emerald-500 hover:text-emerald-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p v-if="couponError" class="text-xs text-red-500">{{ couponError }}</p>
                </div>

                <!-- Error -->
                <div v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ error }}
                </div>

                <!-- Checkout button -->
                <button @click="checkout" :disabled="processing"
                    class="w-full rounded-xl bg-primary-600 py-3.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg v-if="processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ processing ? 'Redirecting to Stripe…' : `Pay $${finalPrice.toFixed(2)} securely` }}
                </button>

                <p class="text-center text-xs text-subtle dark:text-subtle flex items-center justify-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Payments are processed securely by Stripe. We never store card details.
                </p>
            </div>
        </div>
    </div>
</template>
