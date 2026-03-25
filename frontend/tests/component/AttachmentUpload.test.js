import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AttachmentUpload from '@/components/attachments/AttachmentUpload.vue'

describe('AttachmentUpload', () => {
  it('renders drop zone', () => {
    const wrapper = mount(AttachmentUpload)
    expect(wrapper.find('[role="button"]').exists()).toBe(true)
  })

  it('shows max file size label', () => {
    const wrapper = mount(AttachmentUpload)
    expect(wrapper.text()).toContain('10 MB')
  })

  it('shows drag active styling on dragover', async () => {
    const wrapper = mount(AttachmentUpload)
    await wrapper.find('[role="button"]').trigger('dragover')
    expect(wrapper.find('[role="button"]').classes()).toContain('border-primary-400')
  })

  it('shows error when file exceeds size limit', async () => {
    const wrapper = mount(AttachmentUpload)
    const largeFile = new File(['x'.repeat(11 * 1024 * 1024)], 'large.pdf', { type: 'application/pdf' })
    // Simulate drop with a large file
    const dt = { files: [largeFile], getData: () => '' }
    await wrapper.find('[role="button"]').trigger('drop', { dataTransfer: dt, preventDefault: () => {} })
    expect(wrapper.find('[role="alert"]').exists()).toBe(true)
  })

  it('emits file-selected for valid file', async () => {
    const wrapper = mount(AttachmentUpload)
    const file = new File(['content'], 'doc.pdf', { type: 'application/pdf' })
    const dt = { files: [file], getData: () => '' }
    await wrapper.find('[role="button"]').trigger('drop', { dataTransfer: dt, preventDefault: () => {} })
    expect(wrapper.emitted('file-selected')?.[0]).toEqual([file])
  })
})
