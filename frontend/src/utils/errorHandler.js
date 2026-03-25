import { useUiStore } from '@/stores/ui.js'
import router from '@/router/index.js'

/**
 * Global error handler — registered in main.js as app.config.errorHandler.
 * Catches unhandled errors thrown inside Vue component lifecycle hooks,
 * watchers, and event handlers.
 */
export function globalErrorHandler(err, instance, info) {
  console.error('[Vue error]', info, err)

  // Don't double-handle normalised API errors (already shown at the store level)
  if (err?.status) return

  const ui = useUiStore()
  ui.error('Something went wrong. Please try again.')
}

/**
 * Register a one-time listener for the auth:expired custom event
 * dispatched by the Axios interceptor when a 401 is received.
 */
export function setupAuthExpiredListener() {
  window.addEventListener('auth:expired', () => {
    const ui = useUiStore()
    ui.warning('Your session has expired. Please log in again.')
    router.push({ name: 'login' })
  })
}

/**
 * Handle a caught API error in a component or composable.
 * Displays an appropriate toast based on the status code.
 *
 * @param {object} err  - Normalised error from the Axios interceptor
 * @param {string} fallback - Fallback message if none found
 */
export function handleApiError(err, fallback = 'An error occurred.') {
  const ui = useUiStore()

  if (err?.status === 403) {
    ui.error('You do not have permission to perform this action.')
    return
  }

  if (err?.status === 404) {
    ui.error('The requested resource was not found.')
    return
  }

  if (err?.status >= 500) {
    ui.error('A server error occurred. Please try again later.')
    return
  }

  // 422 validation errors are handled inline via store.errors — no toast needed
  if (err?.status === 422) return

  ui.error(err?.message ?? fallback)
}
