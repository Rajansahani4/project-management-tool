import { describe, it, expect, vi, beforeEach } from 'vitest'
import { tasksApi } from '@/api/tasks.js'

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

describe('tasksApi', () => {
  beforeEach(() => vi.clearAllMocks())

  it('index — GETs /projects/:pid/tasks', async () => {
    client.get.mockResolvedValue({ data: [] })

    await tasksApi.index(PID)

    expect(client.get).toHaveBeenCalledWith(`/projects/${PID}/tasks`, { params: undefined })
  })

  it('index — forwards filter params', async () => {
    client.get.mockResolvedValue({ data: [] })

    await tasksApi.index(PID, { status: 'todo' })

    expect(client.get).toHaveBeenCalledWith(`/projects/${PID}/tasks`, { params: { status: 'todo' } })
  })

  it('show — GETs /projects/:pid/tasks/:tid', async () => {
    client.get.mockResolvedValue({ data: { id: TID } })

    await tasksApi.show(PID, TID)

    expect(client.get).toHaveBeenCalledWith(`/projects/${PID}/tasks/${TID}`)
  })

  it('create — POSTs to /projects/:pid/tasks', async () => {
    const payload = { title: 'Fix bug', priority: 'high', status: 'todo' }
    client.post.mockResolvedValue({ data: { id: TID, ...payload } })

    await tasksApi.create(PID, payload)

    expect(client.post).toHaveBeenCalledWith(`/projects/${PID}/tasks`, payload)
  })

  it('update — PATCHes /projects/:pid/tasks/:tid', async () => {
    const payload = { title: 'Updated' }
    client.patch.mockResolvedValue({ data: { id: TID } })

    await tasksApi.update(PID, TID, payload)

    expect(client.patch).toHaveBeenCalledWith(`/projects/${PID}/tasks/${TID}`, payload)
  })

  it('destroy — DELETEs /projects/:pid/tasks/:tid', async () => {
    client.delete.mockResolvedValue({})

    await tasksApi.destroy(PID, TID)

    expect(client.delete).toHaveBeenCalledWith(`/projects/${PID}/tasks/${TID}`)
  })

  it('updateStatus — PATCHes .../status with status value', async () => {
    client.patch.mockResolvedValue({ data: { id: TID, status: 'in_progress' } })

    await tasksApi.updateStatus(PID, TID, 'in_progress')

    expect(client.patch).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/status`,
      { status: 'in_progress' },
    )
  })

  it('assign — PATCHes .../assign with user_id', async () => {
    client.patch.mockResolvedValue({ data: { id: TID } })

    await tasksApi.assign(PID, TID, 7)

    expect(client.patch).toHaveBeenCalledWith(
      `/projects/${PID}/tasks/${TID}/assign`,
      { user_id: 7 },
    )
  })

  it('restore — POSTs to .../restore', async () => {
    client.post.mockResolvedValue({ data: { id: TID } })

    await tasksApi.restore(PID, TID)

    expect(client.post).toHaveBeenCalledWith(`/projects/${PID}/tasks/${TID}/restore`)
  })

  it('propagates 404 rejection', async () => {
    client.get.mockRejectedValue({ status: 404, message: 'Not Found' })

    await expect(tasksApi.show(PID, 999)).rejects.toMatchObject({ status: 404 })
  })
})
