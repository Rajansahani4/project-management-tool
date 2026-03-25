import client from './client.js'

export const membersApi = {
  index:      (projectId)             => client.get(`/projects/${projectId}/members`),
  add:        (projectId, payload)    => client.post(`/projects/${projectId}/members`, payload),
  updateRole: (projectId, userId, payload) => client.patch(`/projects/${projectId}/members/${userId}`, payload),
  remove:     (projectId, userId)     => client.delete(`/projects/${projectId}/members/${userId}`),
}
