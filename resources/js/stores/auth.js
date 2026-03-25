import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'))
  const token = ref(localStorage.getItem('pos_token') || null)
  const lastActivity = ref(parseInt(localStorage.getItem('pos_last_activity') || '0'))
  
  // Session timeout: 10 minutes in milliseconds
  const SESSION_TIMEOUT = 10 * 60 * 1000
  let timeoutWarning = null
  let logoutTimer = null

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isKitchen  = computed(() => user.value?.role === 'kitchen')
  const isSessionExpired = computed(() => {
    if (!isLoggedIn.value) return false
    const now = Date.now()
    const timeSinceActivity = now - lastActivity.value
    return timeSinceActivity > SESSION_TIMEOUT
  })

  // ← Set token on store init if it exists in localStorage
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    // Check session on init
    if (isSessionExpired.value) {
      console.log('Session expired on init, logging out')
      clearSession()
    } else {
      startSessionTimer()
    }
  }

  function updateLastActivity() {
    lastActivity.value = Date.now()
    localStorage.setItem('pos_last_activity', lastActivity.value.toString())
    resetSessionTimer()
  }

  function startSessionTimer() {
    clearSessionTimer()
    
    // Show warning at 8 minutes (2 minutes before timeout)
    timeoutWarning = setTimeout(() => {
      showSessionWarning()
    }, SESSION_TIMEOUT - 2 * 60 * 1000) // 8 minutes
    
    // Auto logout at 10 minutes
    logoutTimer = setTimeout(() => {
      console.log('Session timed out, auto-logging out')
      logout()
      showTimeoutMessage()
    }, SESSION_TIMEOUT)
  }

  function resetSessionTimer() {
    if (isLoggedIn.value) {
      startSessionTimer()
    }
  }

  function clearSessionTimer() {
    if (timeoutWarning) {
      clearTimeout(timeoutWarning)
      timeoutWarning = null
    }
    if (logoutTimer) {
      clearTimeout(logoutTimer)
      logoutTimer = null
    }
  }

  function showSessionWarning() {
    if (isLoggedIn.value) {
      const warning = confirm('⚠️ Session will expire in 2 minutes!\n\nClick OK to extend your session.')
      if (warning) {
        updateLastActivity()
        console.log('Session extended by user')
      }
    }
  }

  function showTimeoutMessage() {
    alert('🔒 Session expired due to inactivity\n\nPlease login again to continue.')
  }

  async function loginWithPin(pin) {
    const { data } = await axios.post('/login-pin', { pin })
    setSession(data)
    return data
  }

  function setSession({ user: u, token: t }) {
    user.value  = u
    token.value = t
    lastActivity.value = Date.now()
    localStorage.setItem('pos_user',  JSON.stringify(u))
    localStorage.setItem('pos_token', t)
    localStorage.setItem('pos_last_activity', lastActivity.value.toString())
    // ← Update axios header immediately after login
    axios.defaults.headers.common['Authorization'] = `Bearer ${t}`
    console.log('Token set:', t)
    startSessionTimer()
  }

  async function logout() {
    clearSessionTimer()
    try {
      await axios.post('/logout')
    } catch (e) {
      console.warn('Logout API failed:', e.message)
    } finally {
      clearSession()
    }
  }

  function clearSession() {
    clearSessionTimer()
    user.value  = null
    token.value = null
    lastActivity.value = 0
    localStorage.removeItem('pos_user')
    localStorage.removeItem('pos_token')
    localStorage.removeItem('pos_last_activity')
    delete axios.defaults.headers.common['Authorization']
  }

  // Activity tracking functions
  function trackActivity() {
    if (isLoggedIn.value) {
      updateLastActivity()
    }
  }

  return {
    user, token, lastActivity, isLoggedIn, isAdmin, isKitchen, isSessionExpired,
    loginWithPin, logout, clearSession, trackActivity, updateLastActivity
  }
})