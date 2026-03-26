<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import AppAvatar from '@/components/common/AppAvatar.vue'
import AppSpinner from '@/components/common/AppSpinner.vue'

const props = defineProps({
  task:        { type: Object,  required: true },
  members:     { type: Array,   default: () => [] },
  anchorEl:    { type: Object,  default: null },   // DOM element to anchor to
  loading:     { type: Boolean, default: false },
})

const emit = defineEmits(['select', 'close'])

const pickerEl = ref(null)
const style    = ref({})

// Position the dropdown below/above the anchor element
function reposition() {
  if (!props.anchorEl || !pickerEl.value) return
  const anchor = props.anchorEl.getBoundingClientRect()
  const picker = pickerEl.value.getBoundingClientRect()
  const vp     = { w: window.innerWidth, h: window.innerHeight }

  let top  = anchor.bottom + 6
  let left = anchor.right - picker.width

  // Flip above if not enough space below
  if (top + picker.height > vp.h - 12) top = anchor.top - picker.height - 6
  // Keep within viewport horizontally
  if (left < 8) left = 8
  if (left + picker.width > vp.w - 8) left = vp.w - picker.width - 8

  style.value = { top: `${top}px`, left: `${left}px` }
}

watch(() => props.anchorEl, async () => {
  await nextTick()
  reposition()
}, { immediate: true })

// Close on outside click
function onPointerDown(e) {
  if (!pickerEl.value?.contains(e.target) && !props.anchorEl?.contains(e.target)) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', onPointerDown, true)
  window.addEventListener('scroll', reposition, true)
  window.addEventListener('resize', reposition)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onPointerDown, true)
  window.removeEventListener('scroll', reposition, true)
  window.removeEventListener('resize', reposition)
})

function selectMember(userId) {
  if (props.loading) return
  emit('select', userId)
}
</script>

<template>
  <Teleport to="body">
    <div
      ref="pickerEl"
      class="fixed z-[9999] w-64 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl shadow-gray-300/40 outline-none"
      :style="style"
      role="listbox"
      aria-label="Assign to"
    >
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2.5">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Assign to</span>
        <div v-if="loading" class="flex items-center gap-1.5 text-xs text-gray-400">
          <AppSpinner size="xs" />
          <span>Saving…</span>
        </div>
      </div>

      <!-- Unassign option -->
      <button
        class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm transition-colors hover:bg-gray-50 focus:outline-none focus:bg-gray-50"
        :class="!task.assigned_to ? 'bg-blue-50' : ''"
        role="option"
        :aria-selected="!task.assigned_to"
        @click="selectMember(null)"
      >
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-gray-400">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <span class="flex-1 text-gray-500 italic">Unassigned</span>
        <svg v-if="!task.assigned_to" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </button>

      <!-- Divider -->
      <div class="mx-3 h-px bg-gray-100" />

      <!-- Member list -->
      <ul class="max-h-56 overflow-y-auto py-1" role="group">
        <li v-if="!members.length" class="px-3 py-3 text-center text-xs text-gray-400">
          No members found
        </li>
        <li
          v-for="member in members"
          :key="member.user.id"
        >
          <button
            class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm transition-colors hover:bg-gray-50 focus:outline-none focus:bg-gray-50"
            :class="task.assigned_to === member.user.id ? 'bg-blue-50' : ''"
            role="option"
            :aria-selected="task.assigned_to === member.user.id"
            @click="selectMember(member.user.id)"
          >
            <AppAvatar :name="member.user.name" size="sm" class="shrink-0" />
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-gray-900">{{ member.user.name }}</p>
              <p v-if="member.role" class="truncate text-[11px] text-gray-400 capitalize">{{ member.role }}</p>
            </div>
            <!-- Checkmark for current assignee -->
            <svg v-if="task.assigned_to === member.user.id" class="h-4 w-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </button>
        </li>
      </ul>
    </div>
  </Teleport>
</template>
