<script setup>
import { computed } from 'vue'
import AppSpinner from './AppSpinner.vue'

const props = defineProps({
  variant:   { type: String, default: 'primary' },   // primary|secondary|danger|ghost|success|subtle
  size:      { type: String, default: 'md' },          // xs|sm|md|lg
  loading:   { type: Boolean, default: false },
  disabled:  { type: Boolean, default: false },
  fullWidth: { type: Boolean, default: false },
  type:      { type: String, default: 'button' },
})

const emit = defineEmits(['click'])

const base = 'inline-flex items-center justify-center gap-1.5 font-medium rounded transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50 select-none'

const variantMap = {
  primary:   'bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 focus:ring-primary-500',
  secondary: 'bg-[#F4F5F7] text-[#172B4D] border border-[#DFE1E6] hover:bg-[#EBECF0] active:bg-[#DFE1E6] focus:ring-primary-500',
  danger:    'bg-[#DE350B] text-white hover:bg-[#BF2600] active:bg-[#BF2600] focus:ring-red-500',
  ghost:     'bg-transparent text-primary-600 hover:bg-primary-50 active:bg-primary-100 focus:ring-primary-500',
  success:   'bg-[#00875A] text-white hover:bg-[#006644] active:bg-[#006644] focus:ring-green-500',
  subtle:    'bg-transparent text-[#6B778C] hover:bg-[#F4F5F7] hover:text-[#172B4D] active:bg-[#EBECF0] focus:ring-gray-400',
}

const sizeMap = {
  xs: 'px-2 py-0.5 text-xs h-6',
  sm: 'px-2.5 py-1 text-xs h-7',
  md: 'px-3 py-1.5 text-sm h-8',
  lg: 'px-4 py-2 text-sm h-10',
}

const spinnerSize = { xs: 'xs', sm: 'xs', md: 'sm', lg: 'sm' }

const classes = computed(() => [
  base,
  variantMap[props.variant] ?? variantMap.secondary,
  sizeMap[props.size] ?? sizeMap.md,
  props.fullWidth && 'w-full',
])

function handleClick(e) {
  if (!props.disabled && !props.loading) emit('click', e)
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="classes"
    @click="handleClick"
  >
    <AppSpinner v-if="loading" :size="spinnerSize[size]" class="shrink-0" />
    <slot />
  </button>
</template>
