import client from './client.js'

export const commentsApi = {
  create: (projectId, taskId, payload) =>
    client.post(`/projects/${projectId}/tasks/${taskId}/comments`, payload),

  update: (projectId, taskId, commentId, payload) =>
    client.patch(`/projects/${projectId}/tasks/${taskId}/comments/${commentId}`, payload),

  destroy: (projectId, taskId, commentId) =>
    client.delete(`/projects/${projectId}/tasks/${taskId}/comments/${commentId}`),
}
