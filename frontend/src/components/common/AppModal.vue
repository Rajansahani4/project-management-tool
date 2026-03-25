<script setup>
import { onMounted, onUnmounted } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  open:     { type: Boolean, required: true },
  title:    { type: String, default: '' },
  size:     { type: String, default: 'md' },  // sm|md|lg|xl|full
  canClose: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const sizeMap = {
  sm:   'max-w-sm',
  md:   'max-w-lg',
  lg:   'max-w-2xl',
  xl:   'max-w-4xl',
  full: 'max-w-[95vw]',
}

function handleKeydown(e) {
  if (e.key === 'Escape' && props.canClose) emit('close')
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-[1px]"
          @click="canClose && emit('close')"
        />

        <!-- Panel -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="scale-95 opacity-0"
          enter-to-class="scale-100 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-95 opacity-0"
        >
          <div
            v-if="open"
            :class="['relative w-full rounded-xl bg-white shadow-2xl flex flex-col max-h-[90vh]', sizeMap[size] ?? sizeMap.md]"
            role="dialog"
            :aria-label="title"
          >
            <!-- Header -->
            <div v-if="title || $slots.header" class="flex items-center justify-between border-b border-[#DFE1E6] px-6 py-4 shrink-0">
              <div class="flex-1 min-w-0">
                <slot name="header">
                  <h2 class="text-base font-semibold text-[#172B4D] truncate">{{ title }}</h2>
                </slot>
              </div>
              <button
                v-if="canClose"
                class="ml-4 shrink-0 rounded p-1 text-[#6B778C] hover:bg-[#F4F5F7] hover:text-[#172B4D] transition-colors"
                aria-label="Close"
                @click="emit('close')"
              >
                <X class="h-4 w-4" />
              </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
              <slot />
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="flex items-center justify-end gap-2 border-t border-[#DFE1E6] px-6 py-4 shrink-0"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
