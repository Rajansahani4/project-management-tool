import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projects.js'
import { useUiStore } from '@/stores/ui.js'

export function useProject() {
  const store  = useProjectStore()
  const ui     = useUiStore()
  const router = useRouter()

  const projects = computed(() => store.projects)
  const current  = computed(() => store.current)
  const loading  = computed(() => store.loading)
  const errors   = computed(() => store.errors)

  async function fetchAll(params) {
    await store.fetchAll(params)
  }

  async function fetchOne(id) {
    await store.fetchOne(id)
  }

  async function create(payload) {
    const project = await store.create(payload)
    ui.success('Project created.')
    router.push({ name: 'project-show', params: { id: project.id } })
    return project
  }

  async function update(id, payload) {
    await store.update(id, payload)
    ui.success('Project updated.')
  }

  async function destroy(id) {
    await store.destroy(id)
    ui.success('Project deleted.')
    router.push({ name: 'dashboard' })
  }

  return {
    projects,
    current,
    loading,
    errors,
    fetchAll,
    fetchOne,
    create,
    update,
    destroy,
  }
}
