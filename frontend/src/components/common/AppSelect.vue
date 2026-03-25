<script setup>
defineProps({
  modelValue:  { type: [String, Number], default: '' },
  label:       { type: String, default: '' },
  options:     { type: Array, default: () => [] },  // [{value, label}]
  placeholder: { type: String, default: 'Select…' },
  error:       { type: String, default: '' },
  hint:        { type: String, default: '' },
  disabled:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
  id:          { type: String, default: () => `select-${Math.random().toString(36).slice(2)}` },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-xs font-semibold text-[#172B4D]">
      {{ label }}<span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <select
      :id="id"
      :value="modelValue"
      :disabled="disabled"
      :required="required"
      :class="[
        'block w-full rounded border bg-white px-3 py-1.5 text-sm text-[#172B4D] transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
        'disabled:cursor-not-allowed disabled:bg-[#F4F5F7] disabled:text-[#6B778C]',
        error ? 'border-[#DE350B]' : 'border-[#DFE1E6] hover:border-[#97A0AF]',
        !modelValue ? 'text-[#97A0AF]' : 'text-[#172B4D]',
      ]"
      @change="emit('update:modelValue', $event.target.value)"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>

    <p v-if="error" class="text-xs text-[#DE350B]">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-[#6B778C]">{{ hint }}</p>
  </div>
</template>
