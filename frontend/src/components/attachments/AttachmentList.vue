<script setup>
import AppButton from '@/components/common/AppButton.vue'
import { formatFileSize } from '@/utils/formatters.js'

/** @type {{ attachments?: Array<{ id: number, filename: string, size?: number, url?: string }> }} */
defineProps({
  attachments: { type: Array, default: () => [] },
})

/** @type {(event: 'delete', id: number) => void} */
const emit = defineEmits(['delete'])

function fileExtension(filename) {
  return filename.split('.').pop()?.toUpperCase() ?? 'FILE'
}
</script>

<template>
  <div>
    <div v-if="!attachments.length" class="py-4 text-center text-sm text-gray-400">
      No attachments yet.
    </div>

    <ul class="flex flex-col gap-2">
      <li
        v-for="attachment in attachments"
        :key="attachment.id"
        class="flex items-center gap-3 rounded-lg border bg-white px-3 py-2 shadow-sm"
      >
        <!-- File type badge -->
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-500">
          {{ fileExtension(attachment.filename) }}
        </span>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <p class="truncate text-sm font-medium text-gray-900">{{ attachment.filename }}</p>
          <p v-if="attachment.size" class="text-xs text-gray-400">
            {{ formatFileSize(attachment.size) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex shrink-0 items-center gap-2">
          <a
            v-if="attachment.url"
            :href="attachment.url"
            download
            class="text-sm text-primary-600 hover:text-primary-800"
            aria-label="Download file"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </a>
          <button
            class="text-gray-400 hover:text-red-600 transition-colors"
            :aria-label="`Delete ${attachment.filename}`"
            @click="emit('delete', attachment.id)"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>
