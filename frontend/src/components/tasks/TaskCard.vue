<script setup>
import AppAvatar from '@/components/common/AppAvatar.vue'
import { formatDate } from '@/utils/formatters.js'
import { computed } from 'vue'

const props = defineProps({
  task:      { type: Object,  required: true },
  draggable: { type: Boolean, default: false },
})

const emit = defineEmits(['click', 'assign-click'])

const priorityConfig = {
  high:   { bar: 'bg-red-500',    badge: 'bg-red-50 text-red-700 ring-red-200',     label: 'High' },
  medium: { bar: 'bg-amber-400',  badge: 'bg-amber-50 text-amber-700 ring-amber-200', label: 'Medium' },
  low:    { bar: 'bg-green-400',  badge: 'bg-green-50 text-green-700 ring-green-200', label: 'Low' },
}

const priority = computed(() => priorityConfig[props.task.priority] ?? null)

const isOverdue = computed(() => {
  if (!props.task.due_date) return false
  return new Date(props.task.due_date) < new Date()
})

function onDragStart(e) {
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('taskId', String(props.task.id))
}
</script>

<template>
  <article
    :draggable="draggable"
    class="group relative flex cursor-pointer flex-col gap-2.5 rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition-all hover:border-blue-300 hover:shadow-md"
    :class="draggable && 'cursor-grab active:cursor-grabbing'"
    @click="emit('click', task)"
    @dragstart="onDragStart"
  >
    <!-- Priority colour strip (left border) -->
    <div
      v-if="priority"
      class="absolute inset-y-0 left-0 w-1 rounded-l-lg"
      :class="priority.bar"
    />

    <div class="pl-1.5">
      <!-- Title -->
      <p class="text-sm font-medium text-gray-900 leading-snug group-hover:text-blue-700 transition-colors line-clamp-2">
        {{ task.title }}
      </p>

      <!-- Description preview -->
      <p v-if="task.description" class="mt-0.5 text-xs text-gray-400 line-clamp-1">
        {{ task.description }}
      </p>
    </div>

    <!-- Footer row -->
    <div class="flex items-center justify-between gap-2 pl-1.5">
      <!-- Left: priority + due date -->
      <div class="flex flex-wrap items-center gap-1.5">
        <!-- Priority badge -->
        <span
          v-if="priority"
          class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-semibold ring-1"
          :class="priority.badge"
        >
          <svg class="h-2.5 w-2.5" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true">
            <path d="M1 1h6l-2 3 2 3H1l2-3z" />
          </svg>
          {{ priority.label }}
        </span>

        <!-- Due date -->
        <span
          v-if="task.due_date"
          class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium"
          :class="isOverdue
            ? 'bg-red-50 text-red-600 ring-1 ring-red-200'
            : 'bg-gray-50 text-gray-500 ring-1 ring-gray-200'"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ formatDate(task.due_date) }}
        </span>

        <!-- Comments count -->
        <span
          v-if="task.comments_count || task.comments?.length"
          class="inline-flex items-center gap-1 text-[11px] text-gray-400"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          {{ task.comments_count ?? task.comments?.length }}
        </span>
      </div>

      <!-- Right: assignee avatar (clickable) -->
      <button
        type="button"
        class="shrink-0 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 hover:scale-110"
        :title="task.assignee ? `Assigned to ${task.assignee.name} — click to change` : 'Unassigned — click to assign'"
        @click.stop="emit('assign-click', { task, el: $event.currentTarget })"
      >
        <AppAvatar
          v-if="task.assignee"
          :name="task.assignee.name"
          size="sm"
        />
        <div v-else class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-gray-300 hover:border-blue-400 hover:text-blue-400 transition-colors">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
        </div>
      </button>
    </div>
  </article>
</template>
