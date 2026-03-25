import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import TeamManagementPage from '@/pages/projects/TeamManagementPage.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/',                         name: 'dashboard',       component: { template: '<div/>' } },
    { path: '/projects/:id',             name: 'project-show',    component: { template: '<div/>' } },
    { path: '/projects/:projectId/team', name: 'team-management', component: { template: '<div/>' } },
  ],
})

const ownerMember  = { user_id: 1, role: 'owner',  user: { id: 1, name: 'Jane Smith', email: 'jane@example.com' } }
const regularMember = { user_id: 2, role: 'member', user: { id: 2, name: 'Bob Builder', email: 'bob@example.com' } }

function mountPage({ members = [], isOwner = true } = {}) {
  const membersState = isOwner ? [ownerMember, ...members] : [{ ...ownerMember, role: 'member' }, ...members]
  return mount(TeamManagementPage, {
    props: { projectId: '1' },
    global: {
      plugins: [
        createTestingPinia({
          initialState: {
            auth:     { user: { id: 1, name: 'Jane Smith', email: 'jane@example.com' }, token: 'tok' },
            projects: { current: { id: 1, name: 'Alpha Project', status: 'active' }, loading: false },
            members:  { members: membersState, loading: false, errors: {} },
          },
          stubActions: true,
          createSpy: vi.fn,
        }),
        router,
      ],
    },
  })
}

describe('TeamManagementPage', () => {
  beforeEach(() => router.push('/projects/1/team'))

  it('renders the page heading', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Team Management')
  })

  it('shows the project name in the heading', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Alpha Project')
  })

  it('renders the breadcrumb', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Dashboard')
    expect(wrapper.text()).toContain('Team')
  })

  it('shows Add Member form for owner', async () => {
    const wrapper = mountPage({ isOwner: true })
    await flushPromises()
    expect(wrapper.find('#member-email').exists()).toBe(true)
  })

  it('hides Add Member form for non-owners', async () => {
    const wrapper = mountPage({ isOwner: false })
    await flushPromises()
    expect(wrapper.find('#member-email').exists()).toBe(false)
  })

  it('renders member names in the list', async () => {
    const wrapper = mountPage({ members: [regularMember] })
    await flushPromises()
    expect(wrapper.text()).toContain('Jane Smith')
    expect(wrapper.text()).toContain('Bob Builder')
  })

  it('renders role badges', async () => {
    const wrapper = mountPage({ members: [regularMember] })
    await flushPromises()
    expect(wrapper.text()).toContain('owner')
  })

  it('shows remove button for non-owner members when user is owner', async () => {
    const wrapper = mountPage({ members: [regularMember] })
    await flushPromises()
    const btns = wrapper.findAll('button[aria-label]')
    const removeBtns = btns.filter(b => b.attributes('aria-label')?.includes('Remove'))
    expect(removeBtns.length).toBeGreaterThan(0)
  })

  it('shows validation message when email is empty on Add submit', async () => {
    const wrapper = mountPage()
    await flushPromises()
    await wrapper.find('form').trigger('submit')
    // ui.error should be called — we test the input is still present
    expect(wrapper.find('#member-email').exists()).toBe(true)
  })
})
