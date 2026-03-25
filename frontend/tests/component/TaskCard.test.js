import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import TaskCard from '@/components/tasks/TaskCard.vue'

const task = {
  id: 1,
  title: 'Fix login bug',
  priority: 'high',
  status: 'todo',
  assignee: { name: 'Alice Smith' },
  due_date: '2026-04-01',
}

describe('TaskCard', () => {
  const global = { plugins: [createTestingPinia()] }

  it('renders task title', () => {
    const wrapper = mount(TaskCard, { props: { task }, global })
    expect(wrapper.text()).toContain('Fix login bug')
  })

  it('shows priority badge', () => {
    const wrapper = mount(TaskCard, { props: { task }, global })
    expect(wrapper.text()).toContain('high')
  })

  it('shows assignee avatar', () => {
    const wrapper = mount(TaskCard, { props: { task }, global })
    expect(wrapper.findComponent({ name: 'AppAvatar' }).exists()).toBe(true)
  })

  it('emits click with task on article click', async () => {
    const wrapper = mount(TaskCard, { props: { task }, global })
    await wrapper.find('article').trigger('click')
    expect(wrapper.emitted('click')?.[0]).toEqual([task])
  })

  it('sets draggable attribute when draggable prop is true', () => {
    const wrapper = mount(TaskCard, { props: { task, draggable: true }, global })
    expect(wrapper.find('article').attributes('draggable')).toBe('true')
  })

  it('task without assignee renders without avatar', () => {
    const wrapper = mount(TaskCard, {
      props: { task: { ...task, assignee: null } },
      global,
    })
    expect(wrapper.findComponent({ name: 'AppAvatar' }).exists()).toBe(false)
  })
})
