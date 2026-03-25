import { defineStore } from 'pinia'
import { ref } from 'vue'
import { membersApi } from '@/api/members.js'

export const useMembersStore = defineStore('members', () => {
  // ─── State ───────────────────────────────────────────────────────────────
  const members    = ref([])
  const loading    = ref(false)
  const errors     = ref({})
  const projectId  = ref(null)

  // ─── Actions ──────────────────────────────────────────────────────────────
  async function fetchAll(pid) {
    loading.value   = true
    errors.value    = {}
    projectId.value = pid
    try {
      const res     = await membersApi.index(pid)
      members.value = res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function add(pid, payload) {
    loading.value = true
    errors.value  = {}
    try {
      const res = await membersApi.add(pid, payload)
      members.value.push(res.data)
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateRole(pid, userId, role) {
    errors.value = {}
    try {
      const res = await membersApi.updateRole(pid, userId, { role })
      const idx = members.value.findIndex(m => m.user_id === userId || m.user?.id === userId)
      if (idx !== -1) members.value[idx] = res.data
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  async function remove(pid, userId) {
    errors.value = {}
    try {
      await membersApi.remove(pid, userId)
      members.value = members.value.filter(
        m => m.user_id !== userId && m.user?.id !== userId
      )
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    }
  }

  function $reset() {
    members.value   = []
    loading.value   = false
    errors.value    = {}
    projectId.value = null
  }

  return { members, loading, errors, projectId, fetchAll, add, updateRole, remove, $reset }
})
