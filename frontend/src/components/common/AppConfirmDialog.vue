<script setup>
import AppModal from './AppModal.vue'
import AppButton from './AppButton.vue'
import { TriangleAlert } from 'lucide-vue-next'

const props = defineProps({
  open:          { type: Boolean, required: true },
  title:         { type: String, default: 'Confirm Action' },
  message:       { type: String, default: 'Are you sure?' },
  confirmLabel:  { type: String, default: 'Confirm' },
  cancelLabel:   { type: String, default: 'Cancel' },
  variant:       { type: String, default: 'danger' },
  loading:       { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <AppModal :open="open" size="sm" :can-close="!loading" @close="emit('cancel')">
    <template #header>
      <div class="flex items-center gap-3">
        <div
          :class="[
            'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
            variant === 'danger' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600',
          ]"
        >
          <TriangleAlert class="h-5 w-5" />
        </div>
        <span class="font-semibold text-[#172B4D]">{{ title }}</span>
      </div>
    </template>

    <p class="text-sm text-[#6B778C]">{{ message }}</p>

    <template #footer>
      <AppButton variant="secondary" :disabled="loading" @click="emit('cancel')">
        {{ cancelLabel }}
      </AppButton>
      <AppButton
        :variant="variant === 'danger' ? 'danger' : 'primary'"
        :loading="loading"
        @click="emit('confirm')"
      >
        {{ confirmLabel }}
      </AppButton>
    </template>
  </AppModal>
</template>
