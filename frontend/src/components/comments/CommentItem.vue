<script setup>
import AppAvatar from '@/components/common/AppAvatar.vue'
import AppButton from '@/components/common/AppButton.vue'
import { formatDateTime } from '@/utils/formatters.js'

/** @type {{ comment: { id: number, content: string, author: { name: string }, created_at: string }, isOwn?: boolean }} */
defineProps({
  comment: { type: Object,  required: true },
  isOwn:   { type: Boolean, default: false },
})

/** @type {(event: 'edit' | 'delete', id: number) => void} */
const emit = defineEmits(['edit', 'delete'])
</script>

<template>
  <div class="flex gap-3">
    <AppAvatar :name="comment.author?.name ?? ''" size="sm" class="shrink-0 mt-0.5" />

    <div class="flex-1 min-w-0">
      <div class="flex items-baseline gap-2">
        <span class="text-sm font-medium text-gray-900">{{ comment.author?.name }}</span>
        <time
          :datetime="comment.created_at"
          class="text-xs text-gray-400"
        >
          {{ formatDateTime(comment.created_at) }}
        </time>
      </div>

      <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700">{{ comment.content }}</p>

      <div v-if="isOwn" class="mt-1 flex items-center gap-2">
        <button
          class="text-xs text-gray-400 hover:text-primary-600 transition-colors"
          @click="emit('edit', comment.id)"
        >
          Edit
        </button>
        <span class="text-gray-200">|</span>
        <button
          class="text-xs text-gray-400 hover:text-red-600 transition-colors"
          @click="emit('delete', comment.id)"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>
