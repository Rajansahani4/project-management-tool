<script setup>
import { reactive, ref } from 'vue'
import { useAuth } from '@/composables/useAuth.js'
import { User, Mail, Lock, Eye, EyeOff, AlertCircle, CheckCircle, Loader2 } from 'lucide-vue-next'

const { register, loading, errors, generalError } = useAuth()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const showPassword = ref(false)
const showConfirm  = ref(false)
const localErrors  = reactive({ password_confirmation: '' })

async function handleSubmit() {
  localErrors.password_confirmation = ''
  if (form.password !== form.password_confirmation) {
    localErrors.password_confirmation = 'Passwords do not match'
    return
  }
  await register(form)
}

const passwordStrength = (pwd) => {
  if (!pwd) return null
  if (pwd.length < 6) return { level: 1, label: 'Weak', color: 'bg-red-400' }
  if (pwd.length < 10 || !/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd)) return { level: 2, label: 'Fair', color: 'bg-amber-400' }
  return { level: 3, label: 'Strong', color: 'bg-emerald-500' }
}
</script>

<template>
  <div class="px-6 py-6">
    <!-- Header -->
    <div class="mb-5">
      <h2 class="text-xl font-bold text-gray-900 tracking-tight">Create your account</h2>
      <p class="mt-1 text-sm text-gray-500">Get started with ProjectFlow today — it's free</p>
    </div>

    <!-- General Error -->
    <div
      v-if="generalError"
      class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3.5"
    >
      <AlertCircle class="h-4 w-4 text-red-500 mt-0.5 shrink-0" />
      <p class="text-sm text-red-700 font-medium">{{ generalError }}</p>
    </div>

    <form class="space-y-3" novalidate @submit.prevent="handleSubmit">
      <!-- Full Name -->
      <div class="space-y-1">
        <label class="block text-sm font-semibold text-gray-700">Full name</label>
        <div class="relative">
          <User class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input
            v-model="form.name"
            type="text"
            placeholder="Alice Johnson"
            autocomplete="name"
            :class="[
              'block w-full rounded-xl border bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all duration-150',
              'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:bg-white',
              errors?.name ? 'border-red-400 bg-red-50/50' : 'border-gray-200 hover:border-gray-300',
            ]"
          />
        </div>
        <p v-if="errors?.name" class="text-xs text-red-600 flex items-center gap-1">
          <AlertCircle class="h-3 w-3" /> {{ errors.name[0] }}
        </p>
      </div>

      <!-- Email -->
      <div class="space-y-1">
        <label class="block text-sm font-semibold text-gray-700">Email address</label>
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
      <div class="space-y-1">
        <label class="block text-sm font-semibold text-gray-700">Password</label>
        <div class="relative">
          <Lock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Min. 8 characters"
            autocomplete="new-password"
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
        <!-- Password strength indicator -->
        <div v-if="form.password" class="space-y-1">
          <div class="flex gap-1">
            <div
              v-for="i in 3"
              :key="i"
              :class="[
                'h-1 flex-1 rounded-full transition-all duration-300',
                passwordStrength(form.password) && i <= passwordStrength(form.password).level
                  ? passwordStrength(form.password).color
                  : 'bg-gray-200',
              ]"
            ></div>
          </div>
          <p class="text-xs text-gray-500">
            Strength: <span :class="{ 'text-red-500': passwordStrength(form.password)?.level === 1, 'text-amber-500': passwordStrength(form.password)?.level === 2, 'text-emerald-600': passwordStrength(form.password)?.level === 3 }" class="font-medium">{{ passwordStrength(form.password)?.label }}</span>
          </p>
        </div>
        <p v-if="errors?.password" class="text-xs text-red-600 flex items-center gap-1">
          <AlertCircle class="h-3 w-3" /> {{ errors.password[0] }}
        </p>
      </div>

      <!-- Confirm Password -->
      <div class="space-y-1">
        <label class="block text-sm font-semibold text-gray-700">Confirm password</label>
        <div class="relative">
          <Lock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input
            v-model="form.password_confirmation"
            :type="showConfirm ? 'text' : 'password'"
            placeholder="Repeat your password"
            autocomplete="new-password"
            :class="[
              'block w-full rounded-xl border bg-gray-50 pl-10 pr-10 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all duration-150',
              'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:bg-white',
              (localErrors.password_confirmation || errors?.password_confirmation) ? 'border-red-400 bg-red-50/50' : 'border-gray-200 hover:border-gray-300',
            ]"
          />
          <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
            <CheckCircle
              v-if="form.password_confirmation && form.password === form.password_confirmation"
              class="h-4 w-4 text-emerald-500"
            />
            <button
              type="button"
              class="p-1 text-gray-400 hover:text-gray-600 transition-colors rounded-md"
              @click="showConfirm = !showConfirm"
            >
              <EyeOff v-if="showConfirm" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>
        </div>
        <p v-if="localErrors.password_confirmation || errors?.password_confirmation" class="text-xs text-red-600 flex items-center gap-1">
          <AlertCircle class="h-3 w-3" />
          {{ localErrors.password_confirmation || errors?.password_confirmation?.[0] }}
        </p>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        :disabled="loading"
        class="relative mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-150 hover:from-indigo-500 hover:to-violet-500 hover:shadow-indigo-500/40 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
        {{ loading ? 'Creating account…' : 'Create Account' }}
      </button>

      <p class="text-center text-xs text-gray-400 leading-relaxed">
        By creating an account, you agree to our
        <a href="#" class="text-indigo-600 hover:underline">Terms of Service</a>
        and
        <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>.
      </p>
    </form>

    <!-- Divider -->
    <div class="my-4 flex items-center gap-3">
      <div class="h-px flex-1 bg-gray-100"></div>
      <span class="text-xs text-gray-400 font-medium">Already have an account?</span>
      <div class="h-px flex-1 bg-gray-100"></div>
    </div>

    <!-- Login link -->
    <RouterLink
      to="/login"
      class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all duration-150 hover:bg-gray-100 hover:border-gray-300 active:scale-[0.98]"
    >
      Sign in instead
    </RouterLink>
  </div>
</template>
