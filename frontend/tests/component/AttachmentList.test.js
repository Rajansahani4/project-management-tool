import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import AttachmentList from '@/components/attachments/AttachmentList.vue'

const attachments = [
  { id: 1, filename: 'report.pdf', size: 204800, url: '/files/1' },
  { id: 2, filename: 'photo.png', size: 512000, url: '/files/2' },
]

describe('AttachmentList', () => {
  const global = { plugins: [createTestingPinia()] }

  it('renders attachment filenames', () => {
    const wrapper = mount(AttachmentList, { props: { attachments }, global })
    expect(wrapper.text()).toContain('report.pdf')
    expect(wrapper.text()).toContain('photo.png')
  })

  it('shows empty state when no attachments', () => {
    const wrapper = mount(AttachmentList, { props: { attachments: [] }, global })
    expect(wrapper.text()).toContain('No attachments yet')
  })

  it('shows formatted file sizes', () => {
    const wrapper = mount(AttachmentList, { props: { attachments }, global })
    expect(wrapper.text()).toContain('200')
  })

  it('emits delete with attachment id on delete click', async () => {
    const wrapper = mount(AttachmentList, { props: { attachments }, global })
    await wrapper.findAll('[aria-label^="Delete"]')[0].trigger('click')
    expect(wrapper.emitted('delete')?.[0]).toEqual([1])
  })

  it('renders download link for each attachment', () => {
    const wrapper = mount(AttachmentList, { props: { attachments }, global })
    const links = wrapper.findAll('a[download]')
    expect(links.length).toBe(2)
  })
})
