<script setup>
import CommentItem from '@/components/comments/CommentItem.vue'
import CommentForm from '@/components/comments/CommentForm.vue'
import AppSpinner  from '@/components/common/AppSpinner.vue'

defineProps({
  comments:    { type: Array,   default: () => [] },
  loading:     { type: Boolean, default: false },
  submitting:  { type: Boolean, default: false },
})

const emit = defineEmits(['submit', 'edit', 'delete'])
</script>

<template>
  <div>
    <!-- Loading comments -->
    <div v-if="loading" class="flex justify-center py-8">
      <AppSpinner size="md" />
    </div>

    <!-- Comment list (oldest-first) -->
    <div v-else class="flex flex-col gap-5">
      <div v-if="!comments.length" class="py-6 text-center text-sm text-gray-400">
        No comments yet. Be the first to comment!
      </div>

      <CommentItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        :is-own="comment.is_own ?? false"
        @edit="emit('edit', $event)"
        @delete="emit('delete', $event)"
      />
    </div>

    <!-- Comment form -->
    <div class="mt-5 border-t border-gray-100 pt-4">
      <CommentForm :loading="submitting" @submit="emit('submit', $event)" />
    </div>
  </div>
</template>
