import { createApp }   from 'vue'
import { createPinia } from 'pinia'
import router          from './router/index.js'
import App             from './App.vue'
import axios           from 'axios'
import { useOrderStore } from './stores/orders'
import '../css/app.css'

// ── Axios global config ──────────────────────────────
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept']       = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.withCredentials = false

// ── Restore token from localStorage on every page load ──
const savedToken = localStorage.getItem('pos_token')
if (savedToken) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`
  console.log('Token restored from localStorage')
}

// ── Axios response interceptor ───────────────────────
// Auto-logout if token expires (401 on protected routes)
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      const url = error.config?.url ?? ''
      // Don't redirect on login or user-list endpoints
      if (!url.includes('login-pin') && !url.includes('users/list')) {
        console.warn('401 received — clearing session and redirecting to login')
        localStorage.removeItem('pos_token')
        localStorage.removeItem('pos_user')
        delete axios.defaults.headers.common['Authorization']
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)

// Initialize order store after mounting to ensure localStorage is available
app.mount('#app')

// Initialize order store from localStorage
const orderStore = useOrderStore(pinia)
orderStore.initializeFromStorage()