<script setup>
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next'

const props = defineProps({
  notification: { type: Object, required: true },
})

const emit = defineEmits(['dismiss'])

const typeMap = {
  success: { icon: CheckCircle,   bg: 'bg-[#E3FCEF]', border: 'border-[#ABF5D1]', icon_color: 'text-[#00875A]', text: 'text-[#006644]' },
  error:   { icon: XCircle,       bg: 'bg-[#FFEBE6]', border: 'border-[#FFBDAD]', icon_color: 'text-[#DE350B]', text: 'text-[#BF2600]' },
  warning: { icon: AlertTriangle, bg: 'bg-[#FFFAE6]', border: 'border-[#FFE380]', icon_color: 'text-[#FF8B00]', text: 'text-[#974F0C]' },
  info:    { icon: Info,          bg: 'bg-[#DEEBFF]', border: 'border-[#B3D4FF]', icon_color: 'text-[#0052CC]', text: 'text-[#0747A6]' },
}

const config = typeMap[props.notification.type] ?? typeMap.info
</script>

<template>
  <div
    :class="[
      'flex items-start gap-3 rounded-lg border p-3 shadow-panel w-80 max-w-full',
      config.bg, config.border,
    ]"
    role="alert"
  >
    <component :is="config.icon" :class="['h-5 w-5 shrink-0 mt-0.5', config.icon_color]" />
    <p :class="['flex-1 text-sm font-medium', config.text]">{{ notification.message }}</p>
    <button
      :class="['shrink-0 rounded p-0.5 transition-colors', config.icon_color, 'hover:opacity-70']"
      aria-label="Dismiss"
      @click="emit('dismiss', notification.id)"
    >
      <X class="h-4 w-4" />
    </button>
  </div>
</template>
