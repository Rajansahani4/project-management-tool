<script setup>
import { onMounted, computed, ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useProject } from '@/composables/useProject.js'
import { useTask } from '@/composables/useTask.js'
import { useTaskStore } from '@/stores/tasks.js'
import { useProjectChannel } from '@/composables/useEcho.js'
import { useAuthStore } from '@/stores/auth.js'
import { useUiStore } from '@/stores/ui.js'
import AppSpinner  from '@/components/common/AppSpinner.vue'
import AppModal    from '@/components/common/AppModal.vue'
import AppButton   from '@/components/common/AppButton.vue'
import TaskBoard   from '@/components/tasks/TaskBoard.vue'
import TaskFilters from '@/components/tasks/TaskFilters.vue'
import { formatDate } from '@/utils/formatters.js'

const props = defineProps({
  id: { type: String, required: true },
})

const router    = useRouter()
const authStore = useAuthStore()
const ui        = useUiStore()
const taskStore = useTaskStore()

const { current: project, loading: projectLoading, fetchOne, destroy: destroyProject } = useProject()
const { tasks, loading: taskLoading, fetchAll, create: createTask, errors: taskErrors } = useTask()

const loading = computed(() => projectLoading.value || taskLoading.value)

// ── View mode ────────────────────────────────────────────────────────────────
const viewMode = ref('kanban') // 'kanban' | 'list'

// ── Filters ──────────────────────────────────────────────────────────────────
const activeFilters = reactive({ status: '', priority: '', assignee: '', due_date: '' })

const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    if (activeFilters.status   && task.status   !== activeFilters.status)   return false
    if (activeFilters.priority && task.priority !== activeFilters.priority) return false
    if (activeFilters.assignee && task.assigned_to !== Number(activeFilters.assignee)) return false
    return true
  })
})

function onFiltersChanged(filters) {
  Object.assign(activeFilters, filters)
}

// ── Task create modal ────────────────────────────────────────────────────────
const showCreateModal = ref(false)
const createForm = reactive({ title: '', description: '', priority: 'medium', status: 'todo', due_date: '' })
const createLoading = ref(false)

const priorityOptions = [
  { value: 'low',    label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High' },
]

const statusOptions = [
  { value: 'todo',        label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed',   label: 'Completed' },
  { value: 'archived',    label: 'Archived' },
]

async function submitCreateTask() {
  if (!createForm.title.trim()) { taskErrors.value = { title: ['Title is required.'] }; return }
  createLoading.value = true
  try {
    await createTask(props.id, { ...createForm })
    showCreateModal.value = false
    Object.assign(createForm, { title: '', description: '', priority: 'medium', status: 'todo', due_date: '' })
    ui.success('Task created.')
  } finally {
    createLoading.value = false
  }
}

// ── Drag-and-drop status update ───────────────────────────────────────────────
async function onTaskMoved({ taskId, newStatus }) {
  await taskStore.updateStatus(props.id, taskId, newStatus)
}

// ── Navigate to task detail ───────────────────────────────────────────────────
function onTaskClick(task) {
  router.push({ name: 'task-show', params: { projectId: props.id, taskId: task.id } })
}

// ── Delete project ────────────────────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteLoading     = ref(false)

async function confirmDelete() {
  deleteLoading.value = true
  try {
    await destroyProject(props.id)
  } finally {
    deleteLoading.value = false
    showDeleteConfirm.value = false
  }
}

// ── Status badge classes ──────────────────────────────────────────────────────
function statusClass(status) {
  return {
    'bg-green-100 text-green-700': status === 'active',
    'bg-gray-100 text-gray-600':   status === 'archived',
    'bg-blue-100 text-blue-700':   status === 'in_progress',
  }
}

onMounted(async () => {
  await fetchOne(props.id)
  await fetchAll(props.id)

  useProjectChannel(props.id, {
    onTaskCreated:  (task)       => taskStore._upsert?.(task),
    onTaskUpdated:  (task)       => taskStore._upsert?.(task),
  })
})
</script>

<template>
  <div class="space-y-6 px-6 pt-4">

    <!-- ── Loading ─────────────────────────────────────────────────────────── -->
    <div v-if="loading && !project" class="flex items-center justify-center py-24">
      <AppSpinner size="lg" />
    </div>

    <template v-else-if="project">

      <!-- ── Breadcrumb ───────────────────────────────────────────────────── -->
      <nav class="flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
        <RouterLink :to="{ name: 'dashboard' }" class="hover:text-primary-600 transition-colors">
          Dashboard
        </RouterLink>
        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="truncate font-medium text-gray-900">{{ project.name }}</span>
      </nav>

      <!-- ── Project header ───────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 truncate">{{ project.name }}</h1>
            <span
              class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
              :class="statusClass(project.status)"
            >
              {{ project.status }}
            </span>
          </div>
          <p v-if="project.description" class="mt-1 text-sm text-gray-500">{{ project.description }}</p>
          <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-400">
            <span v-if="project.due_date" class="flex items-center gap-1">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Due {{ formatDate(project.due_date) }}
            </span>
            <span v-if="project.members_count != null" class="flex items-center gap-1">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ project.members_count }} members
            </span>
            <span class="flex items-center gap-1">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              {{ tasks.length }} tasks
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex shrink-0 flex-wrap items-center gap-2">
          <RouterLink
            :to="{ name: 'team-management', params: { projectId: id } }"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Team
          </RouterLink>
          <AppButton size="sm" variant="secondary" @click="showDeleteConfirm = true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </AppButton>
          <AppButton size="sm" @click="showCreateModal = true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Task
          </AppButton>
        </div>
      </div>

      <!-- ── Filters + view toggle ────────────────────────────────────────── -->
      <div class="flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 sm:flex-row sm:items-end sm:justify-between">
        <TaskFilters :active-filters="activeFilters" @filters-changed="onFiltersChanged" />

        <!-- View mode toggle -->
        <div class="flex shrink-0 items-center rounded-lg border border-gray-200 bg-gray-50 p-1">
          <button
            :class="['flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition',
              viewMode === 'kanban' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700']"
            @click="viewMode = 'kanban'"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            Board
          </button>
          <button
            :class="['flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition',
              viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700']"
            @click="viewMode = 'list'"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            List
          </button>
        </div>
      </div>

      <!-- ── Loading overlay (on task fetch) ─────────────────────────────── -->
      <div v-if="taskLoading" class="flex items-center justify-center py-16">
        <AppSpinner size="lg" />
      </div>

      <!-- ── Kanban board view ────────────────────────────────────────────── -->
      <TaskBoard
        v-else-if="viewMode === 'kanban'"
        :tasks="filteredTasks"
        :loading="false"
        @task-moved="onTaskMoved"
        @task-click="onTaskClick"
      />

      <!-- ── List view ────────────────────────────────────────────────────── -->
      <div v-else>
        <div v-if="filteredTasks.length === 0" class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
          <p class="text-sm text-gray-400">No tasks match the current filters.</p>
        </div>

        <ul v-else class="space-y-2">
          <li
            v-for="task in filteredTasks"
            :key="task.id"
          >
            <button
              class="flex w-full items-center justify-between rounded-xl bg-white px-5 py-3.5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md hover:ring-primary-200 text-left"
              @click="onTaskClick(task)"
            >
              <div class="flex min-w-0 items-center gap-3">
                <!-- Status dot -->
                <span
                  class="h-2.5 w-2.5 shrink-0 rounded-full"
                  :class="{
                    'bg-gray-300':  task.status === 'todo',
                    'bg-blue-500':  task.status === 'in_progress',
                    'bg-green-500': task.status === 'completed',
                    'bg-gray-400':  task.status === 'archived',
                  }"
                />
                <span class="truncate font-medium text-gray-900">{{ task.title }}</span>
              </div>
              <div class="ml-4 flex shrink-0 items-center gap-2">
                <span
                  v-if="task.priority"
                  class="rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="{
                    'bg-red-100 text-red-700':      task.priority === 'high',
                    'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                    'bg-gray-100 text-gray-600':     task.priority === 'low',
                  }"
                >
                  {{ task.priority }}
                </span>
                <span v-if="task.due_date" class="hidden text-xs text-gray-400 sm:block">
                  {{ formatDate(task.due_date) }}
                </span>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </button>
          </li>
        </ul>
      </div>

    </template>

    <!-- ── Create task modal ─────────────────────────────────────────────── -->
    <AppModal :open="showCreateModal" title="Create Task" size="md" @close="showCreateModal = false">
      <form class="space-y-4" @submit.prevent="submitCreateTask" novalidate>

        <!-- Title -->
        <div>
          <label for="task-title" class="mb-1.5 block text-sm font-medium text-gray-700">
            Title <span class="text-red-500">*</span>
          </label>
          <input
            id="task-title"
            v-model="createForm.title"
            type="text"
            placeholder="What needs to be done?"
            autofocus
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
            :class="taskErrors.title ? 'border-red-400 focus:ring-red-400' : ''"
          />
          <p v-if="taskErrors.title" class="mt-1.5 text-xs text-red-600">{{ taskErrors.title[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="task-desc" class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
          <textarea
            id="task-desc"
            v-model="createForm.description"
            rows="3"
            placeholder="Optional details…"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
          />
        </div>

        <!-- Priority + Status row -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="task-priority" class="mb-1.5 block text-sm font-medium text-gray-700">Priority</label>
            <select
              id="task-priority"
              v-model="createForm.priority"
              class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label for="task-status" class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
            <select
              id="task-status"
              v-model="createForm.status"
              class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
        </div>

        <!-- Due date -->
        <div>
          <label for="task-due" class="mb-1.5 block text-sm font-medium text-gray-700">Due date</label>
          <input
            id="task-due"
            v-model="createForm.due_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 border-t pt-4">
          <AppButton variant="secondary" @click="showCreateModal = false">Cancel</AppButton>
          <AppButton type="submit" :loading="createLoading">Create Task</AppButton>
        </div>
      </form>
    </AppModal>

    <!-- ── Delete confirm modal ──────────────────────────────────────────── -->
    <AppModal :open="showDeleteConfirm" title="Delete Project" size="sm" @close="showDeleteConfirm = false">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to delete <strong>{{ project?.name }}</strong>? This action cannot be undone and all tasks will be permanently removed.
        </p>
        <div class="flex justify-end gap-3">
          <AppButton variant="secondary" @click="showDeleteConfirm = false">Cancel</AppButton>
          <AppButton variant="danger" :loading="deleteLoading" @click="confirmDelete">Delete Project</AppButton>
        </div>
      </div>
    </AppModal>

  </div>
</template>
