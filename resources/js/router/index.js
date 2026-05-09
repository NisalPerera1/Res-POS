import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/pages/LoginPIN.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/pages/Dashboard.vue'),
      },
      {
        path: 'tables',
        name: 'tables',
        component: () => import('@/views/components/TableView.vue'),
      },
      {
        path: 'pos/:tableId',
        name: 'pos',
        component: () => import('@/views/components/POSScreen.vue'),
      },
      {
        path: 'pos/direct/:orderId',
        name: 'pos-direct',
        component: () => import('@/views/components/POSScreen.vue'),
      },
      {
        path: 'kitchen',
        name: 'kitchen',
        component: () => import('@/views/components/KitchenDisplay.vue'),
      },
      {
        path: 'direct',
        name: 'direct-order',
        component: () => import('@/views/components/DirectOrderImproved.vue'),
      },
      {
        path: 'menu',
        name: 'menu',
        component: () => import('@/views/admin/MenuManager.vue'),
      },
      {
        path: 'reports',
        name: 'reports',
        component: () => import('@/views/admin/Reports.vue'),
      },

{ path: 'staff', name: 'staff', component: () => import('@/views/admin/StaffManagement.vue') },
      { path: 'recent-orders', name: 'recent-orders', component: () => import('@/views/components/RecentOrders.vue') },
      { path: 'account-settings', name: 'account-settings', component: () => import('@/views/pages/AccountSettings.vue') },



    ],
  },
]

const router = createRouter({
  history: createWebHistory('/admin'),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    next({ name: 'login' })
  } else if (to.meta.guest && auth.isLoggedIn) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router