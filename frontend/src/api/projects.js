import client from './client.js'

export const projectsApi = {
  index: (params) => client.get('/projects', { params }),

  show: (id) => client.get(`/projects/${id}`),

  create: (payload) => client.post('/projects', payload),

  update: (id, payload) => client.patch(`/projects/${id}`, payload),

  destroy: (id) => client.delete(`/projects/${id}`),
}
