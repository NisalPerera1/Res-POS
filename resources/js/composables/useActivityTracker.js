import { onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useActivityTracker() {
  const authStore = useAuthStore()

  // Track user activity events
  const activityEvents = [
    'mousedown',
    'mousemove',
    'keypress',
    'scroll',
    'touchstart',
    'click',
    'keydown',
    'keyup'
  ]

  let throttledUpdate = null

  // Throttle activity updates to avoid excessive calls
  function throttleActivityUpdate() {
    if (!throttledUpdate) {
      throttledUpdate = setTimeout(() => {
        authStore.trackActivity()
        throttledUpdate = null
      }, 1000) // Update at most once per second
    }
  }

  // Activity event handler
  function handleActivity() {
    if (authStore.isLoggedIn) {
      throttleActivityUpdate()
    }
  }

  // Start tracking activity
  function startActivityTracking() {
    activityEvents.forEach(event => {
      document.addEventListener(event, handleActivity, { passive: true })
    })
    console.log('Activity tracking started')
  }

  // Stop tracking activity
  function stopActivityTracking() {
    activityEvents.forEach(event => {
      document.removeEventListener(event, handleActivity)
    })
    if (throttledUpdate) {
      clearTimeout(throttledUpdate)
      throttledUpdate = null
    }
    console.log('Activity tracking stopped')
  }

  // Lifecycle hooks
  onMounted(() => {
    startActivityTracking()
  })

  onUnmounted(() => {
    stopActivityTracking()
  })

  return {
    startActivityTracking,
    stopActivityTracking
  }
}
