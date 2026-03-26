<script setup>
import { computed, ref } from 'vue'
import TaskCard      from '@/components/tasks/TaskCard.vue'
import AppSpinner    from '@/components/common/AppSpinner.vue'
import AssigneePicker from '@/components/tasks/AssigneePicker.vue'

const props = defineProps({
  tasks:          { type: Array,   default: () => [] },
  loading:        { type: Boolean, default: false },
  members:        { type: Array,   default: () => [] },
  assigningTaskId:{ type: Number,  default: null },
})

const emit = defineEmits(['task-moved', 'task-click', 'task-assigned'])

const columns = [
  {
    key:   'todo',
    label: 'To Do',
    accent: 'border-t-gray-400',
    header: 'text-gray-700',
    count:  'bg-gray-100 text-gray-600',
    empty:  'border-gray-200',
  },
  {
    key:   'in_progress',
    label: 'In Progress',
    accent: 'border-t-blue-500',
    header: 'text-blue-700',
    count:  'bg-blue-50 text-blue-600',
    empty:  'border-blue-200',
  },
  {
    key:   'completed',
    label: 'Done',
    accent: 'border-t-green-500',
    header: 'text-green-700',
    count:  'bg-green-50 text-green-600',
    empty:  'border-green-200',
  },
  {
    key:   'archived',
    label: 'Archived',
    accent: 'border-t-gray-300',
    header: 'text-gray-500',
    count:  'bg-gray-100 text-gray-400',
    empty:  'border-gray-200',
  },
]

// ── Assignee picker state ─────────────────────────────────────────────────────
const pickerTask   = ref(null)   // task object the picker is open for
const pickerAnchor = ref(null)   // DOM element to anchor the dropdown to

function openPicker({ task, el }) {
  pickerTask.value   = task
  pickerAnchor.value = el
}

function closePicker() {
  pickerTask.value   = null
  pickerAnchor.value = null
}

function onAssignSelect(userId) {
  if (!pickerTask.value) return
  emit('task-assigned', { taskId: pickerTask.value.id, userId })
  closePicker()
}

// ── Task grouping ─────────────────────────────────────────────────────────────
const tasksByStatus = computed(() => {
  const map = {}
  columns.forEach(c => { map[c.key] = [] })
  props.tasks.forEach(task => {
    if (map[task.status]) map[task.status].push(task)
  })
  return map
})

// Drag-drop state
const dragOverColumn = ref(null)
const draggingTaskId = ref(null)

function onDragStart(taskId) {
  draggingTaskId.value = taskId
}

function onDragOver(e, columnKey) {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
  dragOverColumn.value = columnKey
}

function onDragLeave(e) {
  // Only clear if leaving the column container, not a child element
  if (!e.currentTarget.contains(e.relatedTarget)) {
    dragOverColumn.value = null
  }
}

function onDrop(e, newStatus) {
  e.preventDefault()
  dragOverColumn.value = null
  draggingTaskId.value = null
  const taskId = Number(e.dataTransfer.getData('taskId'))
  if (taskId) emit('task-moved', { taskId, newStatus })
}

function onDragEnd() {
  dragOverColumn.value = null
  draggingTaskId.value = null
}
</script>

<template>
  <!-- Loading -->
  <div v-if="loading" class="flex items-center justify-center py-20">
    <AppSpinner size="lg" />
  </div>

  <!-- Board -->
  <div v-else class="flex gap-3 overflow-x-auto pb-4" style="min-height: 520px;">
    <div
      v-for="col in columns"
      :key="col.key"
      class="flex w-72 flex-none flex-col rounded-xl border-t-4 bg-[#F4F5F7] transition-colors"
      :class="[
        col.accent,
        dragOverColumn === col.key ? 'ring-2 ring-blue-400 ring-offset-1' : '',
      ]"
      @dragover="onDragOver($event, col.key)"
      @dragleave="onDragLeave"
      @drop="onDrop($event, col.key)"
    >
      <!-- Column header -->
      <div class="flex items-center justify-between px-3 py-3">
        <h3 class="text-sm font-bold uppercase tracking-wide" :class="col.header">
          {{ col.label }}
        </h3>
        <span
          class="min-w-[1.5rem] rounded-full px-1.5 py-0.5 text-center text-xs font-semibold"
          :class="col.count"
        >
          {{ tasksByStatus[col.key].length }}
        </span>
      </div>

      <!-- Divider -->
      <div class="mx-3 mb-2 h-px bg-gray-200" />

      <!-- Cards area -->
      <div
        class="flex flex-1 flex-col gap-2 overflow-y-auto px-3 pb-3 transition-colors"
        :class="dragOverColumn === col.key ? 'bg-blue-50/40' : ''"
        style="max-height: calc(100vh - 320px); min-height: 120px;"
      >
        <TaskCard
          v-for="task in tasksByStatus[col.key]"
          :key="task.id"
          :task="task"
          :draggable="true"
          @click="emit('task-click', $event)"
          @assign-click="openPicker"
          @dragstart.native="onDragStart(task.id)"
          @dragend.native="onDragEnd"
        />

        <!-- Empty state -->
        <div
          v-if="!tasksByStatus[col.key].length"
          class="flex flex-1 items-center justify-center rounded-lg border-2 border-dashed py-8 transition-colors"
          :class="dragOverColumn === col.key ? `${col.empty} bg-blue-50` : 'border-gray-200'"
        >
          <p class="text-xs text-gray-400">Drop tasks here</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Assignee picker dropdown (teleported to body) -->
  <AssigneePicker
    v-if="pickerTask"
    :task="pickerTask"
    :members="members"
    :anchor-el="pickerAnchor"
    :loading="pickerTask && assigningTaskId === pickerTask.id"
    @select="onAssignSelect"
    @close="closePicker"
  />
</template>
