import client from './client.js'

export const attachmentsApi = {
  upload: (projectId, taskId, file) => {
    const form = new FormData()
    form.append('file', file)
    return client.post(
      `/projects/${projectId}/tasks/${taskId}/attachments`,
      form,
      { headers: { 'Content-Type': 'multipart/form-data' } },
    )
  },

  destroy: (projectId, taskId, attachmentId) =>
    client.delete(`/projects/${projectId}/tasks/${taskId}/attachments/${attachmentId}`),
}
