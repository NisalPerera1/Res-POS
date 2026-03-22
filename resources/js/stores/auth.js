import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'))
  const token = ref(localStorage.getItem('pos_token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isKitchen  = computed(() => user.value?.role === 'kitchen')

  // ← Set token on store init if it exists in localStorage
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  async function loginWithPin(pin) {
    const { data } = await axios.post('/login-pin', { pin })
    setSession(data)
    return data
  }

  function setSession({ user: u, token: t }) {
    user.value  = u
    token.value = t
    localStorage.setItem('pos_user',  JSON.stringify(u))
    localStorage.setItem('pos_token', t)
    // ← Update axios header immediately after login
    axios.defaults.headers.common['Authorization'] = `Bearer ${t}`
    console.log('Token set:', t)
  }

  async function logout() {
    try {
      await axios.post('/logout')
    } catch (e) {
      console.warn('Logout API failed:', e.message)
    } finally {
      clearSession()
    }
  }

  function clearSession() {
    user.value  = null
    token.value = null
    localStorage.removeItem('pos_user')
    localStorage.removeItem('pos_token')
    delete axios.defaults.headers.common['Authorization']
  }

  return {
    user, token, isLoggedIn, isAdmin, isKitchen,
    loginWithPin, logout, clearSession
  }
})