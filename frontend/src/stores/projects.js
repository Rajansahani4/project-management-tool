import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { projectsApi } from '@/api/projects.js'

export const useProjectStore = defineStore('projects', () => {
  // ─── State ───────────────────────────────────────────────────────────────
  const projects = ref([])
  const current  = ref(null)
  const loading  = ref(false)
  const errors   = ref({})
  const meta     = ref(null)  // pagination meta

  // ─── Getters ──────────────────────────────────────────────────────────────
  const active   = computed(() => projects.value.filter(p => p.status === 'active'))
  const archived = computed(() => projects.value.filter(p => p.status === 'archived'))

  // ─── Actions ──────────────────────────────────────────────────────────────
  async function fetchAll(params = {}) {
    loading.value = true
    errors.value  = {}
    try {
      const res      = await projectsApi.index(params)
      projects.value = res.data
      meta.value     = res.meta ?? null
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    errors.value  = {}
    try {
      const res    = await projectsApi.show(id)
      current.value = res.data
      _upsert(res.data)
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    loading.value = true
    errors.value  = {}
    try {
      const res = await projectsApi.create(payload)
      _upsert(res.data)
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function update(id, payload) {
    loading.value = true
    errors.value  = {}
    try {
      const res = await projectsApi.update(id, payload)
      _upsert(res.data)
      if (current.value?.id === id) current.value = res.data
      return res.data
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function destroy(id) {
    loading.value = true
    try {
      await projectsApi.destroy(id)
      projects.value = projects.value.filter(p => p.id !== id)
      if (current.value?.id === id) current.value = null
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  function setCurrent(project) {
    current.value = project
  }

  function $reset() {
    projects.value = []
    current.value  = null
    loading.value  = false
    errors.value   = {}
    meta.value     = null
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function _upsert(project) {
    const idx = projects.value.findIndex(p => p.id === project.id)
    if (idx !== -1) {
      projects.value[idx] = project
    } else {
      projects.value.unshift(project)
    }
  }

  return {
    projects,
    current,
    loading,
    errors,
    meta,
    active,
    archived,
    fetchAll,
    fetchOne,
    create,
    update,
    destroy,
    setCurrent,
    $reset,
  }
})
