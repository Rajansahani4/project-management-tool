<script setup>
import { useUiStore } from '@/stores/ui.js'
import Notification from './Notification.vue'

const ui = useUiStore()
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2 items-end"
      aria-live="polite"
    >
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0 scale-95"
        enter-to-class="translate-y-0 opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
        move-class="transition-all duration-200"
      >
        <Notification
          v-for="n in ui.notifications"
          :key="n.id"
          :notification="n"
          @dismiss="ui.dismiss"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>
