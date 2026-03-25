import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppModal from '@/components/common/AppModal.vue'

// Teleport renders into document.body, so we must attach to a real DOM element
function mountModal(props, slots) {
  return mount(AppModal, {
    props,
    slots,
    attachTo: document.body,
  })
}

describe('AppModal', () => {
  it('does not render dialog when closed', () => {
    const wrapper = mountModal({ open: false })
    expect(document.body.querySelector('[role="dialog"]')).toBeNull()
    wrapper.unmount()
  })

  it('renders dialog when open', () => {
    const wrapper = mountModal({ open: true })
    expect(document.body.querySelector('[role="dialog"]')).not.toBeNull()
    wrapper.unmount()
  })

  it('shows title when provided', () => {
    const wrapper = mountModal({ open: true, title: 'My Modal' })
    expect(document.body.querySelector('h2').textContent).toBe('My Modal')
    wrapper.unmount()
  })

  it('emits close when overlay clicked', async () => {
    const wrapper = mountModal({ open: true, title: 'Test' })
    const overlay = document.body.querySelector('[aria-hidden="true"]')
    overlay.click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('close')).toBeTruthy()
    wrapper.unmount()
  })

  it('emits close when X button clicked', async () => {
    const wrapper = mountModal({ open: true, title: 'Test' })
    const btn = document.body.querySelector('[aria-label="Close modal"]')
    btn.click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('close')).toBeTruthy()
    wrapper.unmount()
  })

  it('renders slot content', () => {
    const wrapper = mountModal({ open: true }, { default: '<p id="modal-body">Modal body</p>' })
    expect(document.body.querySelector('#modal-body')).not.toBeNull()
    wrapper.unmount()
  })

  it('applies correct size class for lg', () => {
    const wrapper = mountModal({ open: true, size: 'lg' })
    expect(document.body.querySelector('.max-w-2xl')).not.toBeNull()
    wrapper.unmount()
  })
})
