<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projects.js'
import { useUiStore } from '@/stores/ui.js'
import AppButton from '@/components/common/AppButton.vue'
import AppInput from '@/components/common/AppInput.vue'
import AppTextarea from '@/components/common/AppTextarea.vue'
import AppBreadcrumb from '@/components/common/AppBreadcrumb.vue'
import { LayoutDashboard, FolderOpen, FolderPlus, ArrowLeft } from 'lucide-vue-next'

const router       = useRouter()
const projectStore = useProjectStore()
const ui           = useUiStore()

const form   = reactive({ name: '', description: '' })
const saving = ref(false)

async function handleSubmit() {
  saving.value = true
  try {
    const project = await projectStore.create(form)
    ui.success(`Project "${form.name}" created!`)
    router.push({ name: 'project-show', params: { id: project.id ?? project.data?.id } })
  } catch (err) {
    // errors are surfaced via projectStore.errors
  } finally {
    saving.value = false
  }
}

const breadcrumbs = [
  { label: 'Dashboard',   to: { name: 'dashboard' },     icon: LayoutDashboard },
  { label: 'Projects',    to: { name: 'project-index' }, icon: FolderOpen },
  { label: 'New Project', icon: FolderPlus },
]
</script>

<template>
  <div class="max-w-2xl mx-auto px-6 py-6 space-y-6">
    <!-- Header -->
    <div>
      <AppBreadcrumb :items="breadcrumbs" class="mb-3" />
      <div class="flex items-center gap-3">
        <button
          class="rounded p-2 text-[#6B778C] hover:bg-[#F4F5F7] hover:text-[#172B4D] transition-colors"
          @click="router.back()"
        >
          <ArrowLeft class="h-5 w-5" />
        </button>
        <div>
          <h1 class="text-xl font-bold text-[#172B4D]">Create New Project</h1>
          <p class="text-sm text-[#6B778C]">Set up your project workspace</p>
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-xl border border-[#DFE1E6] bg-white shadow-card overflow-hidden">
      <!-- Card header -->
      <div class="border-b border-[#DFE1E6] bg-[#F4F5F7] px-6 py-4">
        <div class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0052CC] text-white">
            <FolderPlus class="h-4 w-4" />
          </div>
          <div>
            <p class="text-sm font-semibold text-[#172B4D]">Project Details</p>
            <p class="text-xs text-[#6B778C]">Required fields are marked with *</p>
          </div>
        </div>
      </div>

      <form class="p-6 space-y-5" @submit.prevent="handleSubmit">
        <!-- Project name preview avatar -->
        <div class="flex items-center gap-4 pb-4 border-b border-[#DFE1E6]">
          <div
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl font-bold uppercase text-white text-xl transition-all"
            :style="{
              backgroundColor: form.name
                ? `hsl(${(form.name.charCodeAt(0) * 37) % 360}, 60%, 45%)`
                : '#DFE1E6',
              color: form.name ? 'white' : '#97A0AF',
            }"
          >
            {{ form.name ? form.name.charAt(0).toUpperCase() : '?' }}
          </div>
          <div>
            <p class="text-sm font-semibold text-[#172B4D]">{{ form.name || 'Project Name' }}</p>
            <p class="text-xs text-[#6B778C]">Project icon is auto-generated from the name</p>
          </div>
        </div>

        <AppInput
          v-model="form.name"
          label="Project name"
          placeholder="e.g. Website Redesign, Mobile App v2"
          :error="projectStore.errors?.name?.[0]"
          required
          autofocus
        />

        <AppTextarea
          v-model="form.description"
          label="Description"
          placeholder="What is this project about? (optional)"
          :error="projectStore.errors?.description?.[0]"
          :rows="4"
          hint="A brief description helps team members understand the project goals"
        />

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-[#DFE1E6]">
          <AppButton variant="secondary" type="button" @click="router.back()">
            Cancel
          </AppButton>
          <AppButton type="submit" :loading="saving" :disabled="!form.name.trim()">
            <FolderPlus class="h-4 w-4" />
            Create Project
          </AppButton>
        </div>
      </form>
    </div>

    <!-- Tips -->
    <div class="rounded-xl border border-[#DFE1E6] bg-[#DEEBFF] p-4">
      <p class="mb-2 text-sm font-semibold text-[#0043A4]">Tips for great projects</p>
      <ul class="space-y-1 text-xs text-[#0052CC]">
        <li>• Use a clear, descriptive name that reflects the project's purpose</li>
        <li>• Add a description to help onboard new team members</li>
        <li>• After creating, you can invite members and start adding tasks</li>
      </ul>
    </div>
  </div>
</template>
