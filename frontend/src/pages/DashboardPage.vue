<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useProjectStore } from '@/stores/projects.js'
import { useTaskStore } from '@/stores/tasks.js'
import AppButton from '@/components/common/AppButton.vue'
import AppBadge from '@/components/common/AppBadge.vue'
import AppAvatar from '@/components/common/AppAvatar.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppEmptyState from '@/components/common/AppEmptyState.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import AppBreadcrumb from '@/components/common/AppBreadcrumb.vue'
import { Plus, FolderOpen, ArrowRight, LayoutDashboard, Sparkles, ClipboardList, CheckCircle2, TrendingUp } from 'lucide-vue-next'

const authStore    = useAuthStore()
const projectStore = useProjectStore()
const router       = useRouter()

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const firstName = computed(() => (authStore.user?.name ?? 'there').split(' ')[0])

const recentProjects = computed(() => (projectStore.projects ?? []).slice(0, 6))

const stats = computed(() => {
  const all = projectStore.projects ?? []
  return [
    { label: 'Total Projects',  value: all.length,                                          icon: FolderOpen,    color: 'bg-blue-50 text-primary-600',  border: 'border-t-primary-600' },
    { label: 'Active Projects', value: all.filter(p => p.status !== 'archived').length,     icon: TrendingUp,    color: 'bg-green-50 text-green-600',   border: 'border-t-green-500'   },
    { label: 'Tasks Assigned',  value: 0,                                                    icon: ClipboardList, color: 'bg-purple-50 text-purple-600', border: 'border-t-purple-500'  },
  ]
})

onMounted(() => {
  if (!projectStore.projects?.length) projectStore.fetchAll()
})
</script>

<template>
  <div class="min-h-full">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-primary-700 via-primary-600 to-primary-500 px-6 py-8 lg:px-8">
      <div class="relative max-w-5xl mx-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <Sparkles class="h-5 w-5 text-yellow-300" />
              <span class="text-sm font-medium text-white/80">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white sm:text-3xl">
              {{ greeting }}, {{ firstName }}! 👋
            </h1>
            <p class="mt-1 text-white/70 text-sm">Here's what's happening with your projects today.</p>
          </div>
          <AppButton size="lg" variant="secondary" @click="router.push({ name: 'project-create' })">
            <Plus class="h-4 w-4" />
            New Project
          </AppButton>
        </div>
      </div>

      <!-- Decorative circles -->
      <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/5" />
      <div class="absolute -right-4 top-16 h-24 w-24 rounded-full bg-white/5" />
    </div>

    <div class="max-w-5xl mx-auto px-6 py-6 lg:px-8 space-y-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-xl border border-[#DFE1E6] bg-white p-5 shadow-sm border-t-4 transition-shadow hover:shadow-md"
          :class="stat.border"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-xs font-medium text-[#6B778C] uppercase tracking-wider">{{ stat.label }}</p>
              <p class="mt-2 text-3xl font-bold text-[#172B4D]">
                <span v-if="projectStore.loading" class="h-8 w-16 rounded bg-gray-200 animate-pulse inline-block" />
                <span v-else>{{ stat.value }}</span>
              </p>
            </div>
            <div :class="['flex h-10 w-10 items-center justify-center rounded-xl', stat.color]">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Projects -->
      <div>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-base font-semibold text-[#172B4D]">Recent Projects</h2>
          <button
            class="flex items-center gap-1 text-sm font-medium text-primary-600 hover:underline"
            @click="router.push({ name: 'project-index' })"
          >
            View all <ArrowRight class="h-4 w-4" />
          </button>
        </div>

        <AppLoading v-if="projectStore.loading" text="Loading projects…" />

        <AppEmptyState
          v-else-if="!recentProjects.length"
          :icon="FolderOpen"
          title="No projects yet"
          description="Create your first project to start tracking tasks and collaborating with your team."
          action-label="Create Project"
          :action-icon="Plus"
          @action="router.push({ name: 'project-create' })"
        />

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <ProjectCard
            v-for="project in recentProjects"
            :key="project.id"
            :project="project"
            @select-project="router.push({ name: 'project-show', params: { id: $event.id } })"
          />
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="rounded-xl border border-[#DFE1E6] bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-[#172B4D]">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
          <AppButton variant="secondary" @click="router.push({ name: 'project-create' })">
            <Plus class="h-4 w-4" />
            Create Project
          </AppButton>
          <AppButton variant="secondary" @click="router.push({ name: 'project-index' })">
            <FolderOpen class="h-4 w-4" />
            Browse Projects
          </AppButton>
          <AppButton variant="secondary" @click="router.push({ name: 'settings' })">
            <LayoutDashboard class="h-4 w-4" />
            Profile Settings
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
