import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import ProjectCard from '@/components/projects/ProjectCard.vue'

const project = {
  id: 1,
  name: 'Alpha Project',
  description: 'A great project',
  members_count: 3,
  tasks_count: 12,
}

describe('ProjectCard', () => {
  it('renders project name', () => {
    const wrapper = mount(ProjectCard, {
      props: { project },
      global: { plugins: [createTestingPinia()] },
    })
    expect(wrapper.text()).toContain('Alpha Project')
  })

  it('renders description', () => {
    const wrapper = mount(ProjectCard, {
      props: { project },
      global: { plugins: [createTestingPinia()] },
    })
    expect(wrapper.text()).toContain('A great project')
  })

  it('shows member and task counts', () => {
    const wrapper = mount(ProjectCard, {
      props: { project },
      global: { plugins: [createTestingPinia()] },
    })
    expect(wrapper.text()).toContain('3')
    expect(wrapper.text()).toContain('12')
  })

  it('emits select-project on click', async () => {
    const wrapper = mount(ProjectCard, {
      props: { project },
      global: { plugins: [createTestingPinia()] },
    })
    await wrapper.find('article').trigger('click')
    expect(wrapper.emitted('select-project')?.[0]).toEqual([project])
  })
})
