import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppTextarea from '@/components/common/AppTextarea.vue'

describe('AppTextarea', () => {
  it('emits update:modelValue on input', async () => {
    const wrapper = mount(AppTextarea, { props: { modelValue: '' } })
    await wrapper.find('textarea').setValue('hello')
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['hello'])
  })

  it('shows label', () => {
    const wrapper = mount(AppTextarea, { props: { label: 'Notes' } })
    expect(wrapper.find('label').text()).toContain('Notes')
  })

  it('shows error', () => {
    const wrapper = mount(AppTextarea, { props: { error: 'Too short' } })
    expect(wrapper.find('[role="alert"]').text()).toBe('Too short')
  })

  it('applies rows attribute', () => {
    const wrapper = mount(AppTextarea, { props: { rows: 6 } })
    expect(wrapper.find('textarea').attributes('rows')).toBe('6')
  })

  it('disables textarea when disabled', () => {
    const wrapper = mount(AppTextarea, { props: { disabled: true } })
    expect(wrapper.find('textarea').element.disabled).toBe(true)
  })
})
