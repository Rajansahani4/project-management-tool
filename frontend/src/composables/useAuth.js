import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useUiStore } from '@/stores/ui.js'
import { useProjectStore } from '@/stores/projects.js'
import { useTaskStore } from '@/stores/tasks.js'
import { destroyEcho } from '@/composables/useEcho.js'

export function useAuth() {
  const authStore    = useAuthStore()
  const ui           = useUiStore()
  const projectStore = useProjectStore()
  const taskStore    = useTaskStore()
  const router       = useRouter()

  const user            = computed(() => authStore.user)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const loading         = computed(() => authStore.loading)
  const errors          = computed(() => authStore.errors)
  const generalError    = ref(null)

  async function login(payload) {
    generalError.value = null
    try {
      await authStore.login(payload)
      await authStore.fetchUser()
      ui.success('Welcome back!')
      const redirect = router.currentRoute.value.query.redirect
      router.push(redirect ? decodeURIComponent(redirect) : { name: 'dashboard' })
    } catch (err) {
      generalError.value = err.message ?? 'Invalid credentials. Please try again.'
    }
  }

  async function register(payload) {
    generalError.value = null
    try {
      await authStore.register(payload)
      await authStore.fetchUser()
      ui.success('Account created! Welcome aboard.')
      router.push({ name: 'dashboard' })
    } catch (err) {
      generalError.value = err.message ?? 'Registration failed. Please try again.'
    }
  }

  async function logout() {
    destroyEcho()
    await authStore.logout()
    projectStore.$reset()
    taskStore.$reset()
    router.push({ name: 'login' })
  }

  return {
    user,
    isAuthenticated,
    loading,
    errors,
    generalError,
    login,
    register,
    logout,
  }
}
