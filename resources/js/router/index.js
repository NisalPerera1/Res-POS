import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginPIN.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '',          name: 'tables',  component: () => import('@/views/TableView.vue') },
      { path: 'pos/:tableId', name: 'pos', component: () => import('@/views/POSScreen.vue') },
      { path: 'pos/direct/:orderId', name: 'direct-pos', component: () => import('@/views/POSScreen.vue') },
      { path: 'kitchen',   name: 'kitchen', component: () => import('@/views/KitchenDisplay.vue') },
      { path: 'menu',      name: 'menu',    component: () => import('@/views/MenuManager.vue') },
      { path: 'reports',   name: 'reports', component: () => import('@/views/Reports.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory('/'),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    next('/login')
  } else if (to.meta.guest && auth.isLoggedIn) {
    next('/')
  } else {
    next()
  }
})

export default router