import client from './client.js'

export const authApi = {
  register: (payload) => client.post('/auth/register', payload),

  login: (payload) => client.post('/auth/login', payload),

  logout: () => client.post('/auth/logout'),

  me: () => client.get('/auth/me'),

  updateProfile: (payload) => client.patch('/auth/profile', payload),

  changePassword: (payload) => client.post('/auth/change-password', payload),
}
