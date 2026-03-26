<script setup>
import { ref } from 'vue'
import AppAvatar from '@/components/common/AppAvatar.vue'
import AppButton from '@/components/common/AppButton.vue'
import { formatDateTime } from '@/utils/formatters.js'

const props = defineProps({
  comment: { type: Object,  required: true },
  isOwn:   { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'delete'])

const editing     = ref(false)
const editContent = ref('')

function startEdit() {
  editContent.value = props.comment.content
  editing.value = true
}

function cancelEdit() {
  editing.value = false
  editContent.value = ''
}

function saveEdit() {
  const trimmed = editContent.value.trim()
  if (!trimmed) return
  emit('edit', { id: props.comment.id, content: trimmed })
  editing.value = false
}
</script>

<template>
  <div class="flex gap-3">
    <AppAvatar :name="comment.author?.name ?? ''" size="sm" class="shrink-0 mt-0.5" />

    <div class="flex-1 min-w-0">
      <!-- Header -->
      <div class="flex items-baseline gap-2">
        <span class="text-sm font-medium text-gray-900">{{ comment.author?.name }}</span>
        <time :datetime="comment.created_at" class="text-xs text-gray-400">
          {{ formatDateTime(comment.created_at) }}
        </time>
        <span
          v-if="Math.abs(new Date(comment.updated_at) - new Date(comment.created_at)) > 2000"
          class="text-xs text-gray-300 italic"
        >
          (edited)
        </span>
      </div>

      <!-- View mode -->
      <template v-if="!editing">
        <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700 leading-relaxed">{{ comment.content }}</p>

        <div v-if="isOwn" class="mt-1.5 flex items-center gap-2">
          <button
            class="text-xs text-gray-400 hover:text-primary-600 transition-colors"
            @click="startEdit"
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
      </template>

      <!-- Inline edit mode -->
      <div v-else class="mt-2 space-y-2">
        <textarea
          v-model="editContent"
          rows="3"
          class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @keydown.ctrl.enter="saveEdit"
          @keydown.meta.enter="saveEdit"
          @keydown.esc="cancelEdit"
        />
        <div class="flex items-center gap-2">
          <AppButton size="sm" :disabled="!editContent.trim()" @click="saveEdit">
            Save
          </AppButton>
          <AppButton size="sm" variant="secondary" @click="cancelEdit">
            Cancel
          </AppButton>
          <span class="text-xs text-gray-400">Ctrl+Enter to save</span>
        </div>
      </div>
    </div>
  </div>
</template>
