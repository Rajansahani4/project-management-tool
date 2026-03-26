<script setup>
import { RouterView } from 'vue-router'
import { CheckCircle, Zap, Users, BarChart3 } from 'lucide-vue-next'

const features = [
  { icon: CheckCircle, text: 'Real-time task tracking and updates' },
  { icon: Users, text: 'Seamless team collaboration tools' },
  { icon: BarChart3, text: 'Insightful analytics and reports' },
  { icon: Zap, text: 'Lightning-fast and intuitive interface' },
]

const stats = [
  { value: '10K+', label: 'Teams worldwide' },
  { value: '99.9%', label: 'Uptime SLA' },
  { value: '4.9★', label: 'User rating' },
]
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 flex-col justify-between p-12">
      <!-- Background decorations -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute top-1/2 -right-48 w-[500px] h-[500px] rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-32 left-1/3 w-80 h-80 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <!-- Grid pattern -->
        <svg class="absolute inset-0 w-full h-full opacity-10" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
      </div>

      <!-- Logo -->
      <div class="relative z-10 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm border border-white/20 text-white font-bold text-lg shadow-lg">
          PM
        </div>
        <span class="text-xl font-bold text-white tracking-tight">ProjectFlow</span>
      </div>

      <!-- Hero content -->
      <div class="relative z-10 space-y-8">
        <div class="space-y-4">
          <h2 class="text-4xl xl:text-5xl font-bold text-white leading-tight">
            Manage projects<br/>
            <span class="text-violet-200">with confidence.</span>
          </h2>
          <p class="text-lg text-white/70 max-w-md leading-relaxed">
            Streamline your team's workflow, track progress in real-time, and deliver projects on time — every time.
          </p>
        </div>

        <!-- Feature highlights -->
        <div class="space-y-4">
          <div v-for="feature in features" :key="feature.text" class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/15 backdrop-blur-sm border border-white/10 shrink-0">
              <component :is="feature.icon" class="h-4 w-4 text-violet-200" />
            </div>
            <span class="text-white/80 text-sm">{{ feature.text }}</span>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="relative z-10 grid grid-cols-3 gap-6">
        <div v-for="stat in stats" :key="stat.label" class="text-center">
          <div class="text-2xl font-bold text-white">{{ stat.value }}</div>
          <div class="text-xs text-white/60 mt-0.5">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <!-- Right Panel - Form -->
    <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-8 bg-[#F8F9FC]">
      <!-- Mobile logo -->
      <div class="lg:hidden flex items-center gap-2 mb-8">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm">
          PM
        </div>
        <span class="text-lg font-bold text-gray-900">ProjectFlow</span>
      </div>

      <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/60 border border-gray-100 overflow-hidden">
          <RouterView />
        </div>

        <p class="mt-4 text-center text-xs text-gray-400">© 2025 ProjectFlow. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>
