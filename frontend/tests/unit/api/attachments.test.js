import { describe, it, expect, vi, beforeEach } from 'vitest'
import { attachmentsApi } from '@/api/attachments.js'

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
const AID = 33

describe('attachmentsApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('upload — POSTs FormData to .../attachments with multipart header', async () => {
    const file = new File(['content'], 'report.pdf', { type: 'application/pdf' })
    client.post.mockResolvedValue({ data: { id: AID, filename: 'report.pdf' } })

    await attachmentsApi.upload(PID, TID, file)

    expect(client.post).toHaveBeenCalledOnce()
    const [url, body, config] = client.post.mock.calls[0]
    expect(url).toBe(`/projects/${PID}/tasks/${TID}/attachments`)
    expect(body).toBeInstanceOf(FormData)
    expect(body.get('file')).toBe(file)
    expect(config.headers['Content-Type']).toBe('multipart/form-data')
  })

  it('destroy — DELETEs .../attachments/:aid', async () => {
    client.delete.mockResolvedValue({})

    await attachmentsApi.destroy(PID, TID, AID)

    expect(client.delete).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/attachments/${AID}`,
    )
  })

  it('propagates 422 on oversized file', async () => {
    const file = new File(['x'], 'big.zip')
    client.post.mockRejectedValue({ status: 422, errors: { file: ['too large'] } })

    await expect(attachmentsApi.upload(PID, TID, file)).rejects.toMatchObject({ status: 422 })
  })
})
