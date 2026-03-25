import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, beforeEach } from 'vitest'
import NotFoundPage from '@/pages/NotFoundPage.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/',         name: 'dashboard',     component: { template: '<div/>' } },
    { path: '/projects', name: 'project-index', component: { template: '<div/>' } },
    { path: '/404',      name: 'not-found',     component: { template: '<div/>' } },
  ],
})

function mountPage() {
  return mount(NotFoundPage, {
    global: { plugins: [router] },
  })
}

describe('NotFoundPage', () => {
  beforeEach(() => router.push('/404'))

  it('renders 404 code', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('404')
  })

  it('renders "Page not found" heading', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Page not found')
  })

  it('renders a link back to Dashboard', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Dashboard')
    const links = wrapper.findAll('a')
    expect(links.length).toBeGreaterThan(0)
  })

  it('renders a link to Browse Projects', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Browse Projects')
  })

  it('shows a friendly description message', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('moved or deleted')
  })
})
