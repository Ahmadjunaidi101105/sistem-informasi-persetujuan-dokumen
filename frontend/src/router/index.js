import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // Public landing
  {
    path: '/',
    name: 'landing',
    component: () => import('@/pages/LandingPage.vue'),
    meta: { public: true },
  },
  // Auth (public)
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/RegisterPage.vue'),
    meta: { guest: true },
  },
  // Pemohon routes
  {
    path: '/pemohon',
    meta: { requiresAuth: true, role: 'pemohon' },
    children: [
      { path: 'dashboard', name: 'pemohon.dashboard', component: () => import('@/pages/pemohon/DashboardPage.vue') },
      { path: 'projects', name: 'pemohon.projects', component: () => import('@/pages/pemohon/ProjectListPage.vue') },
      { path: 'projects/create', name: 'pemohon.projects.create', component: () => import('@/pages/pemohon/ProjectCreatePage.vue') },
      { path: 'projects/:id/edit', name: 'pemohon.projects.edit', component: () => import('@/pages/pemohon/ProjectEditPage.vue') },
      { path: 'projects/:id', name: 'pemohon.projects.show', component: () => import('@/pages/pemohon/ProjectDetailPage.vue') },
    ],
  },
  // Penilai routes
  {
    path: '/penilai',
    meta: { requiresAuth: true, role: 'penilai' },
    children: [
      { path: 'dashboard', name: 'penilai.dashboard', component: () => import('@/pages/penilai/DashboardPage.vue') },
      { path: 'submissions', name: 'penilai.submissions', component: () => import('@/pages/penilai/SubmissionListPage.vue') },
      { path: 'submissions/:id/review', name: 'penilai.review', component: () => import('@/pages/penilai/ReviewPage.vue') },
      { path: 'history', name: 'penilai.history', component: () => import('@/pages/penilai/ReviewHistoryPage.vue') },
    ],
  },
  // Shared
  { path: '/notifications', name: 'notifications', component: () => import('@/pages/NotificationsPage.vue'), meta: { requiresAuth: true } },
  // Unknown routes fall back to the public landing page
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  // Anchor links on the landing page must scroll to their section, while
  // ordinary navigation should always start at the top.
  scrollBehavior(to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  },
})

// Navigation guards
let authChecked = false
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Only fetch user data once on initial app load (not every navigation)
  if (!authChecked) {
    authChecked = true
    await authStore.checkAuth()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }
  if (to.meta.guest && authStore.isAuthenticated) {
    return next(authStore.isPemohon ? { name: 'pemohon.dashboard' } : { name: 'penilai.dashboard' })
  }
  if (to.meta.role && !authStore.hasRole(to.meta.role)) {
    return next(authStore.isPemohon ? { name: 'pemohon.dashboard' } : { name: 'penilai.dashboard' })
  }
  next()
})

export default router
