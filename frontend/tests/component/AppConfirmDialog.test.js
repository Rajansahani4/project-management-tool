import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import AppConfirmDialog from '@/components/common/AppConfirmDialog.vue'

function mountDialog(props) {
  return mount(AppConfirmDialog, { props, attachTo: document.body })
}

describe('AppConfirmDialog', () => {
  it('does not render when closed', () => {
    const wrapper = mountDialog({ open: false })
    expect(document.body.querySelector('[role="dialog"]')).toBeNull()
    wrapper.unmount()
  })

  it('renders when open', () => {
    const wrapper = mountDialog({ open: true })
    expect(document.body.querySelector('[role="dialog"]')).not.toBeNull()
    wrapper.unmount()
  })

  it('shows title and message', () => {
    const wrapper = mountDialog({ open: true, title: 'Delete?', message: 'This cannot be undone.' })
    expect(document.body.querySelector('h2').textContent).toBe('Delete?')
    expect(document.body.textContent).toContain('This cannot be undone.')
    wrapper.unmount()
  })

  it('emits confirm on confirm button click', async () => {
    const wrapper = mountDialog({ open: true, confirmText: 'Yes' })
    const buttons = document.body.querySelectorAll('button[type="button"]')
    // last button should be confirm (danger)
    buttons[buttons.length - 1].click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('confirm')).toBeTruthy()
    wrapper.unmount()
  })

  it('emits cancel on cancel button click', async () => {
    const wrapper = mountDialog({ open: true, cancelText: 'No' })
    const buttons = document.body.querySelectorAll('button[type="button"]')
    // first button among the two action buttons is cancel
    // buttons[0] is close-x, buttons[1] is Cancel, buttons[2] is Confirm
    const cancelBtn = Array.from(buttons).find(b => b.textContent.trim() === 'No')
    cancelBtn.click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('cancel')).toBeTruthy()
    wrapper.unmount()
  })
})
