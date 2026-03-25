<script setup>
defineProps({
  label:   { type: String, default: '' },
  variant: { type: String, default: 'default' },  // default|primary|success|danger|warning|info|todo|in_progress|completed|archived|low|medium|high
  size:    { type: String, default: 'md' },
  dot:     { type: Boolean, default: false },
})

const variantMap = {
  default:     'bg-[#DFE1E6] text-[#42526E]',
  primary:     'bg-[#DEEBFF] text-[#0052CC]',
  success:     'bg-[#E3FCEF] text-[#006644]',
  danger:      'bg-[#FFEBE6] text-[#BF2600]',
  warning:     'bg-[#FFFAE6] text-[#974F0C]',
  info:        'bg-[#E6FCFF] text-[#006884]',
  // Status variants
  todo:        'bg-[#DFE1E6] text-[#42526E]',
  in_progress: 'bg-[#DEEBFF] text-[#0052CC]',
  completed:   'bg-[#E3FCEF] text-[#006644]',
  archived:    'bg-[#DFE1E6] text-[#6B778C]',
  // Priority
  low:         'bg-[#E3FCEF] text-[#006644]',
  medium:      'bg-[#FFFAE6] text-[#974F0C]',
  high:        'bg-[#FFEBE6] text-[#BF2600]',
}

const dotColorMap = {
  default:     'bg-[#6B778C]',
  primary:     'bg-[#0052CC]',
  success:     'bg-[#00875A]',
  danger:      'bg-[#DE350B]',
  warning:     'bg-[#F59E0B]',
  info:        'bg-[#0065FF]',
  todo:        'bg-[#6B778C]',
  in_progress: 'bg-[#0052CC]',
  completed:   'bg-[#00875A]',
  archived:    'bg-[#97A0AF]',
  low:         'bg-[#00875A]',
  medium:      'bg-[#F59E0B]',
  high:        'bg-[#DE350B]',
}

const sizeMap = {
  sm: 'px-1.5 py-0.5 text-[11px]',
  md: 'px-2 py-0.5 text-xs',
  lg: 'px-2.5 py-1 text-xs',
}
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1 rounded font-medium whitespace-nowrap',
      variantMap[variant] ?? variantMap.default,
      sizeMap[size] ?? sizeMap.md,
    ]"
  >
    <span
      v-if="dot"
      :class="['h-1.5 w-1.5 rounded-full shrink-0', dotColorMap[variant] ?? dotColorMap.default]"
    />
    <slot>{{ label }}</slot>
  </span>
</template>
