<script setup>
import { reactive, watch } from 'vue'
import AppSelect from '@/components/common/AppSelect.vue'
import AppButton from '@/components/common/AppButton.vue'

/** @type {{ activeFilters?: { status?: string, priority?: string, assignee?: string|number, due_date?: string }, members?: Array<{value: string|number, label: string}> }} */
const props = defineProps({
  activeFilters: { type: Object, default: () => ({}) },
  members:       { type: Array,  default: () => [] },
})

/** @type {(event: 'filters-changed', filters: object) => void} */
const emit = defineEmits(['filters-changed'])

const statusOptions = [
  { value: 'todo',        label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed',   label: 'Completed' },
  { value: 'archived',    label: 'Archived' },
]

const priorityOptions = [
  { value: 'low',    label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High' },
]

const dueDateOptions = [
  { value: 'today',     label: 'Today' },
  { value: 'this_week', label: 'This Week' },
  { value: 'overdue',   label: 'Overdue' },
]

const filters = reactive({
  status:   props.activeFilters.status   ?? '',
  priority: props.activeFilters.priority ?? '',
  assignee: props.activeFilters.assignee ?? '',
  due_date: props.activeFilters.due_date ?? '',
})

watch(filters, (val) => {
  emit('filters-changed', { ...val })
})

function clearAll() {
  filters.status   = ''
  filters.priority = ''
  filters.assignee = ''
  filters.due_date = ''
}

const hasActiveFilters = () =>
  Object.values(filters).some(v => v !== '')
</script>

<template>
  <div class="flex flex-wrap items-end gap-3">
    <AppSelect
      v-model="filters.status"
      label="Status"
      placeholder="All statuses"
      :options="statusOptions"
    />
    <AppSelect
      v-model="filters.priority"
      label="Priority"
      placeholder="All priorities"
      :options="priorityOptions"
    />
    <AppSelect
      v-if="members.length"
      v-model="filters.assignee"
      label="Assignee"
      placeholder="All assignees"
      :options="members"
    />
    <AppSelect
      v-model="filters.due_date"
      label="Due Date"
      placeholder="Any date"
      :options="dueDateOptions"
    />

    <AppButton
      v-if="hasActiveFilters()"
      variant="ghost"
      size="sm"
      class="self-end"
      @click="clearAll"
    >
      Clear filters
    </AppButton>
  </div>
</template>
