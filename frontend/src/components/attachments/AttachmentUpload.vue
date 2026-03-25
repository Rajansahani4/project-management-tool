<script setup>
import { ref } from 'vue'

const MAX_SIZE_MB = 10
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024

/** @type {{ loading?: boolean }} */
defineProps({
  loading: { type: Boolean, default: false },
})

/** @type {(event: 'file-selected', file: File) => void} */
const emit = defineEmits(['file-selected'])

const isDragging = ref(false)
const sizeError  = ref('')
const fileInput  = ref(null)

function validateAndEmit(file) {
  sizeError.value = ''
  if (!file) return
  if (file.size > MAX_SIZE_BYTES) {
    sizeError.value = `File exceeds the ${MAX_SIZE_MB} MB limit.`
    return
  }
  emit('file-selected', file)
}

function handleDrop(e) {
  e.preventDefault()
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  validateAndEmit(file)
}

function handleFileInput(e) {
  validateAndEmit(e.target.files?.[0])
  // Reset so the same file can be re-selected
  if (fileInput.value) fileInput.value.value = ''
}
</script>

<template>
  <div>
    <div
      :class="[
        'flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 transition-colors',
        isDragging ? 'border-primary-400 bg-primary-50' : 'border-gray-300 bg-gray-50',
        loading    ? 'pointer-events-none opacity-60' : 'cursor-pointer',
      ]"
      role="button"
      tabindex="0"
      aria-label="Upload file"
      @dragover.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop="handleDrop"
      @click="fileInput?.click()"
      @keydown.enter="fileInput?.click()"
    >
      <svg
        class="mb-3 h-10 w-10 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="1.5"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
      </svg>
      <p class="text-sm text-gray-600">
        <span class="font-medium text-primary-600">Click to upload</span> or drag and drop
      </p>
      <p class="mt-1 text-xs text-gray-400">Max file size: {{ MAX_SIZE_MB }} MB</p>
    </div>

    <p v-if="sizeError" class="mt-1 text-xs text-red-600" role="alert">{{ sizeError }}</p>

    <input
      ref="fileInput"
      type="file"
      class="sr-only"
      aria-hidden="true"
      tabindex="-1"
      @change="handleFileInput"
    />
  </div>
</template>
