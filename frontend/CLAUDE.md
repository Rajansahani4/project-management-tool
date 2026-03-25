# Project Management Tool — Frontend Standards

## Tech Stack
- **Vue 3** (Composition API with `<script setup>`)
- **Vite** (build tool)
- **Vue Router 4** (navigation)
- **Pinia** (state management)
- **Axios** (HTTP client)
- **Laravel Echo + Socket.io** (real-time updates)
- **Tailwind CSS** (styling)
- **Vitest + Vue Test Utils** (testing)

---

## Project Structure

```
frontend/
├── public/                        # Static assets served as-is
├── src/
│   ├── api/                       # One module per backend resource
│   │   ├── client.js              # Axios instance + interceptors
│   │   ├── auth.js
│   │   ├── projects.js
│   │   ├── tasks.js
│   │   ├── comments.js
│   │   └── attachments.js
│   ├── assets/                    # Images, fonts, global CSS
│   ├── components/                # Reusable UI components
│   │   ├── common/                # Generic, domain-agnostic UI
│   │   │   ├── AppButton.vue
│   │   │   ├── AppInput.vue
│   │   │   ├── AppModal.vue
│   │   │   ├── AppBadge.vue
│   │   │   └── AppSpinner.vue
│   │   ├── projects/
│   │   ├── tasks/
│   │   ├── comments/
│   │   └── attachments/
│   ├── composables/               # Reusable composition functions
│   │   ├── useAuth.js
│   │   ├── useForm.js
│   │   └── useEcho.js
│   ├── layouts/                   # Wrapping layout components
│   │   ├── AppLayout.vue          # Authenticated shell (nav + sidebar)
│   │   └── AuthLayout.vue         # Unauthenticated (login/register)
│   ├── pages/                     # One file per route — thin, delegate to components
│   │   ├── auth/
│   │   │   ├── LoginPage.vue
│   │   │   └── RegisterPage.vue
│   │   ├── projects/
│   │   │   ├── ProjectIndexPage.vue
│   │   │   ├── ProjectShowPage.vue
│   │   │   └── ProjectCreatePage.vue
│   │   └── tasks/
│   │       └── TaskShowPage.vue
│   ├── router/
│   │   └── index.js               # All routes + navigation guards
│   ├── stores/                    # Pinia stores — one per domain
│   │   ├── auth.js
│   │   ├── projects.js
│   │   ├── tasks.js
│   │   ├── comments.js
│   │   └── attachments.js
│   ├── utils/                     # Pure helper functions (no side effects)
│   │   ├── formatters.js          # Date, file size, etc.
│   │   └── validators.js
│   ├── App.vue
│   └── main.js
├── tests/
│   ├── unit/                      # Composable / utility tests
│   └── component/                 # Vue component tests
├── index.html
├── vite.config.js
├── tailwind.config.js
└── package.json
```

---

## Architecture Layers

### Pages — thin route controllers
- One `.vue` file per route under `src/pages/`
- Responsibilities: read route params, call store actions, render layout
- Must NOT contain: business logic, direct API calls, complex template logic
- Delegate everything to child components and stores

### Components — focused UI units
- Each component has a single, clear purpose
- Smart components (connect to store) live in `pages/` or top-level feature components
- Dumb/presentational components (props in, emits out) live in `components/`
- Maximum ~150 lines; split if larger

### Stores (Pinia) — all state and server interaction
- One store per domain resource (`useAuthStore`, `useProjectStore`, etc.)
- Stores own: API calls, loading/error state, cached data
- Use `actions` for mutations and async operations
- Use `getters` for derived/computed state
- Components never call `api/*` directly — always through a store action

### API Layer — HTTP only
- One module per resource in `src/api/`
- Functions are plain async functions returning response data
- No state, no side effects — only HTTP calls
- All requests go through the shared Axios client in `src/api/client.js`

### Composables — reusable logic
- Extract logic shared across multiple components/pages
- Prefix with `use`: `useForm`, `useAuth`, `useEcho`
- Return reactive refs and functions, never raw data

---

## Coding Conventions

### Single-File Components
- Always use `<script setup>` syntax
- Order: `<script setup>` → `<template>` → `<style>`
- Use `defineProps` / `defineEmits` with typed signatures

```vue
<script setup>
const props = defineProps({
  task: { type: Object, required: true },
})
const emit = defineEmits(['updated', 'deleted'])
</script>
```

### Naming
- **Pages**: `<Domain><Action>Page.vue` (e.g. `ProjectShowPage.vue`)
- **Components**: PascalCase, descriptive noun (e.g. `TaskCard.vue`, `CommentList.vue`)
- **Common components**: prefix with `App` (e.g. `AppButton.vue`, `AppModal.vue`)
- **Stores**: camelCase function name (e.g. `useProjectStore`)
- **Composables**: camelCase, `use` prefix (e.g. `useForm.js`)
- **API modules**: camelCase resource noun (e.g. `projects.js`)

### Props & Emits
- Always define props with types and `required`
- Use `v-model` only for form inputs; use explicit props + emits elsewhere
- Event names: `kebab-case` verbs (e.g. `task-updated`, `file-uploaded`)

---

## API Client (`src/api/client.js`)

- Single Axios instance with `baseURL: /api/v1`
- Request interceptor: attach `Authorization: Bearer <token>` from auth store
- Response interceptor: unwrap `response.data`, handle 401 (redirect to login), handle 422 (return validation errors to caller)
- All API functions return the `data` field from the response envelope

```js
// Usage pattern in a store
import { getTasks } from '@/api/tasks'

const fetchTasks = async (projectId) => {
  loading.value = true
  try {
    tasks.value = await getTasks(projectId)
  } finally {
    loading.value = false
  }
}
```

---

## State Management (Pinia)

- Store file exports a single `use<Resource>Store` composable
- Standard shape per store:

```js
export const useTaskStore = defineStore('tasks', () => {
  // State
  const tasks = ref([])
  const current = ref(null)
  const loading = ref(false)
  const errors = ref({})

  // Getters
  const byStatus = computed(() => (status) =>
    tasks.value.filter(t => t.status === status)
  )

  // Actions
  async function fetch(projectId) { ... }
  async function create(projectId, payload) { ... }
  async function update(taskId, payload) { ... }
  async function remove(taskId) { ... }

  return { tasks, current, loading, errors, byStatus, fetch, create, update, remove }
})
```

- Reset store state on logout (call `$reset()` or manually clear refs)

---

## Real-Time (Laravel Echo + Socket.io)

- Echo is initialised once in `src/composables/useEcho.js`
- Subscribe to private channels using the JWT token for auth
- Channel naming mirrors the backend: `private-task.{taskId}`
- Stores subscribe/unsubscribe in `onMounted`/`onUnmounted` of the relevant page

```js
// useEcho.js
import Echo from 'laravel-echo'
import io from 'socket.io-client'

export function useEcho() {
  const echo = new Echo({
    broadcaster: 'socket.io',
    host: import.meta.env.VITE_SOCKET_URL,
    auth: { headers: { Authorization: `Bearer ${token}` } },
  })
  return { echo }
}
```

```js
// Inside a page component
const { echo } = useEcho()
onMounted(() => {
  echo.private(`task.${taskId}`)
    .listen('CommentCreated', (e) => commentStore.addFromEvent(e.comment))
    .listen('AttachmentUploaded', (e) => attachmentStore.addFromEvent(e.attachment))
})
onUnmounted(() => echo.leave(`task.${taskId}`))
```

---

## Routing (`src/router/index.js`)

- All authenticated routes wrapped in a parent route that uses `AppLayout`
- Navigation guard: if no token → redirect to `/login`
- Route names are `kebab-case` nouns (e.g. `project-index`, `task-show`)
- Lazy-load all page components: `component: () => import('@/pages/...')`

```js
{
  path: '/projects/:projectId/tasks/:taskId',
  name: 'task-show',
  component: () => import('@/pages/tasks/TaskShowPage.vue'),
  meta: { requiresAuth: true },
}
```

---

## Styling (Tailwind CSS)

- Use utility classes directly in templates — no custom CSS unless unavoidable
- Responsive: mobile-first (`sm:`, `md:`, `lg:`)
- No `@apply` except in `src/assets/base.css` for truly global element styles
- Colour palette and spacing extended in `tailwind.config.js` — do not use arbitrary values (`[...]`) for colours or spacing
- Dark mode: class strategy (`dark:` prefix)

---

## Error Handling

- API 422 responses: interceptor extracts `errors` object and returns it; store sets `errors.value`
- API 401: interceptor clears auth store and redirects to login
- API 5xx: display a generic toast notification
- Form validation errors: bound directly from `store.errors` — never derive from component state

---

## Testing (Vitest + Vue Test Utils)

- Unit tests for composables and utils: `tests/unit/`
- Component tests for UI logic: `tests/component/`
- Test file naming: `<Subject>.test.js`
- Use `mountComponent` helpers; mock stores with `createTestingPinia`
- Cover: render output, user interactions, emitted events, store interactions
- Do NOT test implementation details (internal refs, private methods)

```js
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import TaskCard from '@/components/tasks/TaskCard.vue'

test('emits task-updated on save', async () => {
  const wrapper = mount(TaskCard, {
    props: { task: { id: 1, title: 'Fix bug', status: 'todo' } },
    global: { plugins: [createTestingPinia()] },
  })
  await wrapper.find('[data-testid="save-btn"]').trigger('click')
  expect(wrapper.emitted('task-updated')).toBeTruthy()
})
```

---

## Environment Variables

All env vars prefixed with `VITE_` (exposed to client bundle):

```
VITE_API_BASE_URL=http://localhost:8000
VITE_SOCKET_URL=http://localhost:6001
```

---

## Running Dev Server

```bash
cd frontend && npm install
cd frontend && npm run dev
```

## Running Tests

```bash
cd frontend && npm run test
cd frontend && npm run test:coverage
```

## Building for Production

```bash
cd frontend && npm run build
```
