import { describe, it, expect, vi, beforeEach } from 'vitest'
import { membersApi } from '@/api/members.js'

vi.mock('@/api/client.js', () => ({
  default: {
    get:    vi.fn(),
    post:   vi.fn(),
    patch:  vi.fn(),
    delete: vi.fn(),
  },
}))

import client from '@/api/client.js'

const PID = 3
const UID = 7

describe('membersApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('index — GETs /projects/:pid/members', async () => {
    client.get.mockResolvedValue({ data: [] })

    await membersApi.index(PID)

    expect(client.get).toHaveBeenCalledWith(`/projects/${PID}/members`)
  })

  it('add — POSTs to /projects/:pid/members with email and role', async () => {
    const payload = { email: 'bob@example.com', role: 'member' }
    client.post.mockResolvedValue({ data: { id: 1, ...payload } })

    await membersApi.add(PID, payload)

    expect(client.post).toHaveBeenCalledWith(`/projects/${PID}/members`, payload)
  })

  it('updateRole — PATCHes .../members/:uid with role', async () => {
    const payload = { role: 'admin' }
    client.patch.mockResolvedValue({ data: { id: UID, role: 'admin' } })

    await membersApi.updateRole(PID, UID, payload)

    expect(client.patch).toHaveBeenCalledWith(`/projects/${PID}/members/${UID}`, payload)
  })

  it('remove — DELETEs .../members/:uid', async () => {
    client.delete.mockResolvedValue({})

    await membersApi.remove(PID, UID)

    expect(client.delete).toHaveBeenCalledWith(`/projects/${PID}/members/${UID}`)
  })

  it('propagates 422 when email not found', async () => {
    client.post.mockRejectedValue({ status: 422, errors: { email: ['not found'] } })

    await expect(membersApi.add(PID, { email: 'nobody@x.com', role: 'member' })).rejects.toMatchObject({
      status: 422,
      errors: { email: ['not found'] },
    })
  })

  it('propagates 403 when not project owner', async () => {
    client.post.mockRejectedValue({ status: 403, message: 'Forbidden' })

    await expect(membersApi.add(PID, { email: 'x@x.com', role: 'admin' })).rejects.toMatchObject({
      status: 403,
    })
  })
})
