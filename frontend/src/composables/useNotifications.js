import { computed } from 'vue'
import { useUiStore } from '@/stores/ui.js'

export function useNotifications() {
  const ui = useUiStore()

  const notifications = computed(() => ui.notifications)

  return {
    notifications,
    success: (msg, duration)  => ui.success(msg, duration),
    error:   (msg, duration)  => ui.error(msg, duration),
    warning: (msg, duration)  => ui.warning(msg, duration),
    info:    (msg, duration)  => ui.info(msg, duration),
    dismiss: (id)             => ui.dismiss(id),
    clearAll: ()              => ui.clearAll(),
  }
}
