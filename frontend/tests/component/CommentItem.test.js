import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import CommentItem from '@/components/comments/CommentItem.vue'

const comment = {
  id: 1,
  content: 'Great progress!',
  author: { name: 'Bob Jones' },
  created_at: '2026-03-25T10:00:00Z',
}

describe('CommentItem', () => {
  const global = { plugins: [createTestingPinia()] }

  it('renders comment content', () => {
    const wrapper = mount(CommentItem, { props: { comment }, global })
    expect(wrapper.text()).toContain('Great progress!')
  })

  it('renders author name', () => {
    const wrapper = mount(CommentItem, { props: { comment }, global })
    expect(wrapper.text()).toContain('Bob Jones')
  })

  it('does not show edit/delete for others comments', () => {
    const wrapper = mount(CommentItem, { props: { comment, isOwn: false }, global })
    expect(wrapper.text()).not.toContain('Edit')
    expect(wrapper.text()).not.toContain('Delete')
  })

  it('shows edit/delete for own comments', () => {
    const wrapper = mount(CommentItem, { props: { comment, isOwn: true }, global })
    expect(wrapper.text()).toContain('Edit')
    expect(wrapper.text()).toContain('Delete')
  })

  it('emits edit with comment id', async () => {
    const wrapper = mount(CommentItem, { props: { comment, isOwn: true }, global })
    await wrapper.find('button:first-of-type').trigger('click')
    expect(wrapper.emitted('edit')?.[0]).toEqual([1])
  })

  it('emits delete with comment id', async () => {
    const wrapper = mount(CommentItem, { props: { comment, isOwn: true }, global })
    const buttons = wrapper.findAll('button')
    await buttons[buttons.length - 1].trigger('click')
    expect(wrapper.emitted('delete')?.[0]).toEqual([1])
  })
})
