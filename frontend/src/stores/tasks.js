import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { tasksApi } from '@/api/tasks.js'
import { commentsApi } from '@/api/comments.js'
import { attachmentsApi } from '@/api/attachments.js'

export const useTaskStore = defineStore('tasks', () => {
  // ─── State ───────────────────────────────────────────────────────────────
  const tasks   = ref([])
  const current = ref(null)
  const loading = ref(false)
  const errors  = ref({})
  const meta    = ref(null)

  // ─── Getters ──────────────────────────────────────────────────────────────
  const byStatus = computed(() => (status) =>
    tasks.value.filter(t => t.status === status)
  )
  const todo       = computed(() => byStatus.value('todo'))
  const inProgress = computed(() => byStatus.value('in_progress'))
  const completed  = computed(() => byStatus.value('completed'))

  // ─── Task CRUD ────────────────────────────────────────────────────────────
  async function fetchAll(projectId, params = {}) {
    loading.value = true
    errors.value  = {}
    try {
      const res    = await tasksApi.index(projectId, params)
      tasks.value  = res.data
      meta.value   = res.meta ?? null
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(projectId, taskId) {
    loading.value = true
    errors.value  = {}
    try {
      const res     = await tasksApi.show(projectId, taskId)
      current.value = res.data
      _upsert(res.data)
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function create(projectId, payload) {
    loading.value = true
    errors.value  = {}
    try {
      const res = await tasksApi.create(projectId, payload)
      _upsert(res.data)
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function update(projectId, taskId, payload) {
    loading.value = true
    errors.value  = {}
    try {
      const res = await tasksApi.update(projectId, taskId, payload)
      _upsert(res.data)
      if (current.value?.id === taskId) current.value = res.data
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function destroy(projectId, taskId) {
    loading.value = true
    try {
      await tasksApi.destroy(projectId, taskId)
      tasks.value = tasks.value.filter(t => t.id !== taskId)
      if (current.value?.id === taskId) current.value = null
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateStatus(projectId, taskId, status) {
    errors.value = {}
    try {
      const res = await tasksApi.updateStatus(projectId, taskId, status)
      _upsert(res.data)
      if (current.value?.id === taskId) current.value = res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  async function assign(projectId, taskId, userId) {
    errors.value = {}
    try {
      const res = await tasksApi.assign(projectId, taskId, userId)
      _upsert(res.data)
      if (current.value?.id === taskId) current.value = res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  // ─── Comment actions ──────────────────────────────────────────────────────
  async function addComment(projectId, taskId, payload) {
    errors.value = {}
    try {
      const res = await commentsApi.create(projectId, taskId, payload)
      if (current.value?.id === taskId) {
        current.value.comments = [...(current.value.comments ?? []), res.data]
      }
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  async function editComment(projectId, taskId, commentId, payload) {
    errors.value = {}
    try {
      const res = await commentsApi.update(projectId, taskId, commentId, payload)
      if (current.value?.id === taskId) {
        current.value.comments = current.value.comments.map(c =>
          c.id === commentId ? res.data : c
        )
      }
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  async function removeComment(projectId, taskId, commentId) {
    try {
      await commentsApi.destroy(projectId, taskId, commentId)
      if (current.value?.id === taskId) {
        current.value.comments = current.value.comments.filter(c => c.id !== commentId)
      }
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  // ─── Attachment actions ───────────────────────────────────────────────────
  async function uploadAttachment(projectId, taskId, file) {
    errors.value = {}
    try {
      const res = await attachmentsApi.upload(projectId, taskId, file)
      if (current.value?.id === taskId) {
        current.value.attachments = [...(current.value.attachments ?? []), res.data]
      }
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  async function removeAttachment(projectId, taskId, attachmentId) {
    try {
      await attachmentsApi.destroy(projectId, taskId, attachmentId)
      if (current.value?.id === taskId) {
        current.value.attachments = current.value.attachments.filter(
          a => a.id !== attachmentId
        )
      }
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  // ─── Real-time handlers ───────────────────────────────────────────────────
  function applyCommentCreated(comment) {
    if (current.value?.id === comment.task_id) {
      const exists = current.value.comments?.some(c => c.id === comment.id)
      if (!exists) {
        current.value.comments = [...(current.value.comments ?? []), comment]
      }
    }
  }

  function applyAttachmentUploaded(attachment) {
    if (current.value?.id === attachment.task_id) {
      const exists = current.value.attachments?.some(a => a.id === attachment.id)
      if (!exists) {
        current.value.attachments = [...(current.value.attachments ?? []), attachment]
      }
    }
  }

  function $reset() {
    tasks.value   = []
    current.value = null
    loading.value = false
    errors.value  = {}
    meta.value    = null
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function _upsert(task) {
    const idx = tasks.value.findIndex(t => t.id === task.id)
    if (idx !== -1) {
      tasks.value[idx] = task
    } else {
      tasks.value.unshift(task)
    }
  }

  return {
    tasks,
    current,
    loading,
    errors,
    meta,
    byStatus,
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
    applyCommentCreated,
    applyAttachmentUploaded,
    $reset,
  }
})
