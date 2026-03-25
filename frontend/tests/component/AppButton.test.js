import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppButton from '@/components/common/AppButton.vue'

describe('AppButton', () => {
  it('renders slot content', () => {
    const wrapper = mount(AppButton, { slots: { default: 'Click me' } })
    expect(wrapper.text()).toContain('Click me')
  })

  it('emits click when clicked', async () => {
    const wrapper = mount(AppButton)
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })

  it('does not emit click when disabled', async () => {
    const wrapper = mount(AppButton, { props: { disabled: true } })
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeFalsy()
  })

  it('does not emit click when loading', async () => {
    const wrapper = mount(AppButton, { props: { loading: true } })
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeFalsy()
  })

  it('shows spinner when loading', () => {
    const wrapper = mount(AppButton, { props: { loading: true } })
    expect(wrapper.findComponent({ name: 'AppSpinner' }).exists()).toBe(true)
  })

  it('applies primary variant classes by default', () => {
    const wrapper = mount(AppButton)
    expect(wrapper.find('button').classes()).toContain('bg-primary-600')
  })

  it('applies danger variant classes', () => {
    const wrapper = mount(AppButton, { props: { variant: 'danger' } })
    expect(wrapper.find('button').classes()).toContain('bg-red-600')
  })

  it('applies correct size classes', () => {
    const wrapper = mount(AppButton, { props: { size: 'lg' } })
    expect(wrapper.find('button').classes()).toContain('px-6')
  })
})
