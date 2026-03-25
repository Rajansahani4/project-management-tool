<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label:      { type: String, default: '' },
  type:       { type: String, default: 'text' },
  placeholder:{ type: String, default: '' },
  error:      { type: String, default: '' },
  hint:       { type: String, default: '' },
  disabled:   { type: Boolean, default: false },
  required:   { type: Boolean, default: false },
  clearable:  { type: Boolean, default: false },
  id:         { type: String, default: () => `input-${Math.random().toString(36).slice(2)}` },
})

const emit = defineEmits(['update:modelValue', 'clear'])

const hasValue = computed(() => props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined)
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="label"
      :for="id"
      class="text-xs font-semibold text-[#172B4D]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <div class="relative">
      <!-- Left icon slot -->
      <div
        v-if="$slots.icon"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#6B778C]"
      >
        <slot name="icon" />
      </div>

      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'block w-full rounded border bg-white py-1.5 text-sm text-[#172B4D] placeholder-[#97A0AF] transition-colors',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
          'disabled:cursor-not-allowed disabled:bg-[#F4F5F7] disabled:text-[#6B778C]',
          error ? 'border-[#DE350B] focus:ring-red-400' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
          $slots.icon ? 'pl-9' : 'pl-3',
          clearable && hasValue ? 'pr-8' : 'pr-3',
        ]"
        @input="emit('update:modelValue', $event.target.value)"
      />

      <!-- Clear button -->
      <button
        v-if="clearable && hasValue && !disabled"
        type="button"
        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#6B778C] hover:text-[#172B4D]"
        @click="emit('update:modelValue', ''); emit('clear')"
      >
        <X class="h-3.5 w-3.5" />
      </button>
    </div>

    <p v-if="error" class="text-xs text-[#DE350B]">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-[#6B778C]">{{ hint }}</p>
  </div>
</template>
