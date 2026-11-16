<template>
    <AuthLayout>
        <div>
            <div class="mb-8">
                <h1 class="text-2xl font-bold font-display text-default tracking-tight text-balance">
                    Welcome back
                </h1>
                <p class="text-sm text-muted mt-1.5">
                    Sign in to your account to continue learning.
                </p>
            </div>

            <!-- Error banner -->
            <div v-if="form.errors.email && !form.errors.password"
                 class="flex items-center gap-2.5 p-3.5 mb-5 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span class="text-sm text-red-700 dark:text-red-300">{{ form.errors.email }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-default mb-1.5">
                        Email address
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
                        :class="inputClass(form.errors.email)"
                        required
                    />
                    <p v-if="form.errors.email && form.errors.password" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-sm font-medium text-default">Password</label>
                        <a href="#" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            :class="inputClass(form.errors.password)"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-subtle hover:text-muted transition-colors"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Remember me -->
                <div class="flex items-center gap-2.5">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="w-4 h-4 rounded border-base text-primary-600 focus:ring-primary-500 focus:ring-offset-0 bg-surface"
                    />
                    <label for="remember" class="text-sm text-muted select-none cursor-pointer">
                        Keep me signed in for 30 days
                    </label>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed mt-2"
                >
                    <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>

            <!-- Register link -->
            <p class="mt-6 text-center text-sm text-muted">
                Don't have an account?
                <Link :href="route('register')" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline ml-1">
                    Create one free
                </Link>
            </p>
        </div>
    </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const showPassword = ref(false);

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
}

function inputClass(error) {
    return [
        'w-full px-3.5 py-2.5 text-sm rounded-xl border bg-surface text-default placeholder:text-subtle',
        'focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors',
        error
            ? 'border-red-400 focus:ring-red-500/20 focus:border-red-400'
            : 'border-base focus:ring-primary-500/20 focus:border-primary-400',
    ].flat().join(' ');
}
</script>
