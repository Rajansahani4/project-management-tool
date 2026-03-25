<script setup>
import { reactive, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { useUiStore } from '@/stores/ui.js'
import { authApi } from '@/api/auth.js'
import AppButton from '@/components/common/AppButton.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppAvatar from '@/components/common/AppAvatar.vue'
import AppBreadcrumb from '@/components/common/AppBreadcrumb.vue'
import { useAuth } from '@/composables/useAuth.js'
import { User, Lock, Eye, EyeOff, Shield, LayoutDashboard, Settings } from 'lucide-vue-next'

const authStore = useAuthStore()
const ui        = useUiStore()
const auth      = useAuth()

// ── Profile form
const profileForm    = reactive({ name: authStore.user?.name ?? '' })
const profileErrors  = ref({})
const profileSaving  = ref(false)
const profileSuccess = ref(false)

watch(() => authStore.user?.name, (v) => { profileForm.name = v ?? '' })

async function saveProfile() {
  profileErrors.value  = {}
  profileSaving.value  = true
  profileSuccess.value = false
  try {
    const res = await authApi.updateProfile({ name: profileForm.name })
    authStore.user = res.data ?? res
    profileSuccess.value = true
    ui.success('Profile updated successfully!')
    setTimeout(() => { profileSuccess.value = false }, 3000)
  } catch (err) {
    profileErrors.value = err.errors ?? {}
    if (err.message) ui.error(err.message)
  } finally {
    profileSaving.value = false
  }
}

// ── Password form
const pwForm    = reactive({ current_password: '', password: '', password_confirmation: '' })
const pwErrors  = ref({})
const pwSaving  = ref(false)
const showPw    = reactive({ current: false, new: false, confirm: false })
const pwSuccess = ref(false)

async function changePassword() {
  pwErrors.value = {}
  if (pwForm.password !== pwForm.password_confirmation) {
    pwErrors.value = { password_confirmation: ['Passwords do not match'] }
    return
  }
  pwSaving.value = true
  try {
    await authApi.changePassword(pwForm)
    pwSuccess.value = true
    Object.assign(pwForm, { current_password: '', password: '', password_confirmation: '' })
    ui.success('Password changed successfully!')
    setTimeout(() => { pwSuccess.value = false }, 3000)
  } catch (err) {
    pwErrors.value = err.errors ?? {}
    if (!Object.keys(pwErrors.value).length && err.message) ui.error(err.message)
  } finally {
    pwSaving.value = false
  }
}

const breadcrumbs = [
  { label: 'Dashboard', to: { name: 'dashboard' }, icon: LayoutDashboard },
  { label: 'Settings', icon: Settings },
]
</script>

<template>
  <div class="max-w-3xl mx-auto px-6 py-6 space-y-6">
    <!-- Breadcrumb -->
    <AppBreadcrumb :items="breadcrumbs" />

    <div>
      <h1 class="text-xl font-bold text-[#172B4D]">Profile &amp; Settings</h1>
      <p class="text-sm text-[#6B778C]">Manage your personal information and security preferences</p>
    </div>

    <!-- Profile Card -->
    <div class="rounded-xl border border-[#DFE1E6] bg-white shadow-sm overflow-hidden">
      <div class="border-b border-[#DFE1E6] px-6 py-4">
        <div class="flex items-center gap-2">
          <User class="h-4 w-4 text-[#6B778C]" />
          <h2 class="text-sm font-semibold text-[#172B4D]">Personal Information</h2>
        </div>
      </div>

      <div class="p-6">
        <!-- Avatar + info -->
        <div class="mb-6 flex items-center gap-4">
          <AppAvatar :name="authStore.user?.name ?? ''" size="xl" />
          <div>
            <p class="font-semibold text-[#172B4D]">{{ authStore.user?.name }}</p>
            <p class="text-sm text-[#6B778C]">{{ authStore.user?.email }}</p>
          </div>
        </div>

        <div v-if="profileSuccess" class="mb-4 rounded-lg border border-[#ABF5D1] bg-[#E3FCEF] px-4 py-3 text-sm font-medium text-[#006644]">
          Profile updated successfully
        </div>

        <form class="space-y-4 max-w-md" @submit.prevent="saveProfile">
          <AppInput
            v-model="profileForm.name"
            label="Full name"
            placeholder="Your name"
            :error="profileErrors?.name?.[0]"
            required
          />

          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-[#172B4D]">Email address</label>
            <input
              :value="authStore.user?.email"
              disabled
              class="block w-full rounded border border-[#DFE1E6] bg-[#F4F5F7] px-3 py-1.5 text-sm text-[#6B778C] cursor-not-allowed"
            />
            <p class="text-xs text-[#6B778C]">Email cannot be changed</p>
          </div>

          <div class="pt-2">
            <AppButton type="submit" :loading="profileSaving">Save Changes</AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- Password Card -->
    <div class="rounded-xl border border-[#DFE1E6] bg-white shadow-sm overflow-hidden">
      <div class="border-b border-[#DFE1E6] px-6 py-4">
        <div class="flex items-center gap-2">
          <Lock class="h-4 w-4 text-[#6B778C]" />
          <h2 class="text-sm font-semibold text-[#172B4D]">Change Password</h2>
        </div>
      </div>

      <div class="p-6">
        <div v-if="pwSuccess" class="mb-4 rounded-lg border border-[#ABF5D1] bg-[#E3FCEF] px-4 py-3 text-sm font-medium text-[#006644]">
          Password changed successfully
        </div>

        <form class="space-y-4 max-w-md" @submit.prevent="changePassword">
          <!-- Current password -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-[#172B4D]">Current password <span class="text-red-500">*</span></label>
            <div class="relative">
              <Lock class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
              <input
                v-model="pwForm.current_password"
                :type="showPw.current ? 'text' : 'password'"
                placeholder="Your current password"
                autocomplete="current-password"
                :class="[
                  'block w-full rounded border bg-white pl-9 pr-10 py-1.5 text-sm text-[#172B4D]',
                  'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
                  pwErrors?.current_password ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
                ]"
              />
              <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6B778C] hover:text-[#172B4D]" @click="showPw.current = !showPw.current">
                <EyeOff v-if="showPw.current" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
            <p v-if="pwErrors?.current_password" class="text-xs text-[#DE350B]">{{ pwErrors.current_password[0] }}</p>
          </div>

          <!-- New password -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-[#172B4D]">New password <span class="text-red-500">*</span></label>
            <div class="relative">
              <Shield class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
              <input
                v-model="pwForm.password"
                :type="showPw.new ? 'text' : 'password'"
                placeholder="Min. 8 characters"
                autocomplete="new-password"
                :class="[
                  'block w-full rounded border bg-white pl-9 pr-10 py-1.5 text-sm text-[#172B4D]',
                  'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
                  pwErrors?.password ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
                ]"
              />
              <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6B778C] hover:text-[#172B4D]" @click="showPw.new = !showPw.new">
                <EyeOff v-if="showPw.new" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
            <p v-if="pwErrors?.password" class="text-xs text-[#DE350B]">{{ pwErrors.password[0] }}</p>
          </div>

          <!-- Confirm new password -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-[#172B4D]">Confirm new password <span class="text-red-500">*</span></label>
            <div class="relative">
              <Shield class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
              <input
                v-model="pwForm.password_confirmation"
                :type="showPw.confirm ? 'text' : 'password'"
                placeholder="Repeat new password"
                autocomplete="new-password"
                :class="[
                  'block w-full rounded border bg-white pl-9 pr-10 py-1.5 text-sm text-[#172B4D]',
                  'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
                  pwErrors?.password_confirmation ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
                ]"
              />
              <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6B778C] hover:text-[#172B4D]" @click="showPw.confirm = !showPw.confirm">
                <EyeOff v-if="showPw.confirm" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
            <p v-if="pwErrors?.password_confirmation" class="text-xs text-[#DE350B]">{{ pwErrors.password_confirmation[0] }}</p>
          </div>

          <div class="pt-2">
            <AppButton type="submit" :loading="pwSaving">Update Password</AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- Danger Zone -->
    <div class="rounded-xl border-2 border-[#FFBDAD] bg-white shadow-sm overflow-hidden">
      <div class="border-b border-[#FFBDAD] bg-[#FFEBE6] px-6 py-4">
        <h2 class="text-sm font-semibold text-[#BF2600]">Danger Zone</h2>
      </div>
      <div class="flex items-center justify-between p-6">
        <div>
          <p class="text-sm font-medium text-[#172B4D]">Sign out of your account</p>
          <p class="text-xs text-[#6B778C]">You'll need to sign in again to access your projects</p>
        </div>
        <AppButton variant="danger" @click="auth.logout()">Sign Out</AppButton>
      </div>
    </div>
  </div>
</template>
