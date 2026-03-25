<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useAuth } from '@/composables/useAuth.js'
import { useUiStore } from '@/stores/ui.js'
import AppAvatar from '@/components/common/AppAvatar.vue'
import { Menu, Bell, HelpCircle, Settings, LogOut, ChevronDown } from 'lucide-vue-next'

const emit = defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const ui        = useUiStore()
const auth      = useAuth()
const router    = useRouter()

const userMenuOpen = ref(false)

function closeUserMenu() { userMenuOpen.value = false }

async function handleLogout() {
  closeUserMenu()
  await auth.logout()
}

function goToSettings() {
  closeUserMenu()
  router.push({ name: 'settings' })
}
</script>

<template>
  <header class="flex h-[60px] shrink-0 items-center gap-3 border-b border-[#DFE1E6] bg-white px-4 z-20 relative">
    <!-- Mobile hamburger -->
    <button
      class="rounded p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors lg:hidden"
      aria-label="Toggle sidebar"
      @click="emit('toggle-sidebar')"
    >
      <Menu class="h-5 w-5" />
    </button>

    <!-- Logo (mobile only) -->
    <div class="flex items-center gap-2 lg:hidden">
      <div class="flex h-7 w-7 items-center justify-center rounded bg-primary-600 text-white font-bold text-xs">
        PM
      </div>
      <span class="font-semibold text-brand-text-dark text-sm">ProjectFlow</span>
    </div>

    <!-- Breadcrumb / page title slot -->
    <div class="hidden lg:flex flex-1 items-center">
      <slot name="breadcrumb" />
    </div>

    <div class="flex flex-1 lg:flex-none" />

    <!-- Right side actions -->
    <div class="flex items-center gap-1">
      <!-- Notification bell -->
      <button
        class="relative rounded p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors"
        aria-label="Notifications"
      >
        <Bell class="h-5 w-5" />
        <span
          v-if="ui.notifications.length > 0"
          class="absolute right-1.5 top-1.5 flex h-2 w-2 items-center justify-center rounded-full bg-red-500"
        />
      </button>

      <!-- Help -->
      <button
        class="rounded p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors"
        aria-label="Help"
      >
        <HelpCircle class="h-5 w-5" />
      </button>

      <!-- User menu -->
      <div class="relative ml-1">
        <button
          class="flex items-center gap-2 rounded p-1 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1"
          :aria-expanded="userMenuOpen"
          aria-haspopup="true"
          @click="userMenuOpen = !userMenuOpen"
        >
          <AppAvatar :name="authStore.user?.name ?? ''" size="sm" />
          <ChevronDown class="h-3 w-3 text-gray-500 hidden sm:block" />
        </button>

        <!-- Dropdown -->
        <Transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="scale-95 opacity-0 translate-y-1"
          enter-to-class="scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-95 opacity-0"
        >
          <div
            v-if="userMenuOpen"
            class="absolute right-0 top-full mt-1 w-56 rounded-lg border border-[#DFE1E6] bg-white py-1 shadow-panel z-50"
            role="menu"
            @click.stop
          >
            <!-- User info -->
            <div class="border-b border-[#DFE1E6] px-4 py-3">
              <p class="text-sm font-semibold text-brand-text-dark">{{ authStore.user?.name }}</p>
              <p class="truncate text-xs text-brand-text-muted mt-0.5">{{ authStore.user?.email }}</p>
            </div>

            <button
              class="flex w-full items-center gap-3 px-4 py-2 text-sm text-brand-text-dark hover:bg-[#F4F5F7] transition-colors"
              role="menuitem"
              @click="goToSettings"
            >
              <Settings class="h-4 w-4 text-brand-text-muted" />
              Profile & Settings
            </button>

            <div class="border-t border-[#DFE1E6] mt-1 pt-1">
              <button
                class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                role="menuitem"
                @click="handleLogout"
              >
                <LogOut class="h-4 w-4" />
                Sign Out
              </button>
            </div>
          </div>
        </Transition>

        <!-- Click outside to close -->
        <div
          v-if="userMenuOpen"
          class="fixed inset-0 z-40"
          @click="closeUserMenu"
        />
      </div>
    </div>
  </header>
</template>
