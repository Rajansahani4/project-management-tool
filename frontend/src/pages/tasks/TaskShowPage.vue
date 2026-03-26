<script setup>
import { onMounted, onUnmounted, computed, ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useTask } from '@/composables/useTask.js'
import { useTaskChannel } from '@/composables/useEcho.js'
import { useTaskStore } from '@/stores/tasks.js'
import { useAuthStore } from '@/stores/auth.js'
import { useUiStore } from '@/stores/ui.js'
import { useMembersStore } from '@/stores/members.js'
import AppSpinner       from '@/components/common/AppSpinner.vue'
import AppModal         from '@/components/common/AppModal.vue'
import AppButton        from '@/components/common/AppButton.vue'
import CommentThread    from '@/components/comments/CommentThread.vue'
import AttachmentList   from '@/components/attachments/AttachmentList.vue'
import AttachmentUpload from '@/components/attachments/AttachmentUpload.vue'
import { formatDate, formatDateTime, timeAgo } from '@/utils/formatters.js'

const props = defineProps({
  projectId: { type: String, required: true },
  taskId:    { type: String, required: true },
})

const router        = useRouter()
const authStore     = useAuthStore()
const ui            = useUiStore()
const taskStore     = useTaskStore()
const membersStore  = useMembersStore()

const {
  current: task,
  loading,
  errors,
  fetchOne,
  update:           updateTask,
  destroy:          destroyTask,
  assign:           assignTask,
  addComment,
  editComment,
  removeComment,
  uploadAttachment,
  removeAttachment,
} = useTask()

// ── Real-time ─────────────────────────────────────────────────────────────────
let echoCleanup = null

onMounted(async () => {
  await Promise.all([
    fetchOne(props.projectId, props.taskId),
    membersStore.fetchAll(props.projectId),
  ])

  echoCleanup = useTaskChannel(props.taskId, {
    onCommentCreated:     (comment)    => taskStore.applyCommentCreated(comment),
    onAttachmentUploaded: (attachment) => taskStore.applyAttachmentUploaded(attachment),
  })
})

onUnmounted(() => {
  if (typeof echoCleanup === 'function') echoCleanup()
})

// ── Edit mode ─────────────────────────────────────────────────────────────────
const editing   = ref(false)
const editForm  = reactive({ title: '', description: '', priority: 'medium', status: 'todo', due_date: '' })
const editLoading = ref(false)

function startEdit() {
  editForm.title       = task.value?.title ?? ''
  editForm.description = task.value?.description ?? ''
  editForm.priority    = task.value?.priority ?? 'medium'
  editForm.status      = task.value?.status  ?? 'todo'
  editForm.due_date    = task.value?.due_date?.split('T')[0] ?? ''
  editing.value = true
}

async function saveEdit() {
  editLoading.value = true
  try {
    await updateTask(props.projectId, props.taskId, { ...editForm })
    editing.value = false
    ui.success('Task updated.')
  } finally {
    editLoading.value = false
  }
}

// ── Status quick-change ───────────────────────────────────────────────────────
async function changeStatus(newStatus) {
  await taskStore.updateStatus(props.projectId, props.taskId, newStatus)
  ui.success('Status updated.')
}

// ── Delete task ───────────────────────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteLoading     = ref(false)

async function confirmDelete() {
  deleteLoading.value = true
  try {
    await destroyTask(props.projectId, props.taskId)
    router.push({ name: 'project-show', params: { id: props.projectId } })
  } finally {
    deleteLoading.value = false
    showDeleteConfirm.value = false
  }
}

// ── Assignee ──────────────────────────────────────────────────────────────────
const assignLoading = ref(false)

async function onAssignChange(rawValue) {
  const userId = rawValue === '' ? null : Number(rawValue)
  assignLoading.value = true
  try {
    await assignTask(props.projectId, props.taskId, userId)
  } finally {
    assignLoading.value = false
  }
}

// ── Comments ──────────────────────────────────────────────────────────────────
const commentSubmitting = ref(false)

async function onCommentSubmit(content) {
  commentSubmitting.value = true
  try {
    await addComment(props.projectId, props.taskId, { content })
    ui.success('Comment posted.')
  } finally {
    commentSubmitting.value = false
  }
}

async function onCommentEdit({ id, content }) {
  await editComment(props.projectId, props.taskId, id, { content })
  ui.success('Comment updated.')
}

async function onCommentDelete(commentId) {
  await removeComment(props.projectId, props.taskId, commentId)
  ui.success('Comment deleted.')
}

// ── Attachments ───────────────────────────────────────────────────────────────
const uploadLoading = ref(false)

async function onFileSelected(file) {
  uploadLoading.value = true
  try {
    await uploadAttachment(props.projectId, props.taskId, file)
    ui.success('File uploaded.')
  } finally {
    uploadLoading.value = false
  }
}

async function onAttachmentDelete(attachmentId) {
  await removeAttachment(props.projectId, props.taskId, attachmentId)
}

// ── Computed helpers ──────────────────────────────────────────────────────────
const statusLabel = computed(() => {
  const map = { todo: 'To Do', in_progress: 'In Progress', completed: 'Completed', archived: 'Archived' }
  return map[task.value?.status] ?? task.value?.status
})

const statusClass = computed(() => {
  return {
    'bg-gray-100 text-gray-600':   task.value?.status === 'todo',
    'bg-blue-100 text-blue-700':   task.value?.status === 'in_progress',
    'bg-green-100 text-green-700': task.value?.status === 'completed',
    'bg-gray-200 text-gray-500':   task.value?.status === 'archived',
  }
})

const priorityClass = computed(() => {
  return {
    'bg-red-100 text-red-700':      task.value?.priority === 'high',
    'bg-yellow-100 text-yellow-700': task.value?.priority === 'medium',
    'bg-gray-100 text-gray-600':    task.value?.priority === 'low',
  }
})

const isOwner = computed(() =>
  authStore.user && task.value?.created_by === authStore.user.id
)

const statusOptions = [
  { value: 'todo',        label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed',   label: 'Completed' },
  { value: 'archived',    label: 'Archived' },
]

const priorityOptions = [
  { value: 'low',    label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High' },
]
</script>

<template>
  <div>

    <!-- ── Loading ─────────────────────────────────────────────────────────── -->
    <div v-if="loading && !task" class="flex items-center justify-center py-24">
      <AppSpinner size="lg" />
    </div>

    <template v-else-if="task">

      <!-- ── Breadcrumb ───────────────────────────────────────────────────── -->
      <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
        <RouterLink :to="{ name: 'dashboard' }" class="hover:text-primary-600 transition-colors">Dashboard</RouterLink>
        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <RouterLink
          :to="{ name: 'project-show', params: { id: projectId } }"
          class="hover:text-primary-600 transition-colors truncate max-w-[12rem]"
        >
          Project
        </RouterLink>
        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="truncate font-medium text-gray-900 max-w-[16rem]">{{ task.title }}</span>
      </nav>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Main content ────────────────────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Task header card -->
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div v-if="!editing">
              <!-- Title row -->
              <div class="mb-4 flex items-start justify-between gap-4">
                <h1 class="text-xl font-bold text-gray-900">{{ task.title }}</h1>
                <div class="flex shrink-0 items-center gap-2">
                  <AppButton size="sm" variant="secondary" @click="startEdit">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                  </AppButton>
                  <AppButton size="sm" variant="danger" @click="showDeleteConfirm = true">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </AppButton>
                </div>
              </div>

              <!-- Badges -->
              <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass">
                  {{ statusLabel }}
                </span>
                <span v-if="task.priority" class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="priorityClass">
                  {{ task.priority }}
                </span>
              </div>

              <!-- Description -->
              <p v-if="task.description" class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">
                {{ task.description }}
              </p>
              <p v-else class="text-sm italic text-gray-400">No description provided.</p>
            </div>

            <!-- Edit form -->
            <form v-else class="space-y-4" @submit.prevent="saveEdit" novalidate>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                <input
                  v-model="editForm.title"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <p v-if="errors.title" class="mt-1.5 text-xs text-red-600">{{ errors.title[0] }}</p>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  v-model="editForm.description"
                  rows="4"
                  class="w-full resize-none rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                  <select
                    v-model="editForm.status"
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">Priority</label>
                  <select
                    v-model="editForm.priority"
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Due date</label>
                <input
                  v-model="editForm.due_date"
                  type="date"
                  class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div class="flex justify-end gap-3">
                <AppButton variant="secondary" @click="editing = false">Cancel</AppButton>
                <AppButton type="submit" :loading="editLoading">Save changes</AppButton>
              </div>
            </form>
          </div>

          <!-- Comments section -->
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="mb-4 text-base font-semibold text-gray-900">
              Comments
              <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                {{ task.comments?.length ?? 0 }}
              </span>
            </h2>
            <CommentThread
              :comments="task.comments ?? []"
              :submitting="commentSubmitting"
              @submit="onCommentSubmit"
              @edit="onCommentEdit"
              @delete="onCommentDelete"
            />
          </div>

          <!-- Attachments section -->
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="mb-4 text-base font-semibold text-gray-900">
              Attachments
              <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                {{ task.attachments?.length ?? 0 }}
              </span>
            </h2>
            <AttachmentUpload :loading="uploadLoading" @file-selected="onFileSelected" />
            <AttachmentList
              v-if="task.attachments?.length"
              :attachments="task.attachments"
              class="mt-4"
              @delete="onAttachmentDelete"
            />
          </div>
        </div>

        <!-- ── Sidebar ─────────────────────────────────────────────────────── -->
        <aside class="space-y-4">

          <!-- Quick status change -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Quick Status</h3>
            <div class="flex flex-col gap-1.5">
              <button
                v-for="opt in statusOptions"
                :key="opt.value"
                :class="[
                  'rounded-lg px-3 py-2 text-left text-sm font-medium transition',
                  task.status === opt.value
                    ? 'bg-primary-50 text-primary-700 ring-1 ring-primary-200'
                    : 'text-gray-600 hover:bg-gray-50',
                ]"
                @click="changeStatus(opt.value)"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>

          <!-- Metadata -->
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Details</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Assigned to</dt>
                <dd>
                  <!-- Current assignee display -->
                  <div v-if="task.assignee" class="mb-2 flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 uppercase shrink-0">
                      {{ task.assignee.name?.charAt(0) }}
                    </div>
                    <span class="text-sm font-medium text-gray-800 truncate">{{ task.assignee.name }}</span>
                  </div>
                  <!-- Dropdown -->
                  <div class="relative">
                    <select
                      :value="task.assigned_to ?? ''"
                      :disabled="assignLoading || membersStore.loading"
                      class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                      @change="onAssignChange($event.target.value)"
                    >
                      <option value="">— Unassigned —</option>
                      <option
                        v-for="member in membersStore.members"
                        :key="member.user.id"
                        :value="member.user.id"
                      >
                        {{ member.user.name }}
                      </option>
                    </select>
                    <div v-if="assignLoading" class="absolute right-2 top-1/2 -translate-y-1/2">
                      <AppSpinner size="xs" />
                    </div>
                  </div>
                </dd>
              </div>

              <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Due date</dt>
                <dd class="mt-1 text-sm text-gray-700">
                  {{ task.due_date ? formatDate(task.due_date) : '—' }}
                </dd>
              </div>

              <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Priority</dt>
                <dd class="mt-1">
                  <span
                    v-if="task.priority"
                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                    :class="priorityClass"
                  >
                    {{ task.priority }}
                  </span>
                  <span v-else class="text-sm text-gray-400">—</span>
                </dd>
              </div>

              <div>
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Created</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ formatDateTime(task.created_at) }}</dd>
              </div>

              <div v-if="task.updated_at">
                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Last updated</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ timeAgo(task.updated_at) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Back link -->
          <RouterLink
            :to="{ name: 'project-show', params: { id: projectId } }"
            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Project
          </RouterLink>
        </aside>
      </div>

    </template>

    <!-- ── Delete confirm modal ──────────────────────────────────────────── -->
    <AppModal :open="showDeleteConfirm" title="Delete Task" size="sm" @close="showDeleteConfirm = false">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to delete <strong>{{ task?.title }}</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <AppButton variant="secondary" @click="showDeleteConfirm = false">Cancel</AppButton>
          <AppButton variant="danger" :loading="deleteLoading" @click="confirmDelete">Delete Task</AppButton>
        </div>
      </div>
    </AppModal>

  </div>
</template>
