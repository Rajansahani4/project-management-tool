<script setup>
import { computed } from 'vue'

const props = defineProps({
  name:     { type: String, default: '' },
  initials: { type: String, default: '' },
  size:     { type: String, default: 'md' },  // xs|sm|md|lg|xl
  color:    { type: String, default: '' },
})

const sizeMap = {
  xs: 'h-5 w-5 text-[10px]',
  sm: 'h-7 w-7 text-xs',
  md: 'h-8 w-8 text-sm',
  lg: 'h-10 w-10 text-sm',
  xl: 'h-12 w-12 text-base',
}

const palette = [
  '#0052CC', '#00875A', '#6554C0', '#DE350B', '#FF8B00',
  '#00B8D9', '#36B37E', '#FF5630', '#6B778C', '#0065FF',
]

const displayInitials = computed(() => {
  if (props.initials) return props.initials.slice(0, 2).toUpperCase()
  if (!props.name)    return '?'
  return props.name
    .split(' ')
    .map(w => w[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

const bgColor = computed(() => {
  if (props.color) return props.color
  if (!props.name)  return '#6B778C'
  let hash = 0
  for (let i = 0; i < props.name.length; i++) hash = props.name.charCodeAt(i) + ((hash << 5) - hash)
  return palette[Math.abs(hash) % palette.length]
})
</script>

<template>
  <span
    :class="[
      'inline-flex shrink-0 items-center justify-center rounded-full font-semibold text-white select-none',
      sizeMap[size] ?? sizeMap.md,
    ]"
    :style="{ backgroundColor: bgColor }"
    :title="name || undefined"
    aria-hidden="true"
  >
    {{ displayInitials }}
  </span>
</template>
