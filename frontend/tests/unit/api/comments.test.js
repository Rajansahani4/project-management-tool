import { describe, it, expect, vi, beforeEach } from 'vitest'
import { commentsApi } from '@/api/comments.js'

vi.mock('@/api/client.js', () => ({
  default: {
    get:    vi.fn(),
    post:   vi.fn(),
    patch:  vi.fn(),
    delete: vi.fn(),
  },
}))

import client from '@/api/client.js'

const PID = 5
const TID = 12
const CID = 99

describe('commentsApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('create — POSTs to .../comments with body', async () => {
    const payload = { body: 'Great work!' }
    client.post.mockResolvedValue({ data: { id: CID, ...payload } })

    await commentsApi.create(PID, TID, payload)

    expect(client.post).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/comments`,
      payload,
    )
  })

  it('update — PATCHes .../comments/:cid', async () => {
    const payload = { body: 'Updated body' }
    client.patch.mockResolvedValue({ data: { id: CID, ...payload } })

    await commentsApi.update(PID, TID, CID, payload)

    expect(client.patch).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/comments/${CID}`,
      payload,
    )
  })

  it('destroy — DELETEs .../comments/:cid', async () => {
    client.delete.mockResolvedValue({})

    await commentsApi.destroy(PID, TID, CID)

    expect(client.delete).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/comments/${CID}`,
    )
  })

  it('propagates 403 on unauthorized delete', async () => {
    client.delete.mockRejectedValue({ status: 403, message: 'Forbidden' })

    await expect(commentsApi.destroy(PID, TID, CID)).rejects.toMatchObject({ status: 403 })
  })
})
