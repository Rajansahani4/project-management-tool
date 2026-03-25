<script setup>
import { ref } from 'vue'
import AppTextarea from '@/components/common/AppTextarea.vue'
import AppButton   from '@/components/common/AppButton.vue'

/** @type {{ loading?: boolean, initialContent?: string }} */
const props = defineProps({
  loading:        { type: Boolean, default: false },
  initialContent: { type: String,  default: '' },
})

/** @type {(event: 'submit', content: string) => void} */
const emit = defineEmits(['submit'])

const content = ref(props.initialContent)

function handleSubmit() {
  const trimmed = content.value.trim()
  if (!trimmed) return
  emit('submit', trimmed)
  content.value = ''
}
</script>

<template>
  <form class="flex flex-col gap-2" @submit.prevent="handleSubmit">
    <AppTextarea
      v-model="content"
      placeholder="Write a comment..."
      :rows="3"
      :disabled="loading"
    />
    <div class="flex justify-end">
      <AppButton
        type="submit"
        :loading="loading"
        :disabled="!content.trim()"
        size="sm"
      >
        Post Comment
      </AppButton>
    </div>
  </form>
</template>
