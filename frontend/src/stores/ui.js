import { defineStore } from 'pinia'
import { ref, readonly } from 'vue'

let _notificationId = 0

export const useUiStore = defineStore('ui', () => {
  // ─── Loading states ────────────────────────────────────────────────────────
  const globalLoading = ref(false)

  function setGlobalLoading(value) {
    globalLoading.value = value
  }

  // ─── Notifications (toasts) ───────────────────────────────────────────────
  const notifications = ref([])

  function notify({ message, type = 'info', duration = 4000 }) {
    const id = ++_notificationId
    notifications.value.push({ id, message, type })

    if (duration > 0) {
      setTimeout(() => dismiss(id), duration)
    }

    return id
  }

  function success(message, duration)  { return notify({ message, type: 'success', duration }) }
  function error(message, duration)    { return notify({ message, type: 'error', duration: duration ?? 6000 }) }
  function warning(message, duration)  { return notify({ message, type: 'warning', duration }) }
  function info(message, duration)     { return notify({ message, type: 'info', duration }) }

  function dismiss(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  function clearAll() {
    notifications.value = []
  }

  // ─── Modals ───────────────────────────────────────────────────────────────
  const modal = ref({ name: null, props: {} })

  function openModal(name, props = {}) {
    modal.value = { name, props }
  }

  function closeModal() {
    modal.value = { name: null, props: {} }
  }

  return {
    globalLoading: readonly(globalLoading),
    notifications: readonly(notifications),
    modal:         readonly(modal),
    setGlobalLoading,
    notify,
    success,
    error,
    warning,
    info,
    dismiss,
    clearAll,
    openModal,
    closeModal,
  }
})
