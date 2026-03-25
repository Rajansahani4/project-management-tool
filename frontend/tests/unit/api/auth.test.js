import { describe, it, expect, vi, beforeEach } from 'vitest'
import { authApi } from '@/api/auth.js'

vi.mock('@/api/client.js', () => ({
  default: {
    get:    vi.fn(),
    post:   vi.fn(),
    patch:  vi.fn(),
    delete: vi.fn(),
  },
}))

import client from '@/api/client.js'

describe('authApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('register — POSTs to /auth/register with payload', async () => {
    const payload = { name: 'Alice', email: 'alice@example.com', password: 'secret' }
    client.post.mockResolvedValue({ data: { token: 'tok' } })

    await authApi.register(payload)

    expect(client.post).toHaveBeenCalledOnce()
    expect(client.post).toHaveBeenCalledWith('/auth/register', payload)
  })

  it('login — POSTs to /auth/login with credentials', async () => {
    const payload = { email: 'alice@example.com', password: 'secret' }
    client.post.mockResolvedValue({ token: 'tok', token_type: 'bearer' })

    await authApi.login(payload)

    expect(client.post).toHaveBeenCalledWith('/auth/login', payload)
  })

  it('logout — POSTs to /auth/logout', async () => {
    client.post.mockResolvedValue({})

    await authApi.logout()

    expect(client.post).toHaveBeenCalledWith('/auth/logout')
  })

  it('me — GETs /auth/me', async () => {
    client.get.mockResolvedValue({ data: { id: 1 } })

    await authApi.me()

    expect(client.get).toHaveBeenCalledWith('/auth/me')
  })

  it('updateProfile — PATCHes /auth/profile with payload', async () => {
    const payload = { name: 'Bob' }
    client.patch.mockResolvedValue({ data: { id: 1, name: 'Bob' } })

    await authApi.updateProfile(payload)

    expect(client.patch).toHaveBeenCalledWith('/auth/profile', payload)
  })

  it('changePassword — POSTs to /auth/change-password with payload', async () => {
    const payload = { current_password: 'old', password: 'new', password_confirmation: 'new' }
    client.post.mockResolvedValue({})

    await authApi.changePassword(payload)

    expect(client.post).toHaveBeenCalledWith('/auth/change-password', payload)
  })

  it('propagates rejection from the client', async () => {
    client.post.mockRejectedValue({ status: 422, errors: { email: ['taken'] } })

    await expect(authApi.login({ email: 'x@x.com', password: 'p' })).rejects.toMatchObject({
      status: 422,
    })
  })
})
