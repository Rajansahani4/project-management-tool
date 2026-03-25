/**
 * Canonical enum values that mirror the Laravel backend.
 * Import from here to guarantee frontend/backend alignment.
 */

/** @type {{ value: string, label: string }[]} */
export const TASK_STATUSES = [
  { value: 'todo',        label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed',   label: 'Completed' },
  { value: 'archived',    label: 'Archived' },
]

/** @type {{ value: string, label: string }[]} */
export const TASK_PRIORITIES = [
  { value: 'low',    label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high',   label: 'High' },
]

/** @type {{ value: string, label: string }[]} */
export const PROJECT_ROLES = [
  { value: 'owner',  label: 'Owner' },
  { value: 'admin',  label: 'Admin' },
  { value: 'member', label: 'Member' },
]

/** Map task status value → Tailwind badge classes */
export const STATUS_CLASS = {
  todo:        'bg-gray-100 text-gray-600',
  in_progress: 'bg-blue-100 text-blue-700',
  completed:   'bg-green-100 text-green-700',
  archived:    'bg-gray-200 text-gray-500',
}

/** Map priority value → Tailwind badge classes */
export const PRIORITY_CLASS = {
  low:    'bg-gray-100 text-gray-600',
  medium: 'bg-yellow-100 text-yellow-700',
  high:   'bg-red-100 text-red-700',
}

/** Map role value → Tailwind badge classes */
export const ROLE_CLASS = {
  owner:  'bg-purple-100 text-purple-700',
  admin:  'bg-blue-100 text-blue-700',
  member: 'bg-gray-100 text-gray-600',
}
