<script setup>
import { computed, ref } from 'vue'
import TaskCard    from '@/components/tasks/TaskCard.vue'
import AppLoading  from '@/components/common/AppLoading.vue'

/** @type {{ tasks?: Array<object>, loading?: boolean }} */
const props = defineProps({
  tasks:   { type: Array,   default: () => [] },
  loading: { type: Boolean, default: false },
})

/** @type {(event: 'task-moved', payload: { taskId: number, newStatus: string }) => void} */
const emit = defineEmits(['task-moved', 'task-click'])

const columns = [
  { key: 'todo',        label: 'To Do' },
  { key: 'in_progress', label: 'In Progress' },
  { key: 'completed',   label: 'Completed' },
  { key: 'archived',    label: 'Archived' },
]

const columnHeaderColor = {
  todo:        'bg-gray-100 text-gray-700',
  in_progress: 'bg-blue-50 text-blue-700',
  completed:   'bg-green-50 text-green-700',
  archived:    'bg-gray-200 text-gray-600',
}

const tasksByStatus = computed(() => {
  const map = {}
  columns.forEach(c => { map[c.key] = [] })
  props.tasks.forEach(task => {
    const col = map[task.status]
    if (col) col.push(task)
  })
  return map
})

// Drag-drop state
const dragOverColumn = ref(null)

function onDragOver(e, columnKey) {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
  dragOverColumn.value = columnKey
}

function onDragLeave() {
  dragOverColumn.value = null
}

function onDrop(e, newStatus) {
  e.preventDefault()
  dragOverColumn.value = null
  const taskId = Number(e.dataTransfer.getData('taskId'))
  if (taskId) emit('task-moved', { taskId, newStatus })
}
</script>

<template>
  <div>
    <!-- Loading overlay -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <AppLoading size="lg" />
    </div>

    <!-- Board -->
    <div v-else class="flex gap-4 overflow-x-auto pb-4">
      <div
        v-for="col in columns"
        :key="col.key"
        class="flex w-64 flex-none flex-col gap-2"
        @dragover="onDragOver($event, col.key)"
        @dragleave="onDragLeave"
        @drop="onDrop($event, col.key)"
      >
        <!-- Column header -->
        <div
          :class="['flex items-center justify-between rounded-lg px-3 py-2', columnHeaderColor[col.key]]"
        >
          <span class="text-sm font-semibold">{{ col.label }}</span>
          <span class="rounded-full bg-white/60 px-2 py-0.5 text-xs font-medium">
            {{ tasksByStatus[col.key].length }}
          </span>
        </div>

        <!-- Drop zone -->
        <div
          :class="[
            'flex min-h-[100px] flex-col gap-2 rounded-lg border-2 border-dashed p-2 transition-colors',
            dragOverColumn === col.key
              ? 'border-primary-400 bg-primary-50'
              : 'border-transparent',
          ]"
        >
          <TaskCard
            v-for="task in tasksByStatus[col.key]"
            :key="task.id"
            :task="task"
            :draggable="true"
            @click="emit('task-click', $event)"
          />

          <div
            v-if="!tasksByStatus[col.key].length"
            class="flex flex-1 items-center justify-center py-4 text-xs text-gray-400"
          >
            No tasks
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
