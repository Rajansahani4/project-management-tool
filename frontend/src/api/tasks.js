import client from './client.js'

export const tasksApi = {
  index: (projectId, params) =>
    client.get(`/projects/${projectId}/tasks`, { params }),

  show: (projectId, taskId) =>
    client.get(`/projects/${projectId}/tasks/${taskId}`),

  create: (projectId, payload) =>
    client.post(`/projects/${projectId}/tasks`, payload),

  update: (projectId, taskId, payload) =>
    client.patch(`/projects/${projectId}/tasks/${taskId}`, payload),

  destroy: (projectId, taskId) =>
    client.delete(`/projects/${projectId}/tasks/${taskId}`),

  updateStatus: (projectId, taskId, status) =>
    client.patch(`/projects/${projectId}/tasks/${taskId}/status`, { status }),

  assign: (projectId, taskId, userId) =>
    client.patch(`/projects/${projectId}/tasks/${taskId}/assign`, { user_id: userId }),

  restore: (projectId, taskId) =>
    client.post(`/projects/${projectId}/tasks/${taskId}/restore`),
}
