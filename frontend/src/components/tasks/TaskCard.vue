<script setup>
import AppBadge  from '@/components/common/AppBadge.vue'
import AppAvatar from '@/components/common/AppAvatar.vue'
import { formatDate } from '@/utils/formatters.js'

/** @type {{ task: { id: number, title: string, priority?: string, assignee?: object, due_date?: string, status?: string }, draggable?: boolean }} */
const props = defineProps({
  task:      { type: Object, required: true },
  draggable: { type: Boolean, default: false },
})

/** @type {(event: 'click', task: object) => void} */
const emit = defineEmits(['click'])

const priorityVariant = {
  low:    'secondary',
  medium: 'warning',
  high:   'danger',
}

function onDragStart(e) {
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('taskId', String(props.task.id))
}
</script>

<template>
  <article
    :draggable="draggable"
    :class="[
      'group flex cursor-pointer flex-col gap-2 rounded-lg border bg-white p-3 shadow-sm',
      'hover:shadow-md transition-shadow',
      draggable && 'cursor-grab active:cursor-grabbing',
    ]"
    @click="emit('click', task)"
    @dragstart="onDragStart"
  >
    <p class="text-sm font-medium text-gray-900 group-hover:text-primary-600 transition-colors">
      {{ task.title }}
    </p>

    <div class="flex items-center justify-between gap-2">
      <AppBadge
        v-if="task.priority"
        :label="task.priority"
        :variant="priorityVariant[task.priority] ?? 'secondary'"
      />

      <div class="ml-auto flex items-center gap-2">
        <span v-if="task.due_date" class="text-xs text-gray-400">
          {{ formatDate(task.due_date) }}
        </span>
        <AppAvatar
          v-if="task.assignee"
          :name="task.assignee.name"
          size="sm"
          :title="task.assignee.name"
        />
      </div>
    </div>
  </article>
</template>
