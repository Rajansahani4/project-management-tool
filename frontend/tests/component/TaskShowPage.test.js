import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import TaskShowPage from '@/pages/tasks/TaskShowPage.vue'

vi.mock('@/composables/useEcho.js', () => ({
  useTaskChannel: vi.fn(() => vi.fn()),
}))

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', name: 'dashboard', component: { template: '<div/>' } },
    { path: '/projects/:id',            name: 'project-show', component: { template: '<div/>' } },
    { path: '/projects/:p/tasks/:t',    name: 'task-show',    component: { template: '<div/>' } },
  ],
})

const mockTask = {
  id:          1,
  title:       'Fix login bug',
  description: 'The login form throws 500 on empty email.',
  status:      'in_progress',
  priority:    'high',
  due_date:    '2026-05-01T00:00:00Z',
  created_at:  '2026-03-01T10:00:00Z',
  updated_at:  '2026-03-25T12:00:00Z',
  created_by:  1,
  assignee:    { id: 2, name: 'Bob Builder' },
  comments:    [{ id: 10, content: 'Looking into this.', user: { id: 2, name: 'Bob' }, created_at: '2026-03-20T00:00:00Z' }],
  attachments: [{ id: 20, filename: 'screenshot.png', size: 102400, download_url: '/files/20' }],
}

function mountPage(taskOverrides = {}) {
  return mount(TaskShowPage, {
    props: { projectId: '1', taskId: '1' },
    global: {
      plugins: [
        createTestingPinia({
          initialState: {
            auth:  { user: { id: 1, name: 'Jane', email: 'jane@example.com' }, token: 'tok' },
            tasks: { current: { ...mockTask, ...taskOverrides }, loading: false, errors: {} },
          },
          stubActions: true,
          createSpy: vi.fn,
        }),
        router,
      ],
      stubs: {
        CommentThread:    { template: '<div data-testid="comment-thread"/>' },
        AttachmentList:   { template: '<div data-testid="attachment-list"/>' },
        AttachmentUpload: { template: '<div data-testid="attachment-upload"/>' },
        AppModal:         { template: '<div v-if="open"><slot/></div>', props: ['open', 'title', 'size'] },
      },
    },
  })
}

describe('TaskShowPage', () => {
  beforeEach(() => router.push('/projects/1/tasks/1'))

  it('renders the task title', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Fix login bug')
  })

  it('renders the task description', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('500 on empty email')
  })

  it('shows status badge', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('In Progress')
  })

  it('shows priority badge', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('high')
  })

  it('renders comment thread component', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.find('[data-testid="comment-thread"]').exists()).toBe(true)
  })

  it('renders attachment upload component', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.find('[data-testid="attachment-upload"]').exists()).toBe(true)
  })

  it('renders attachment list when attachments exist', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.find('[data-testid="attachment-list"]').exists()).toBe(true)
  })

  it('shows Edit button', async () => {
    const wrapper = mountPage()
    await flushPromises()
    const btn = wrapper.findAll('button').find(b => b.text().includes('Edit'))
    expect(btn).toBeTruthy()
  })

  it('switches to edit form on Edit click', async () => {
    const wrapper = mountPage()
    await flushPromises()
    const btn = wrapper.findAll('button').find(b => b.text().includes('Edit'))
    await btn?.trigger('click')
    expect(wrapper.text()).toContain('Save changes')
  })

  it('renders assignee in sidebar', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('Bob Builder')
  })

  it('renders quick status options in sidebar', async () => {
    const wrapper = mountPage()
    await flushPromises()
    expect(wrapper.text()).toContain('To Do')
    expect(wrapper.text()).toContain('In Progress')
    expect(wrapper.text()).toContain('Completed')
  })

  it('shows loading spinner while loading', async () => {
    const wrapper = mount(TaskShowPage, {
      props: { projectId: '1', taskId: '1' },
      global: {
        plugins: [
          createTestingPinia({
            initialState: {
              auth:  { user: { id: 1, name: 'Jane', email: '' }, token: 'tok' },
              tasks: { current: null, loading: true, errors: {} },
            },
            stubActions: true,
            createSpy: vi.fn,
          }),
          router,
        ],
      },
    })
    expect(wrapper.findComponent({ name: 'AppSpinner' }).exists()).toBe(true)
  })
})
