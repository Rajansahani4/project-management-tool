import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import DashboardPage from '@/pages/DashboardPage.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/',               name: 'dashboard',     component: { template: '<div/>' } },
    { path: '/projects',       name: 'project-index', component: { template: '<div/>' } },
    { path: '/projects/create',name: 'project-create',component: { template: '<div/>' } },
    { path: '/projects/:id',   name: 'project-show',  component: { template: '<div/>' } },
  ],
})

const mockProjects = [
  { id: 1, name: 'Alpha', description: 'First project', status: 'active',   members_count: 3, tasks_count: 5, due_date: null },
  { id: 2, name: 'Beta',  description: 'Second project', status: 'archived', members_count: 1, tasks_count: 2, due_date: '2026-04-01' },
]

function mountDashboard(projectsState = []) {
  return mount(DashboardPage, {
    global: {
      plugins: [
        createTestingPinia({
          initialState: {
            auth:     { user: { id: 1, name: 'Jane Smith', email: 'jane@example.com' }, token: 'tok', loading: false },
            projects: { projects: projectsState, loading: false, errors: {} },
          },
          stubActions: true,
          createSpy: vi.fn,
        }),
        router,
      ],
    },
  })
}

describe('DashboardPage', () => {
  beforeEach(() => router.push('/'))

  it('renders a greeting with the user first name', async () => {
    const wrapper = mountDashboard()
    expect(wrapper.text()).toMatch(/Jane/)
  })

  it('shows skeleton loaders while loading', async () => {
    const wrapper = mount(DashboardPage, {
      global: {
        plugins: [
          createTestingPinia({
            initialState: {
              auth:     { user: { id: 1, name: 'Jane', email: '' }, token: 'tok', loading: false },
              projects: { projects: [], loading: true, errors: {} },
            },
            stubActions: true,
            createSpy: vi.fn,
          }),
          router,
        ],
      },
    })
    expect(wrapper.find('.animate-pulse').exists()).toBe(true)
  })

  it('shows empty state when there are no projects', async () => {
    const wrapper = mountDashboard([])
    expect(wrapper.text()).toContain('No projects yet')
  })

  it('renders project cards when projects exist', async () => {
    const wrapper = mountDashboard(mockProjects)
    expect(wrapper.text()).toContain('Alpha')
    expect(wrapper.text()).toContain('Beta')
  })

  it('renders correct stat counts', async () => {
    const wrapper = mountDashboard(mockProjects)
    // 2 total, 1 active, 1 archived
    const text = wrapper.text()
    expect(text).toContain('2') // total
    expect(text).toContain('1') // active / archived
  })

  it('shows New Project link', async () => {
    const wrapper = mountDashboard()
    expect(wrapper.text()).toContain('New Project')
    const links = wrapper.findAll('a')
    expect(links.length).toBeGreaterThan(0)
  })

  it('renders a View all link to project-index', async () => {
    const wrapper = mountDashboard(mockProjects)
    expect(wrapper.text()).toContain('View all')
  })
})
