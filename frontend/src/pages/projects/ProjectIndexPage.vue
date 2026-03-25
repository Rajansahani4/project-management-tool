<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projects.js'
import AppButton from '@/components/common/AppButton.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppLoading from '@/components/common/AppLoading.vue'
import AppEmptyState from '@/components/common/AppEmptyState.vue'
import AppBreadcrumb from '@/components/common/AppBreadcrumb.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import { Plus, Search, FolderOpen, LayoutDashboard, LayoutGrid, List } from 'lucide-vue-next'

const router       = useRouter()
const projectStore = useProjectStore()

const search   = ref('')
const viewMode = ref('grid') // grid | list

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return projectStore.projects ?? []
  return (projectStore.projects ?? []).filter(p =>
    p.name.toLowerCase().includes(q) || p.description?.toLowerCase().includes(q)
  )
})

onMounted(() => {
  projectStore.fetchAll()
})

const breadcrumbs = [
  { label: 'Dashboard', to: { name: 'dashboard' }, icon: LayoutDashboard },
  { label: 'Projects',  icon: FolderOpen },
]
</script>

<template>
  <div class="max-w-6xl mx-auto px-6 py-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <AppBreadcrumb :items="breadcrumbs" class="mb-2" />
        <h1 class="text-xl font-bold text-[#172B4D]">Projects</h1>
        <p class="text-sm text-[#6B778C]">
          {{ projectStore.loading ? 'Loading…' : `${projectStore.projects?.length ?? 0} project${projectStore.projects?.length === 1 ? '' : 's'}` }}
        </p>
      </div>
      <AppButton size="md" @click="router.push({ name: 'project-create' })">
        <Plus class="h-4 w-4" />
        Create Project
      </AppButton>
    </div>

    <!-- Controls -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div class="relative flex-1 max-w-xs">
        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B778C]" />
        <input
          v-model="search"
          type="text"
          placeholder="Search projects…"
          class="block w-full rounded border border-[#DFE1E6] bg-white pl-9 pr-4 py-1.5 text-sm text-[#172B4D] placeholder-[#97A0AF] focus:outline-none focus:ring-2 focus:ring-[#0052CC] focus:border-[#0052CC] hover:border-[#97A0AF] transition-colors"
        />
      </div>

      <div class="flex items-center gap-1 ml-auto">
        <button
          :class="['rounded p-2 transition-colors', viewMode === 'grid' ? 'bg-[#DEEBFF] text-[#0052CC]' : 'text-[#6B778C] hover:bg-[#F4F5F7]']"
          title="Grid view"
          @click="viewMode = 'grid'"
        >
          <LayoutGrid class="h-4 w-4" />
        </button>
        <button
          :class="['rounded p-2 transition-colors', viewMode === 'list' ? 'bg-[#DEEBFF] text-[#0052CC]' : 'text-[#6B778C] hover:bg-[#F4F5F7]']"
          title="List view"
          @click="viewMode = 'list'"
        >
          <List class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Loading -->
    <AppLoading v-if="projectStore.loading" text="Loading projects…" />

    <!-- Error -->
    <div
      v-else-if="projectStore.error"
      class="rounded-lg border border-[#FFBDAD] bg-[#FFEBE6] px-4 py-3 text-sm text-[#BF2600]"
    >
      {{ projectStore.error }}
    </div>

    <!-- Empty state: no projects at all -->
    <AppEmptyState
      v-else-if="!projectStore.projects?.length"
      :icon="FolderOpen"
      title="No projects yet"
      description="Create your first project to start managing tasks and collaborating with your team."
      action-label="Create Your First Project"
      :action-icon="Plus"
      @action="router.push({ name: 'project-create' })"
    />

    <!-- Empty state: no search results -->
    <AppEmptyState
      v-else-if="!filtered.length"
      :icon="Search"
      title="No results found"
      :description="`No projects match '${search}'. Try a different search term.`"
    />

    <!-- Grid view -->
    <div
      v-else-if="viewMode === 'grid'"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
      <ProjectCard
        v-for="project in filtered"
        :key="project.id"
        :project="project"
        @select-project="router.push({ name: 'project-show', params: { id: $event.id } })"
      />
    </div>

    <!-- List view -->
    <div v-else class="rounded-xl border border-[#DFE1E6] bg-white shadow-card overflow-hidden">
      <table class="min-w-full divide-y divide-[#DFE1E6]">
        <thead class="bg-[#F4F5F7]">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[#6B778C]">Project</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[#6B778C] hidden sm:table-cell">Members</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[#6B778C] hidden md:table-cell">Tasks</th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[#6B778C]">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#DFE1E6]">
          <tr
            v-for="project in filtered"
            :key="project.id"
            class="cursor-pointer transition-colors hover:bg-[#F4F5F7]"
            @click="router.push({ name: 'project-show', params: { id: project.id } })"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-8 w-8 shrink-0 items-center justify-center rounded font-bold uppercase text-white text-xs"
                  :style="{ backgroundColor: `hsl(${(project.name.charCodeAt(0) * 37) % 360}, 60%, 45%)` }"
                >
                  {{ project.name.charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-[#172B4D] text-sm">{{ project.name }}</p>
                  <p v-if="project.description" class="text-xs text-[#6B778C] line-clamp-1">{{ project.description }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-[#6B778C] hidden sm:table-cell">{{ project.members_count ?? 0 }}</td>
            <td class="px-4 py-3 text-sm text-[#6B778C] hidden md:table-cell">{{ project.tasks_count ?? 0 }}</td>
            <td class="px-4 py-3 text-right">
              <button
                class="rounded px-3 py-1 text-xs font-medium text-[#0052CC] hover:bg-[#DEEBFF] transition-colors"
                @click.stop="router.push({ name: 'project-show', params: { id: project.id } })"
              >
                Open →
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
