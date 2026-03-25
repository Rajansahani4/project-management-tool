<script setup>
import { ChevronRight, Home } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'

defineProps({
  items: { type: Array, required: true },  // [{label, to?, icon?}]
})
</script>

<template>
  <nav class="flex items-center gap-1 text-sm" aria-label="Breadcrumb">
    <ol class="flex items-center gap-1 flex-wrap">
      <li
        v-for="(item, i) in items"
        :key="i"
        class="flex items-center gap-1"
      >
        <ChevronRight v-if="i > 0" class="h-3.5 w-3.5 shrink-0 text-[#97A0AF]" />
        <RouterLink
          v-if="item.to"
          :to="item.to"
          class="flex items-center gap-1 text-[#6B778C] hover:text-[#172B4D] hover:underline transition-colors"
        >
          <component v-if="item.icon" :is="item.icon" class="h-3.5 w-3.5 shrink-0" />
          {{ item.label }}
        </RouterLink>
        <span v-else class="flex items-center gap-1 font-medium text-[#172B4D]">
          <component v-if="item.icon" :is="item.icon" class="h-3.5 w-3.5 shrink-0" />
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>
