import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppSelect from '@/components/common/AppSelect.vue'

const options = [
  { value: 'a', label: 'Option A' },
  { value: 'b', label: 'Option B' },
]

describe('AppSelect', () => {
  it('renders all options', () => {
    const wrapper = mount(AppSelect, { props: { options } })
    const opts = wrapper.findAll('option')
    // includes placeholder option
    expect(opts.length).toBe(3)
    expect(opts[1].text()).toBe('Option A')
  })

  it('emits update:modelValue on change', async () => {
    const wrapper = mount(AppSelect, { props: { options, modelValue: '' } })
    await wrapper.find('select').setValue('b')
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['b'])
  })

  it('shows label when provided', () => {
    const wrapper = mount(AppSelect, { props: { label: 'Choose', options } })
    expect(wrapper.find('label').text()).toContain('Choose')
  })

  it('shows error message', () => {
    const wrapper = mount(AppSelect, { props: { error: 'Required', options } })
    expect(wrapper.find('[role="alert"]').text()).toBe('Required')
  })

  it('disables select when disabled', () => {
    const wrapper = mount(AppSelect, { props: { disabled: true, options } })
    expect(wrapper.find('select').element.disabled).toBe(true)
  })
})
