<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useProjectStore } from '@/stores/projects.js'
import { useAuth } from '@/composables/useAuth.js'
import AppAvatar from '@/components/common/AppAvatar.vue'
import {
  LayoutDashboard, FolderOpen, Settings, LogOut, ChevronDown,
  X, PanelLeftClose, PanelLeftOpen,
} from 'lucide-vue-next'

const props = defineProps({
  collapsed: { type: Boolean, default: false },
})
const emit = defineEmits(['toggle-collapse', 'close'])

const auth         = useAuth()
const authStore    = useAuthStore()
const projectStore = useProjectStore()
const route        = useRoute()

const projectsExpanded = ref(true)

const recentProjects = computed(() => (projectStore.projects ?? []).slice(0, 6))

const navItems = [
  { name: 'dashboard',     label: 'My Work',  icon: LayoutDashboard },
  { name: 'project-index', label: 'Projects', icon: FolderOpen },
  { name: 'settings',      label: 'Settings', icon: Settings },
]

function isActive(name) {
  return route.name === name
}
</script>

<template>
  <div class="flex h-full flex-col">
    <!-- Logo / Header -->
    <div class="flex h-[60px] shrink-0 items-center justify-between px-3 border-b border-white/10">
      <div v-if="!collapsed" class="flex items-center gap-2 min-w-0">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-primary-600 text-white font-bold text-sm">
          PM
        </div>
        <span class="truncate font-semibold text-white text-sm">ProjectFlow</span>
      </div>
      <div v-else class="flex w-full justify-center">
        <div class="flex h-8 w-8 items-center justify-center rounded bg-primary-600 text-white font-bold text-sm">
          PM
        </div>
      </div>
      <button
        v-if="!collapsed"
        class="shrink-0 rounded p-1 text-brand-sidebar-text hover:bg-white/10 hover:text-white transition-colors lg:flex hidden"
        title="Collapse sidebar"
        @click="emit('toggle-collapse')"
      >
        <PanelLeftClose class="h-4 w-4" />
      </button>
      <button
        class="shrink-0 rounded p-1 text-brand-sidebar-text hover:bg-white/10 hover:text-white transition-colors lg:hidden"
        @click="emit('close')"
      >
        <X class="h-4 w-4" />
      </button>
    </div>

    <!-- Expand button when collapsed -->
    <div v-if="collapsed" class="flex justify-center py-2 border-b border-white/10">
      <button
        class="rounded p-1.5 text-brand-sidebar-text hover:bg-white/10 hover:text-white transition-colors hidden lg:flex"
        title="Expand sidebar"
        @click="emit('toggle-collapse')"
      >
        <PanelLeftOpen class="h-4 w-4" />
      </button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">
      <!-- Main nav items -->
      <RouterLink
        v-for="item in navItems"
        :key="item.name"
        :to="{ name: item.name }"
        :class="[
          'flex items-center gap-3 rounded px-2 py-2 text-sm font-medium transition-colors',
          collapsed ? 'justify-center' : '',
          isActive(item.name)
            ? 'bg-brand-sidebar-active text-white'
            : 'text-brand-sidebar-text hover:bg-white/10 hover:text-white',
        ]"
        :title="collapsed ? item.label : undefined"
        @click="emit('close')"
      >
        <component :is="item.icon" class="h-4 w-4 shrink-0" />
        <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
      </RouterLink>

      <!-- Recent Projects section -->
      <div v-if="!collapsed && recentProjects.length > 0" class="mt-4">
        <button
          class="flex w-full items-center justify-between px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-brand-sidebar-text hover:text-white transition-colors"
          @click="projectsExpanded = !projectsExpanded"
        >
          <span>Recent Projects</span>
          <ChevronDown
            :class="['h-3 w-3 transition-transform', projectsExpanded ? 'rotate-0' : '-rotate-90']"
          />
        </button>
        <Transition
          enter-active-class="transition-all duration-150 ease-out"
          enter-from-class="opacity-0 -translate-y-1"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="projectsExpanded" class="mt-1 space-y-0.5">
            <RouterLink
              v-for="project in recentProjects"
              :key="project.id"
              :to="{ name: 'project-show', params: { id: project.id } }"
              :class="[
                'flex items-center gap-2 rounded px-2 py-1.5 text-sm transition-colors',
                route.params.id == project.id
                  ? 'bg-brand-sidebar-active text-white'
                  : 'text-brand-sidebar-text hover:bg-white/10 hover:text-white',
              ]"
              @click="emit('close')"
            >
              <span
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-xs font-bold uppercase"
                :style="{ backgroundColor: `hsl(${(project.name.charCodeAt(0) * 37) % 360}, 60%, 45%)` }"
              >
                {{ project.name.charAt(0) }}
              </span>
              <span class="truncate text-xs">{{ project.name }}</span>
            </RouterLink>
          </div>
        </Transition>
      </div>
    </nav>

    <!-- User profile at bottom -->
    <div class="shrink-0 border-t border-white/10 p-2">
      <button
        :class="[
          'flex w-full items-center gap-3 rounded px-2 py-2 text-sm text-brand-sidebar-text hover:bg-white/10 hover:text-white transition-colors',
          collapsed ? 'justify-center' : '',
        ]"
        :title="collapsed ? 'Logout' : undefined"
        @click="auth.logout()"
      >
        <AppAvatar :name="authStore.user?.name ?? ''" size="sm" />
        <div v-if="!collapsed" class="min-w-0 flex-1 text-left">
          <p class="truncate text-xs font-medium text-white">{{ authStore.user?.name }}</p>
          <p class="truncate text-[11px] text-brand-sidebar-text">{{ authStore.user?.email }}</p>
        </div>
        <LogOut v-if="!collapsed" class="h-4 w-4 shrink-0" />
      </button>
    </div>
  </div>
</template>
