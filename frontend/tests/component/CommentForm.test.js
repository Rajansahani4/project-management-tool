import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import CommentForm from '@/components/comments/CommentForm.vue'

describe('CommentForm', () => {
  const global = { plugins: [createTestingPinia()] }

  it('renders textarea', () => {
    const wrapper = mount(CommentForm, { global })
    expect(wrapper.find('textarea').exists()).toBe(true)
  })

  it('emits submit with trimmed content', async () => {
    const wrapper = mount(CommentForm, { global })
    await wrapper.find('textarea').setValue('  Great work!  ')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.emitted('submit')?.[0]).toEqual(['Great work!'])
  })

  it('does not emit submit when content is empty', async () => {
    const wrapper = mount(CommentForm, { global })
    await wrapper.find('form').trigger('submit')
    expect(wrapper.emitted('submit')).toBeFalsy()
  })

  it('clears textarea after submit', async () => {
    const wrapper = mount(CommentForm, { global })
    await wrapper.find('textarea').setValue('Hello!')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.find('textarea').element.value).toBe('')
  })

  it('disables submit button when content is empty', () => {
    const wrapper = mount(CommentForm, { global })
    const btn = wrapper.findComponent({ name: 'AppButton' })
    expect(btn.props('disabled')).toBe(true)
  })
})
