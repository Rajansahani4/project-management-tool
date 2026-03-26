import { computed } from 'vue'
import { useTaskStore } from '@/stores/tasks.js'
import { useUiStore } from '@/stores/ui.js'

export function useTask() {
  const store = useTaskStore()
  const ui    = useUiStore()

  const tasks      = computed(() => store.tasks)
  const current    = computed(() => store.current)
  const loading    = computed(() => store.loading)
  const errors     = computed(() => store.errors)
  const todo       = computed(() => store.todo)
  const inProgress = computed(() => store.inProgress)
  const completed  = computed(() => store.completed)

  async function fetchAll(projectId, params) {
    await store.fetchAll(projectId, params)
  }

  async function fetchOne(projectId, taskId) {
    await store.fetchOne(projectId, taskId)
  }

  async function create(projectId, payload) {
    return store.create(projectId, payload)
  }

  async function update(projectId, taskId, payload) {
    await store.update(projectId, taskId, payload)
    ui.success('Task updated.')
  }

  async function destroy(projectId, taskId) {
    await store.destroy(projectId, taskId)
    ui.success('Task deleted.')
  }

  async function updateStatus(projectId, taskId, status) {
    await store.updateStatus(projectId, taskId, status)
  }

  async function assign(projectId, taskId, userId) {
    await store.assign(projectId, taskId, userId)
    ui.success('Task assigned.')
  }

  async function addComment(projectId, taskId, payload) {
    return store.addComment(projectId, taskId, payload)
  }

  async function editComment(projectId, taskId, commentId, payload) {
    await store.editComment(projectId, taskId, commentId, payload)
  }

  async function removeComment(projectId, taskId, commentId) {
    await store.removeComment(projectId, taskId, commentId)
  }

  async function uploadAttachment(projectId, taskId, file) {
    const attachment = await store.uploadAttachment(projectId, taskId, file)
    ui.success('File uploaded.')
    return attachment
  }

  async function removeAttachment(projectId, taskId, attachmentId) {
    await store.removeAttachment(projectId, taskId, attachmentId)
    ui.success('Attachment removed.')
  }

  return {
    tasks,
    current,
    loading,
    errors,
    todo,
    inProgress,
    completed,
    fetchAll,
    fetchOne,
    create,
    update,
    destroy,
    updateStatus,
    assign,
    addComment,
    editComment,
    removeComment,
    uploadAttachment,
    removeAttachment,
  }
}
