<script setup>
import { reactive, ref } from 'vue'
import { useAuth } from '@/composables/useAuth.js'
import { Mail, Lock, Eye, EyeOff, AlertCircle, Loader2 } from 'lucide-vue-next'

const { login, loading, errors, generalError } = useAuth()

const form = reactive({ email: '', password: '' })
const showPassword = ref(false)

async function handleSubmit() {
  await login(form)
}
</script>

<template>
  <div class="px-6 py-6">
    <!-- Header -->
    <div class="mb-5">
      <h2 class="text-xl font-bold text-gray-900 tracking-tight">Welcome back</h2>
      <p class="mt-1 text-sm text-gray-500">Sign in to your account to continue</p>
    </div>

    <!-- General Error -->
    <div
      v-if="generalError"
      class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3.5"
    >
      <AlertCircle class="h-4 w-4 text-red-500 mt-0.5 shrink-0" />
      <p class="text-sm text-red-700 font-medium">{{ generalError }}</p>
    </div>

    <form class="space-y-4" novalidate @submit.prevent="handleSubmit">
      <!-- Email -->
      <div class="space-y-1.5">
        <label class="block text-sm font-semibold text-gray-700">
          Email address
        </label>
        <div class="relative">
          <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
            :class="[
              'block w-full rounded-xl border bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all duration-150',
              'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:bg-white',
              errors?.email ? 'border-red-400 bg-red-50/50' : 'border-gray-200 hover:border-gray-300',
            ]"
          />
        </div>
        <p v-if="errors?.email" class="text-xs text-red-600 flex items-center gap-1">
          <AlertCircle class="h-3 w-3" /> {{ errors.email[0] }}
        </p>
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <label class="block text-sm font-semibold text-gray-700">Password</label>
          <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
            Forgot password?
          </a>
        </div>
        <div class="relative">
          <Lock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="••••••••"
            autocomplete="current-password"
            :class="[
              'block w-full rounded-xl border bg-gray-50 pl-10 pr-10 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all duration-150',
              'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:bg-white',
              errors?.password ? 'border-red-400 bg-red-50/50' : 'border-gray-200 hover:border-gray-300',
            ]"
          />
          <button
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 transition-colors rounded-md"
            @click="showPassword = !showPassword"
          >
            <EyeOff v-if="showPassword" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>
        <p v-if="errors?.password" class="text-xs text-red-600 flex items-center gap-1">
          <AlertCircle class="h-3 w-3" /> {{ errors.password[0] }}
        </p>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        :disabled="loading"
        class="relative mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-150 hover:from-indigo-500 hover:to-violet-500 hover:shadow-indigo-500/40 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        {{ loading ? 'Signing in…' : 'Sign In' }}
      </button>
    </form>

    <!-- Divider -->
    <div class="my-4 flex items-center gap-3">
      <div class="h-px flex-1 bg-gray-100"></div>
      <span class="text-xs text-gray-400 font-medium">New to ProjectFlow?</span>
      <div class="h-px flex-1 bg-gray-100"></div>
    </div>

    <!-- Register link -->
    <RouterLink
      to="/register"
      class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all duration-150 hover:bg-gray-100 hover:border-gray-300 active:scale-[0.98]"
    >
      Create a free account
    </RouterLink>
  </div>
</template>
