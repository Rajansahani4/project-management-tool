import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppBadge from '@/components/common/AppBadge.vue'

describe('AppBadge', () => {
  it('renders label prop', () => {
    const wrapper = mount(AppBadge, { props: { label: 'Active' } })
    expect(wrapper.text()).toBe('Active')
  })

  it('renders slot content over label', () => {
    const wrapper = mount(AppBadge, {
      props: { label: 'Ignored' },
      slots: { default: 'From slot' },
    })
    expect(wrapper.text()).toBe('From slot')
  })

  it('applies success variant classes', () => {
    const wrapper = mount(AppBadge, { props: { variant: 'success' } })
    expect(wrapper.find('span').classes()).toContain('bg-green-100')
  })

  it('applies danger variant classes', () => {
    const wrapper = mount(AppBadge, { props: { variant: 'danger' } })
    expect(wrapper.find('span').classes()).toContain('bg-red-100')
  })

  it('falls back to secondary for unknown variant', () => {
    const wrapper = mount(AppBadge, { props: { variant: 'unknown' } })
    expect(wrapper.find('span').classes()).toContain('bg-gray-100')
  })
})
