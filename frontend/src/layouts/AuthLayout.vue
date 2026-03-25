<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
    <!-- Logo -->
    <div class="mb-8 flex flex-col items-center gap-3">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 text-white font-bold text-xl backdrop-blur-sm">
        PM
      </div>
      <h1 class="text-2xl font-bold text-white">ProjectFlow</h1>
      <p class="text-sm text-white/70">Project Management for Modern Teams</p>
    </div>

    <!-- Card -->
    <div class="w-full max-w-md rounded-xl bg-white shadow-2xl">
      <RouterView />
    </div>

    <p class="mt-6 text-xs text-white/50">© 2025 ProjectFlow. All rights reserved.</p>
  </div>
</template>
