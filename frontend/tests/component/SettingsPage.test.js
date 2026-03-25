import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import SettingsPage from '@/pages/settings/SettingsPage.vue'

vi.mock('@/api/auth.js', () => ({
  authApi: {
    updateProfile:  vi.fn().mockResolvedValue({ data: { id: 1, name: 'Jane Smith', email: 'jane@example.com' } }),
    changePassword: vi.fn().mockResolvedValue({}),
  },
}))

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/settings', name: 'settings', component: { template: '<div/>' } },
    { path: '/login',    name: 'login',    component: { template: '<div/>' } },
  ],
})

function mountPage() {
  return mount(SettingsPage, {
    global: {
      plugins: [
        createTestingPinia({
          initialState: {
            auth: { user: { id: 1, name: 'Jane Smith', email: 'jane@example.com' }, token: 'tok' },
          },
          createSpy: vi.fn,
        }),
        router,
      ],
    },
  })
}

describe('SettingsPage', () => {
  beforeEach(() => router.push('/settings'))

  it('renders the page heading', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Settings')
  })

  it('renders the Profile section', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Profile')
  })

  it('pre-fills the display name from user store', async () => {
    const wrapper = mountPage()
    const nameInput = wrapper.find('#display-name')
    expect(nameInput.element.value).toBe('Jane Smith')
  })

  it('shows email as read-only with user email', async () => {
    const wrapper = mountPage()
    const emailInput = wrapper.find('input[type="email"][disabled]')
    expect(emailInput.element.value).toBe('jane@example.com')
  })

  it('renders the Change Password section', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Change Password')
  })

  it('shows password validation error when new passwords differ', async () => {
    const wrapper = mountPage()
    await wrapper.find('#current-password').setValue('oldpassword')
    await wrapper.find('#new-password').setValue('newpassword1')
    await wrapper.find('#confirm-password').setValue('different123')
    const forms = wrapper.findAll('form')
    await forms[1].trigger('submit')
    expect(wrapper.text()).toContain('do not match')
  })

  it('shows validation error when new password is too short', async () => {
    const wrapper = mountPage()
    await wrapper.find('#current-password').setValue('oldpassword')
    await wrapper.find('#new-password').setValue('short')
    await wrapper.find('#confirm-password').setValue('short')
    const forms = wrapper.findAll('form')
    await forms[1].trigger('submit')
    expect(wrapper.text()).toContain('at least 8 characters')
  })

  it('renders the Danger Zone section', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Danger Zone')
  })

  it('renders a Sign out link', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Sign out')
  })
})
