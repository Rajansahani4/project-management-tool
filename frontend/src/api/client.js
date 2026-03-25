import axios from 'axios'

/**
 * Shared Axios instance.
 * baseURL = VITE_API_BASE_URL + /api/v1
 * All API modules import this and use relative paths (e.g. '/auth/login').
 */
const client = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/api/v1`,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15_000,
})

// ─── Request interceptor: attach JWT ─────────────────────────────────────────
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ─── Response interceptor: unwrap data, normalise errors ─────────────────────
client.interceptors.response.use(
  // Success — return the raw response.data so callers get the full envelope
  (response) => response.data,

  (error) => {
    const status   = error.response?.status
    const data     = error.response?.data
    const url      = error.config?.url ?? ''

    // On 401 outside of auth endpoints clear the stored token and fire a
    // custom event so the router / error handler can redirect to login without
    // creating a circular dependency here.
    const isAuthEndpoint = ['/auth/login', '/auth/register'].some(p => url.endsWith(p))
    if (status === 401 && !isAuthEndpoint) {
      localStorage.removeItem('auth_token')
      window.dispatchEvent(new CustomEvent('auth:expired'))
    }

    // Normalise so every caller receives { message, errors, status }
    const normalised = {
      message: data?.message ?? error.message ?? 'An unexpected error occurred.',
      errors:  data?.errors  ?? {},
      status:  status        ?? 0,
    }

    return Promise.reject(normalised)
  },
)

export default client
