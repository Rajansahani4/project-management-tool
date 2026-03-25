<script setup>
import AppButton from './AppButton.vue'

defineProps({
  icon:        { type: Object, default: null },       // lucide component
  title:       { type: String, default: 'Nothing here yet' },
  description: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
  actionIcon:  { type: Object, default: null },
})

const emit = defineEmits(['action'])
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
    <div
      v-if="icon"
      class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#F4F5F7] text-[#97A0AF]"
    >
      <component :is="icon" class="h-8 w-8" />
    </div>
    <h3 class="mb-2 text-base font-semibold text-[#172B4D]">{{ title }}</h3>
    <p v-if="description" class="mb-6 max-w-sm text-sm text-[#6B778C]">{{ description }}</p>
    <slot>
      <AppButton v-if="actionLabel" @click="emit('action')">
        <component v-if="actionIcon" :is="actionIcon" class="h-4 w-4" />
        {{ actionLabel }}
      </AppButton>
    </slot>
  </div>
</template>
