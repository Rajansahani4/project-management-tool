import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth.js'

export const useAuthStore = defineStore('auth', () => {
  // ─── State ───────────────────────────────────────────────────────────────
  const user    = ref(null)
  const token   = ref(localStorage.getItem('auth_token') ?? null)
  const loading = ref(false)
  const errors  = ref({})

  // ─── Getters ──────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value)

  // ─── Actions ──────────────────────────────────────────────────────────────
  async function register(payload) {
    loading.value = true
    errors.value  = {}
    try {
      const data = await authApi.register(payload)
      _applyCredentials(data)
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function login(payload) {
    loading.value = true
    errors.value  = {}
    try {
      const data = await authApi.login(payload)
      _applyCredentials(data)
    } catch (err) {
      errors.value = err.errors ?? {}
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {
      // Always clear local state even if the server call fails
    } finally {
      _clearCredentials()
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const data = await authApi.me()
      user.value = data.data
    } catch {
      _clearCredentials()
    }
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function _applyCredentials(data) {
    // Backend wraps in { data: { token, user? }, message }
    // Axios interceptor returns response.data (the full envelope)
    const payload = data.data ?? data
    token.value = payload.token
    user.value  = payload.user ?? null
    localStorage.setItem('auth_token', payload.token)
  }

  function _clearCredentials() {
    user.value  = null
    token.value = null
    localStorage.removeItem('auth_token')
  }

  return {
    user,
    token,
    loading,
    errors,
    isAuthenticated,
    register,
    login,
    logout,
    fetchUser,
  }
})
