<script setup>
/** @type {{ project: { id: number, name: string, description?: string, members_count?: number, tasks_count?: number } }} */
defineProps({
  project: { type: Object, required: true },
})

/** @type {(event: 'select-project', project: object) => void} */
const emit = defineEmits(['select-project'])
</script>

<template>
  <article
    class="group flex cursor-pointer flex-col gap-3 rounded-xl border bg-white p-5 shadow-sm transition-shadow hover:shadow-md focus-within:ring-2 focus-within:ring-primary-500"
    @click="emit('select-project', project)"
  >
    <div>
      <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
        {{ project.name }}
      </h3>
      <p v-if="project.description" class="mt-1 line-clamp-2 text-sm text-gray-500">
        {{ project.description }}
      </p>
    </div>

    <div class="flex items-center gap-4 text-sm text-gray-500">
      <span class="flex items-center gap-1" :title="`${project.members_count ?? 0} members`">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        {{ project.members_count ?? 0 }}
      </span>
      <span class="flex items-center gap-1" :title="`${project.tasks_count ?? 0} tasks`">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        {{ project.tasks_count ?? 0 }}
      </span>
    </div>
  </article>
</template>
