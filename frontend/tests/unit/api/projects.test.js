import { describe, it, expect, vi, beforeEach } from 'vitest'
import { projectsApi } from '@/api/projects.js'

vi.mock('@/api/client.js', () => ({
  default: {
    get:    vi.fn(),
    post:   vi.fn(),
    patch:  vi.fn(),
    delete: vi.fn(),
  },
}))

import client from '@/api/client.js'

const project = { id: 1, name: 'My Project' }

describe('projectsApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('index — GETs /projects without params', async () => {
    client.get.mockResolvedValue({ data: [project] })

    await projectsApi.index()

    expect(client.get).toHaveBeenCalledWith('/projects', { params: undefined })
  })

  it('index — forwards query params', async () => {
    client.get.mockResolvedValue({ data: [] })

    await projectsApi.index({ page: 2 })

    expect(client.get).toHaveBeenCalledWith('/projects', { params: { page: 2 } })
  })

  it('show — GETs /projects/:id', async () => {
    client.get.mockResolvedValue({ data: project })

    await projectsApi.show(1)

    expect(client.get).toHaveBeenCalledWith('/projects/1')
  })

  it('create — POSTs to /projects', async () => {
    const payload = { name: 'New', description: 'desc' }
    client.post.mockResolvedValue({ data: { id: 2, ...payload } })

    await projectsApi.create(payload)

    expect(client.post).toHaveBeenCalledWith('/projects', payload)
  })

  it('update — PATCHes /projects/:id', async () => {
    const payload = { name: 'Updated' }
    client.patch.mockResolvedValue({ data: { id: 1, ...payload } })

    await projectsApi.update(1, payload)

    expect(client.patch).toHaveBeenCalledWith('/projects/1', payload)
  })

  it('destroy — DELETEs /projects/:id', async () => {
    client.delete.mockResolvedValue({})

    await projectsApi.destroy(1)

    expect(client.delete).toHaveBeenCalledWith('/projects/1')
  })

  it('propagates 403 rejection', async () => {
    client.get.mockRejectedValue({ status: 403, message: 'Forbidden' })

    await expect(projectsApi.show(99)).rejects.toMatchObject({ status: 403 })
  })
})
