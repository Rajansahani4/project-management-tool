import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import ProjectShowPage from '@/pages/projects/ProjectShowPage.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/',                          name: 'dashboard',       component: { template: '<div/>' } },
    { path: '/projects/:id',              name: 'project-show',    component: { template: '<div/>' } },
    { path: '/projects/:projectId/team',  name: 'team-management', component: { template: '<div/>' } },
    { path: '/projects/:p/tasks/:t',      name: 'task-show',       component: { template: '<div/>' } },
  ],
})

const mockProject = {
  id: 1, name: 'My Project', description: 'A test project', status: 'active',
  members_count: 4, tasks_count: 3, due_date: '2026-06-01',
}

const mockTasks = [
  { id: 1, title: 'Fix bug',    status: 'todo',        priority: 'high' },
  { id: 2, title: 'Write docs', status: 'in_progress', priority: 'medium' },
  { id: 3, title: 'Deploy',     status: 'done',        priority: 'low' },
]

function mountPage(overrides = {}) {
  return mount(ProjectShowPage, {
    props: { id: '1' },
    global: {
      plugins: [
        createTestingPinia({
          initialState: {
            auth:     { user: { id: 1, name: 'Jane', email: 'jane@example.com' }, token: 'tok' },
            projects: { current: mockProject, loading: false, errors: {}, ...overrides.projects },
            tasks:    { tasks: mockTasks, loading: false, errors: {}, ...overrides.tasks },
          },
          stubActions: true,
          createSpy: vi.fn,
        }),
        router,
      ],
      stubs: {
        TaskBoard:   { template: '<div data-testid="task-board"><slot/></div>' },
        TaskFilters: { template: '<div data-testid="task-filters"/>' },
        AppModal:    { template: '<div v-if="open"><slot/></div>', props: ['open', 'title', 'size'] },
      },
    },
  })
}

describe('ProjectShowPage', () => {
  beforeEach(() => router.push('/projects/1'))

  it('renders the project name', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('My Project')
  })

  it('renders the project description', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('A test project')
  })

  it('renders breadcrumb with Dashboard link', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Dashboard')
  })

  it('shows the kanban board by default', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.find('[data-testid="task-board"]').exists()).toBe(true)
  })

  it('switches to list view when list button is clicked', async () => {
    const wrapper = mountPage()
    await flushPromises()
    const listBtn = wrapper.findAll('button').find(b => b.text().includes('List'))
    await listBtn?.trigger('click')
    expect(wrapper.find('[data-testid="task-board"]').exists()).toBe(false)
  })

  it('shows the "Add Task" button', async () => {
    const wrapper = mountPage()
    await flushPromises()
    const btn = wrapper.findAll('button').find(b => b.text().includes('Add Task'))
    expect(btn).toBeTruthy()
  })

  it('opens create task modal on Add Task click', async () => {
    const wrapper = mountPage()
    await flushPromises()
    const btn = wrapper.findAll('button').find(b => b.text().includes('Add Task'))
    await btn?.trigger('click')
    expect(wrapper.text()).toContain('Create Task')
  })

  it('shows loading spinner while loading', async () => {
    const wrapper = mountPage({ projects: { current: null, loading: true, errors: {} } })
    expect(wrapper.findComponent({ name: 'AppSpinner' }).exists()).toBe(true)
  })

  it('renders team link', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Team')
  })
})
