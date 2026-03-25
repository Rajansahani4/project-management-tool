<script setup>
import { onMounted, computed, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import { useProjectStore } from '@/stores/projects.js'
import { useMembersStore } from '@/stores/members.js'
import { useAuthStore } from '@/stores/auth.js'
import { useUiStore } from '@/stores/ui.js'
import AppSpinner from '@/components/common/AppSpinner.vue'
import AppButton  from '@/components/common/AppButton.vue'
import AppAvatar  from '@/components/common/AppAvatar.vue'

const props = defineProps({
  projectId: { type: String, required: true },
})

const projectStore = useProjectStore()
const membersStore = useMembersStore()
const authStore    = useAuthStore()
const ui           = useUiStore()

const project = computed(() => projectStore.current)
const members = computed(() => membersStore.members)
const loading = computed(() => membersStore.loading)

// ── Add member form ──────────────────────────────────────────────────────────
const addForm    = reactive({ email: '', role: 'member' })
const addLoading = ref(false)
const addErrors  = computed(() => membersStore.errors)

const roleOptions = [
  { value: 'admin',  label: 'Admin' },
  { value: 'member', label: 'Member' },
]

async function submitAdd() {
  if (!addForm.email.trim()) {
    ui.error('Please enter a valid email address.')
    return
  }
  addLoading.value = true
  try {
    await membersStore.add(props.projectId, { ...addForm })
    addForm.email = ''
    addForm.role  = 'member'
    ui.success('Member added successfully.')
  } catch {
    ui.error('Failed to add member. Please check the email and try again.')
  } finally {
    addLoading.value = false
  }
}

// ── Change role ──────────────────────────────────────────────────────────────
async function changeRole(userId, role) {
  try {
    await membersStore.updateRole(props.projectId, userId, role)
    ui.success('Role updated.')
  } catch {
    ui.error('Failed to update role.')
  }
}

// ── Remove member ────────────────────────────────────────────────────────────
const removingId = ref(null)

async function removeMember(userId) {
  removingId.value = userId
  try {
    await membersStore.remove(props.projectId, userId)
    ui.success('Member removed.')
  } catch {
    ui.error('Failed to remove member.')
  } finally {
    removingId.value = null
  }
}

// ── Role badge ───────────────────────────────────────────────────────────────
function roleBadgeClass(role) {
  return {
    'bg-purple-100 text-purple-700': role === 'owner',
    'bg-blue-100 text-blue-700':     role === 'admin',
    'bg-gray-100 text-gray-600':     role === 'member',
  }
}

// ── Is current user the owner ────────────────────────────────────────────────
const isOwner = computed(() => {
  const me = members.value.find(
    m => m.user?.id === authStore.user?.id || m.user_id === authStore.user?.id
  )
  return me?.role === 'owner'
})

onMounted(async () => {
  await Promise.all([
    projectStore.fetchOne(props.projectId),
    membersStore.fetchAll(props.projectId),
  ])
})
</script>

<template>
  <div class="mx-auto max-w-3xl space-y-6">

    <!-- ── Breadcrumb ───────────────────────────────────────────────────── -->
    <nav class="flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
      <RouterLink :to="{ name: 'dashboard' }" class="hover:text-primary-600 transition-colors">Dashboard</RouterLink>
      <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      <RouterLink
        :to="{ name: 'project-show', params: { id: projectId } }"
        class="hover:text-primary-600 transition-colors truncate"
      >
        {{ project?.name ?? 'Project' }}
      </RouterLink>
      <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      <span class="font-medium text-gray-900">Team</span>
    </nav>

    <!-- ── Page header ───────────────────────────────────────────────────── -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Team Management</h1>
      <p class="mt-1 text-sm text-gray-500">
        Manage who has access to <strong>{{ project?.name }}</strong>.
      </p>
    </div>

    <!-- ── Add member card (owner only) ─────────────────────────────────── -->
    <div v-if="isOwner" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
      <h2 class="mb-4 text-base font-semibold text-gray-900">Add Team Member</h2>
      <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submitAdd" novalidate>
        <div class="flex-1">
          <label for="member-email" class="mb-1.5 block text-sm font-medium text-gray-700">
            Email address
          </label>
          <input
            id="member-email"
            v-model="addForm.email"
            type="email"
            placeholder="colleague@example.com"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500"
            :class="addErrors.email ? 'border-red-400 focus:ring-red-400' : ''"
          />
          <p v-if="addErrors.email" class="mt-1.5 text-xs text-red-600">{{ addErrors.email[0] }}</p>
        </div>
        <div class="sm:w-40">
          <label for="member-role" class="mb-1.5 block text-sm font-medium text-gray-700">Role</label>
          <select
            id="member-role"
            v-model="addForm.role"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
        <AppButton type="submit" :loading="addLoading" class="shrink-0">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          Add Member
        </AppButton>
      </form>
    </div>

    <!-- ── Members list ──────────────────────────────────────────────────── -->
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
      <div class="border-b px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">
          Members
          <span v-if="!loading" class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
            {{ members.length }}
          </span>
        </h2>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <AppSpinner size="md" />
      </div>

      <!-- Empty -->
      <div v-else-if="members.length === 0" class="py-12 text-center text-sm text-gray-400">
        No team members yet.
      </div>

      <!-- Member rows -->
      <ul v-else class="divide-y divide-gray-50">
        <li
          v-for="member in members"
          :key="member.user?.id ?? member.user_id"
          class="flex items-center gap-4 px-6 py-4"
        >
          <!-- Avatar -->
          <AppAvatar :name="member.user?.name ?? ''" size="sm" />

          <!-- Name + email -->
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">
              {{ member.user?.name ?? 'Unknown User' }}
              <span v-if="(member.user?.id ?? member.user_id) === authStore.user?.id" class="ml-1.5 text-xs text-gray-400">(you)</span>
            </p>
            <p class="truncate text-xs text-gray-500">{{ member.user?.email ?? '' }}</p>
          </div>

          <!-- Role badge / selector -->
          <div class="shrink-0">
            <!-- Owner can change roles of non-owners -->
            <select
              v-if="isOwner && member.role !== 'owner'"
              :value="member.role"
              class="rounded-md border border-gray-200 py-1 pl-2 pr-7 text-xs font-medium text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="changeRole(member.user?.id ?? member.user_id, $event.target.value)"
            >
              <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <span v-else class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="roleBadgeClass(member.role)">
              {{ member.role }}
            </span>
          </div>

          <!-- Remove button (owner only, not self) -->
          <button
            v-if="isOwner && member.role !== 'owner'"
            class="shrink-0 rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-400"
            :disabled="removingId === (member.user?.id ?? member.user_id)"
            :aria-label="`Remove ${member.user?.name}`"
            @click="removeMember(member.user?.id ?? member.user_id)"
          >
            <svg v-if="removingId === (member.user?.id ?? member.user_id)" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
            </svg>
          </button>
        </li>
      </ul>
    </div>

    <!-- ── Back link ────────────────────────────────────────────────────── -->
    <RouterLink
      :to="{ name: 'project-show', params: { id: projectId } }"
      class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      Back to Project
    </RouterLink>

  </div>
</template>
