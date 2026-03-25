<script setup>
import { useRouter } from 'vue-router'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import AppButton   from '@/components/common/AppButton.vue'
import AppLoading  from '@/components/common/AppLoading.vue'

/** @type {{ projects?: Array<object>, loading?: boolean, error?: string }} */
defineProps({
  projects: { type: Array,   default: () => [] },
  loading:  { type: Boolean, default: false },
  error:    { type: String,  default: '' },
})

const emit = defineEmits(['select-project'])

const router = useRouter()

function goCreate() {
  router.push({ name: 'project-create' })
}
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-900">Projects</h2>
      <AppButton size="sm" @click="goCreate">+ New Project</AppButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <AppLoading size="lg" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ error }}
    </div>

    <!-- Empty state -->
    <div
      v-else-if="!projects.length"
      class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-16 text-center"
    >
      <svg class="mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <p class="mb-4 text-sm text-gray-500">No projects yet. Create your first project!</p>
      <AppButton size="sm" @click="goCreate">Create Project</AppButton>
    </div>

    <!-- Grid -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <ProjectCard
        v-for="project in projects"
        :key="project.id"
        :project="project"
        @select-project="emit('select-project', $event)"
      />
    </div>
  </div>
</template>
