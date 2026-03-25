<script setup>
import { reactive, ref } from 'vue'
import { useAuth } from '@/composables/useAuth.js'
import AppButton from '@/components/common/AppButton.vue'
import AppInput from '@/components/common/AppInput.vue'
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next'

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
</script>

<template>
  <div class="px-8 py-8">
    <h2 class="mb-1 text-xl font-bold text-[#172B4D]">Create your account</h2>
    <p class="mb-6 text-sm text-[#6B778C]">Get started with ProjectFlow today — it's free</p>

    <div v-if="generalError" class="mb-4 rounded-lg border border-[#FFBDAD] bg-[#FFEBE6] px-4 py-3 text-sm text-[#BF2600]">
      {{ generalError }}
    </div>

    <form class="space-y-4" novalidate @submit.prevent="handleSubmit">
      <AppInput
        v-model="form.name"
        label="Full name"
        placeholder="Alice Johnson"
        :error="errors?.name?.[0]"
        required
        autocomplete="name"
      >
        <template #icon><User class="h-4 w-4" /></template>
      </AppInput>

      <AppInput
        v-model="form.email"
        label="Email address"
        type="email"
        placeholder="you@example.com"
        :error="errors?.email?.[0]"
        required
        autocomplete="email"
      >
        <template #icon><Mail class="h-4 w-4" /></template>
      </AppInput>

      <!-- Password -->
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-[#172B4D]">
          Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <Lock class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Min. 8 characters"
            autocomplete="new-password"
            :class="[
              'block w-full rounded border bg-white pl-9 pr-10 py-1.5 text-sm text-[#172B4D] placeholder-[#97A0AF] transition-colors',
              'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
              errors?.password ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
            ]"
          />
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 text-[#6B778C] hover:text-[#172B4D]" @click="showPassword = !showPassword">
            <EyeOff v-if="showPassword" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>
        <p v-if="errors?.password" class="text-xs text-[#DE350B]">{{ errors.password[0] }}</p>
      </div>

      <!-- Confirm Password -->
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-[#172B4D]">
          Confirm password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <Lock class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
          <input
            v-model="form.password_confirmation"
            :type="showConfirm ? 'text' : 'password'"
            placeholder="Repeat your password"
            autocomplete="new-password"
            :class="[
              'block w-full rounded border bg-white pl-9 pr-10 py-1.5 text-sm text-[#172B4D] placeholder-[#97A0AF] transition-colors',
              'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
              (localErrors.password_confirmation || errors?.password_confirmation) ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
            ]"
          />
          <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 text-[#6B778C] hover:text-[#172B4D]" @click="showConfirm = !showConfirm">
            <EyeOff v-if="showConfirm" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>
        <p v-if="localErrors.password_confirmation || errors?.password_confirmation" class="text-xs text-[#DE350B]">
          {{ localErrors.password_confirmation || errors?.password_confirmation?.[0] }}
        </p>
      </div>

      <AppButton type="submit" :loading="loading" full-width size="lg" class="mt-2">
        Create Account
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-[#6B778C]">
      Already have an account?
      <RouterLink to="/login" class="font-medium text-primary-600 hover:underline">Sign in</RouterLink>
    </p>
  </div>
</template>
