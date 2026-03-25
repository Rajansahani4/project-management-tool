import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppInput from '@/components/common/AppInput.vue'

describe('AppInput', () => {
  it('renders with a label', () => {
    const wrapper = mount(AppInput, { props: { label: 'Email' } })
    expect(wrapper.find('label').text()).toContain('Email')
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(AppInput, { props: { modelValue: '' } })
    await wrapper.find('input').setValue('hello')
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['hello'])
  })

  it('shows error message when error prop set', () => {
    const wrapper = mount(AppInput, { props: { error: 'Required field' } })
    expect(wrapper.find('[role="alert"]').text()).toBe('Required field')
  })

  it('adds error styling on input when error present', () => {
    const wrapper = mount(AppInput, { props: { error: 'Oops' } })
    expect(wrapper.find('input').classes()).toContain('border-red-300')
  })

  it('shows required asterisk when required', () => {
    const wrapper = mount(AppInput, { props: { label: 'Name', required: true } })
    expect(wrapper.find('label').text()).toContain('*')
  })

  it('disables input when disabled prop set', () => {
    const wrapper = mount(AppInput, { props: { disabled: true } })
    expect(wrapper.find('input').element.disabled).toBe(true)
  })
})
