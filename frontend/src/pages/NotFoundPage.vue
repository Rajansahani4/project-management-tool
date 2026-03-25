<script setup>
import { useRouter } from 'vue-router'
import AppButton from '@/components/common/AppButton.vue'
import { Home, FolderOpen, ArrowLeft } from 'lucide-vue-next'

const router = useRouter()
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-[#F4F5F7] px-4">
    <div class="text-center max-w-md">
      <!-- 404 number -->
      <div class="mb-4 text-[120px] font-extrabold leading-none text-[#DFE1E6] select-none">
        404
      </div>

      <div class="mb-3 flex h-20 w-20 mx-auto items-center justify-center rounded-full bg-[#DEEBFF]">
        <FolderOpen class="h-10 w-10 text-primary-600" />
      </div>

      <h1 class="mb-2 text-2xl font-bold text-[#172B4D]">Page not found</h1>
      <p class="mb-8 text-sm text-[#6B778C]">
        The page you're looking for doesn't exist or you don't have permission to view it.
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <AppButton size="lg" @click="router.push({ name: 'dashboard' })">
          <Home class="h-4 w-4" />
          Go to Dashboard
        </AppButton>
        <AppButton variant="secondary" size="lg" @click="router.back()">
          <ArrowLeft class="h-4 w-4" />
          Go Back
        </AppButton>
      </div>
    </div>
  </div>
</template>
