<script setup>
import { ref, provide } from 'vue'
import { RouterView } from 'vue-router'
import AppSidebar from '@/components/common/AppSidebar.vue'
import AppHeader from '@/components/common/AppHeader.vue'
import NotificationList from '@/components/common/NotificationList.vue'

const sidebarOpen     = ref(false)
const sidebarCollapsed = ref(false)

function toggleMobileSidebar() { sidebarOpen.value = !sidebarOpen.value }
function toggleCollapse()      { sidebarCollapsed.value = !sidebarCollapsed.value }

provide('sidebarCollapsed', sidebarCollapsed)
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-brand-body">
    <!-- Mobile overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-30 bg-black/50 lg:hidden"
        aria-hidden="true"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-40 flex flex-col bg-brand-sidebar transition-all duration-200 ease-in-out',
        'lg:relative lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-16' : 'w-[250px]',
      ]"
    >
      <AppSidebar
        :collapsed="sidebarCollapsed"
        @toggle-collapse="toggleCollapse"
        @close="sidebarOpen = false"
      />
    </aside>

    <!-- Main column -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
      <AppHeader @toggle-sidebar="toggleMobileSidebar" />
      <main class="flex-1 overflow-y-auto px-6 pt-4">
        <RouterView />
      </main>
    </div>

    <NotificationList />
  </div>
</template>
