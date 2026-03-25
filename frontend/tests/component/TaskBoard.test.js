import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import TaskBoard from '@/components/tasks/TaskBoard.vue'

const tasks = [
  { id: 1, title: 'Task A', status: 'todo',        priority: 'low' },
  { id: 2, title: 'Task B', status: 'in_progress',  priority: 'high' },
  { id: 3, title: 'Task C', status: 'completed',    priority: 'medium' },
]

describe('TaskBoard', () => {
  const global = { plugins: [createTestingPinia()] }

  it('renders all four column headers', () => {
    const wrapper = mount(TaskBoard, { props: { tasks }, global })
    expect(wrapper.text()).toContain('To Do')
    expect(wrapper.text()).toContain('In Progress')
    expect(wrapper.text()).toContain('Completed')
    expect(wrapper.text()).toContain('Archived')
  })

  it('places tasks in the correct column', () => {
    const wrapper = mount(TaskBoard, { props: { tasks }, global })
    expect(wrapper.text()).toContain('Task A')
    expect(wrapper.text()).toContain('Task B')
    expect(wrapper.text()).toContain('Task C')
  })

  it('shows loading spinner when loading prop is true', () => {
    const wrapper = mount(TaskBoard, { props: { tasks: [], loading: true }, global })
    expect(wrapper.findComponent({ name: 'AppLoading' }).exists()).toBe(true)
  })

  it('emits task-moved on drop', async () => {
    const wrapper = mount(TaskBoard, { props: { tasks }, global })
    const columns = wrapper.findAll('[class*="flex-col"]')
    // find the drop zone div of first column
    const dropZone = wrapper.find('[class*="border-dashed"]')
    await dropZone.trigger('drop', {
      preventDefault: () => {},
      dataTransfer: { getData: () => '1' },
    })
    expect(wrapper.emitted('task-moved')).toBeTruthy()
  })
})
