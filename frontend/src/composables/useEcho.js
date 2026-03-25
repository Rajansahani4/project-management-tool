import { ref, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import io from 'socket.io-client'
import { useAuthStore } from '@/stores/auth.js'

let _echo  = null
let _token = null   // token used to create the current _echo instance

function getEcho() {
  const authStore    = useAuthStore()
  const currentToken = authStore.token

  // Recreate the Echo instance whenever the token has changed
  if (_echo && _token !== currentToken) {
    _echo.disconnect()
    _echo  = null
    _token = null
  }

  if (!_echo) {
    _echo = new Echo({
      broadcaster: 'socket.io',
      client:      io,
      host:        import.meta.env.VITE_SOCKET_URL || 'http://localhost:6001',
      auth: {
        headers: {
          Authorization: `Bearer ${currentToken}`,
        },
      },
    })
    _token = currentToken
  }

  return _echo
}

export function destroyEcho() {
  if (_echo) {
    _echo.disconnect()
    _echo  = null
    _token = null
  }
}

/**
 * Subscribe to a private task channel and listen for comment/attachment events.
 * Automatically leaves the channel when the calling component unmounts.
 */
export function useTaskChannel(taskId, { onCommentCreated, onAttachmentUploaded } = {}) {
  const echo    = getEcho()
  const channel = ref(null)

  channel.value = echo.private(`task.${taskId}`)

  if (onCommentCreated) {
    channel.value.listen('CommentCreated', (e) => onCommentCreated(e.comment))
  }

  if (onAttachmentUploaded) {
    channel.value.listen('AttachmentUploaded', (e) => onAttachmentUploaded(e.attachment))
  }

  onUnmounted(() => {
    echo.leave(`task.${taskId}`)
  })

  return { channel }
}

/**
 * Subscribe to a private project channel.
 * Automatically leaves on unmount.
 */
export function useProjectChannel(projectId, listeners = {}) {
  const echo    = getEcho()
  const channel = ref(null)

  channel.value = echo.private(`project.${projectId}`)

  Object.entries(listeners).forEach(([event, handler]) => {
    channel.value.listen(event, handler)
  })

  onUnmounted(() => {
    echo.leave(`project.${projectId}`)
  })

  return { channel }
}
