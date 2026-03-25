import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useProjectStore } from '@/stores/projects.js'

const routes = [
  // ─── Public / guest ──────────────────────────────────────────────────────
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/LoginPage.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/RegisterPage.vue'),
    meta: { requiresGuest: true },
  },

  // ─── Authenticated shell ──────────────────────────────────────────────────
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: { name: 'dashboard' } },

      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
      },
      {
        path: 'projects',
        name: 'project-index',
        component: () => import('@/pages/projects/ProjectIndexPage.vue'),
      },
      {
        path: 'projects/create',
        name: 'project-create',
        component: () => import('@/pages/projects/ProjectCreatePage.vue'),
      },

      // ── Project-scoped routes (require project membership check) ─────────
      {
        path: 'projects/:id',
        name: 'project-show',
        component: () => import('@/pages/projects/ProjectShowPage.vue'),
        props: true,
        meta: { requiresProjectAccess: true },
      },
      {
        path: 'projects/:id/team',
        name: 'team-management',
        component: () => import('@/pages/projects/TeamManagementPage.vue'),
        props: (route) => ({ projectId: route.params.id }),
        meta: { requiresProjectAccess: true },
      },
      {
        path: 'projects/:projectId/tasks/:taskId',
        name: 'task-show',
        component: () => import('@/pages/tasks/TaskShowPage.vue'),
        props: true,
        meta: { requiresProjectAccess: true },
      },

      {
        path: 'settings',
        name: 'settings',
        component: () => import('@/pages/settings/SettingsPage.vue'),
      },
    ],
  },

  // ─── 404 ─────────────────────────────────────────────────────────────────
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFoundPage.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// ─── Navigation guard ─────────────────────────────────────────────────────────
router.beforeEach(async (to) => {
  const auth    = useAuthStore()
  const projects = useProjectStore()

  // ── 1. Hydrate user from persisted token on first navigation ─────────────
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser()
    } catch {
      // Token is invalid/expired — clear it and force login
      auth.clearSession()
      if (to.meta.requiresAuth) {
        return { name: 'login', query: { redirect: to.fullPath } }
      }
    }
  }

  // ── 2. Route requires authentication ─────────────────────────────────────
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  // ── 3. Route requires guest (login / register) ───────────────────────────
  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  // ── 4. Project-scoped pages: verify the project exists and user has access ─
  if (to.meta.requiresProjectAccess && auth.isAuthenticated) {
    const projectId = to.params.id ?? to.params.projectId
    if (projectId) {
      // Use cached current project if it matches; otherwise fetch
      if (projects.current?.id !== Number(projectId)) {
        try {
          await projects.fetchOne(projectId)
        } catch (err) {
          // 403 = not a member; 404 = doesn't exist
          if (err?.status === 403 || err?.status === 404) {
            return { name: 'not-found' }
          }
        }
      }
    }
  }
})

export default router
