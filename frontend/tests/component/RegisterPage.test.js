import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import RegisterPage from '@/pages/auth/RegisterPage.vue'

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/register', name: 'register', component: { template: '<div/>' } },
    { path: '/login',    name: 'login',    component: { template: '<div/>' } },
  ],
})

function mountPage() {
  return mount(RegisterPage, {
    global: {
      plugins: [
        createTestingPinia({
          initialState: { auth: { user: null, token: null, loading: false, errors: {} } },
          createSpy: vi.fn,
        }),
        router,
      ],
    },
  })
}

describe('RegisterPage', () => {
  beforeEach(() => router.push('/register'))

  it('renders all form fields', async () => {
    const wrapper = mountPage()
    expect(wrapper.find('#name').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#password_confirmation').exists()).toBe(true)
  })

  it('shows validation error when name is empty on submit', async () => {
    const wrapper = mountPage()
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('Full name is required')
  })

  it('shows validation error for invalid email', async () => {
    const wrapper = mountPage()
    await wrapper.find('#name').setValue('Jane')
    await wrapper.find('#email').setValue('not-an-email')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('valid email')
  })

  it('shows validation error when password is too short', async () => {
    const wrapper = mountPage()
    await wrapper.find('#name').setValue('Jane')
    await wrapper.find('#email').setValue('jane@example.com')
    await wrapper.find('#password').setValue('abc')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('at least 8 characters')
  })

  it('shows validation error when passwords do not match', async () => {
    const wrapper = mountPage()
    await wrapper.find('#name').setValue('Jane')
    await wrapper.find('#email').setValue('jane@example.com')
    await wrapper.find('#password').setValue('password123')
    await wrapper.find('#password_confirmation').setValue('different123')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('do not match')
  })

  it('has a link to the login page', async () => {
    const wrapper = mountPage()
    expect(wrapper.text()).toContain('Sign in')
    const links = wrapper.findAll('a')
    expect(links.length).toBeGreaterThan(0)
  })

  it('shows loading state on submit button while loading', async () => {
    const wrapper = mount(RegisterPage, {
      global: {
        plugins: [
          createTestingPinia({
            initialState: { auth: { user: null, token: null, loading: true, errors: {} } },
            createSpy: vi.fn,
          }),
          router,
        ],
      },
    })
    const btn = wrapper.find('button[type="submit"]')
    expect(btn.attributes('disabled')).toBeDefined()
    expect(btn.text()).toContain('Creating account')
  })
})
