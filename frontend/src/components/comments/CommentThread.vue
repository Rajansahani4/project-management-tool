<script setup>
import CommentItem from '@/components/comments/CommentItem.vue'
import AppLoading  from '@/components/common/AppLoading.vue'

/** @type {{ comments?: Array<object>, loading?: boolean }} */
defineProps({
  comments: { type: Array,   default: () => [] },
  loading:  { type: Boolean, default: false },
})

const emit = defineEmits(['edit', 'delete'])
</script>

<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-8">
      <AppLoading size="md" />
    </div>

    <!-- Comment list (oldest first) -->
    <div v-else class="flex flex-col gap-4">
      <div
        v-if="!comments.length"
        class="py-6 text-center text-sm text-gray-400"
      >
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

    <!-- Form slot -->
    <div class="mt-6 border-t pt-4">
      <slot />
    </div>
  </div>
</template>
