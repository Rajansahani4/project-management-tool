import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppAvatar from '@/components/common/AppAvatar.vue'

describe('AppAvatar', () => {
  it('generates initials from full name', () => {
    const wrapper = mount(AppAvatar, { props: { name: 'John Doe' } })
    expect(wrapper.text()).toBe('JD')
  })

  it('generates single initial from single-word name', () => {
    const wrapper = mount(AppAvatar, { props: { name: 'Alice' } })
    expect(wrapper.text()).toBe('A')
  })

  it('uses initials prop over name', () => {
    const wrapper = mount(AppAvatar, { props: { name: 'John Doe', initials: 'JX' } })
    expect(wrapper.text()).toBe('JX')
  })

  it('shows ? for empty name', () => {
    const wrapper = mount(AppAvatar, { props: { name: '' } })
    expect(wrapper.text()).toBe('?')
  })

  it('applies sm size class', () => {
    const wrapper = mount(AppAvatar, { props: { name: 'A', size: 'sm' } })
    expect(wrapper.find('span').classes()).toContain('h-7')
  })

  it('applies lg size class', () => {
    const wrapper = mount(AppAvatar, { props: { name: 'A', size: 'lg' } })
    expect(wrapper.find('span').classes()).toContain('h-12')
  })
})
