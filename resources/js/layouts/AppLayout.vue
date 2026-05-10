<template>
  <div style="display:flex; height:100vh; overflow:hidden; background:var(--bg-primary);" class="app-layout">

    <!-- Mobile Menu Toggle -->
    <button 
      @click="toggleSidebar" 
      class="mobile-menu-toggle"
      :class="{ 'sidebar-open': sidebarOpen }"
      title="Toggle Menu"
    >
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 6h14M3 10h14M3 14h14"/>
      </svg>
    </button>

    <!-- Mobile Overlay -->
    <div 
      v-if="sidebarOpen" 
      class="mobile-overlay" 
      @click="closeSidebar"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="sidebar"
      :class="{ 'sidebar-open': sidebarOpen }"
      style="width:240px; background:var(--bg-secondary); border-right:1px solid var(--border-color);
                    display:flex; flex-direction:column; padding:16px; gap:8px; flex-shrink:0; height:100vh; overflow-y:auto; min-height:600px;">

      <!-- Brand -->
      <div style="display:flex; align-items:center; gap:12px; padding:12px; margin-bottom:8px;
                  border-radius:12px; background:var(--accent-color);">
        <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:8px;
                    display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; color:#000;">
          🍽️
        </div>
        <div>
          <div style="font-weight:700; font-size:18px; color:#000;">CoreLogixPOS</div>
          <div style="font-size:12px; color:rgba(0,0,0,0.7);">Restaurant Management</div>
        </div>
      </div>

      <!-- Navigation Sections -->
      <div style="flex:1; display:flex; flex-direction:column; gap:20px; overflow-y:auto; padding-bottom:20px;">
        
        <!-- Main Navigation -->
        <div>
          <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted);
                      font-weight:600; margin-bottom:8px; padding:0 8px;">
            Main
          </div>
          <div style="display:flex; flex-direction:column; gap:2px;">
            <NavBtn :to="{ name: 'dashboard' }"     icon="dashboard"  label="Dashboard"  :wide="true" />
            <NavBtn :to="{ name: 'tables' }"        icon="grid"       label="Tables"     :wide="true" />
            <NavBtn :to="{ name: 'direct-order' }" icon="arrow"      label="Takeaway Order" :wide="true" />
            <NavBtn :to="{ name: 'kitchen' }"      icon="chef"       label="Kitchen"    :wide="true" />
          </div>
        </div>

        <!-- Management (Admin only) -->
        <div v-if="auth.isAdmin">
          <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted);
                        font-weight:600; margin-bottom:8px; padding:0 8px;">
            Management
          </div>
          <div style="display:flex; flex-direction:column; gap:2px;">
            <NavBtn :to="{ name: 'menu' }"         icon="menu"       label="Menu Management" :wide="true" />
            <NavBtn :to="{ name: 'staff' }"        icon="users"      label="Staff Management" :wide="true" />
            <NavBtn :to="{ name: 'reports' }"      icon="chart"      label="Reports"          :wide="true" />
            <NavBtn :to="{ name: 'recent-orders' }" icon="receipt"    label="Recent Orders"     :wide="true" />
          </div>
        </div>
      </div>

      <!-- Bottom Section -->
      <div style="display:flex; flex-direction:column; gap:12px; margin-top:8px; flex-shrink:0;">
        
        <!-- Theme Toggle -->
        <button
          @click="toggleTheme"
          style="width:100%; height:40px; border-radius:8px; border:none;
                 background:var(--bg-tertiary); cursor:pointer; display:flex;
                 align-items:center; justify-content:flex-start; padding:0 12px; gap:12px;
                 color:var(--text-secondary); font-size:14px; transition:all 0.15s;"
          @mouseenter="e => { e.currentTarget.style.background='rgba(245,158,11,0.1)'; e.currentTarget.style.color='var(--accent-color)' }"
          @mouseleave="e => { e.currentTarget.style.background='var(--bg-tertiary)'; e.currentTarget.style.color='var(--text-secondary)' }"
          :title="isDarkTheme ? 'Switch to Light Theme' : 'Switch to Dark Theme'"
        >
          <span style="font-size:16px;">{{ isDarkTheme ? '🌙' : '☀️' }}</span>
          <span style="font-weight:500;">{{ isDarkTheme ? 'Dark Mode' : 'Light Mode' }}</span>
        </button>

        <!-- User Info -->
        <div style="display:flex; align-items:center; gap:12px; padding:8px 12px;
                    background:var(--bg-tertiary); border-radius:8px;" 
             :title="auth.user?.role + ' - ' + auth.user?.name">
          <div style="width:32px; height:32px; background:var(--accent-color); border-radius:50%; 
                      display:flex; align-items:center; justify-content:center; color:white; 
                      font-weight:700; font-size:14px; text-transform:uppercase; flex-shrink:0;">
            {{ auth.user?.name?.charAt(0) || 'U' }}
          </div>
          <div style="flex:1; min-width:0;">
            <div style="font-size:14px; font-weight:500; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ auth.user?.name || 'Admin User' }}
            </div>
            <div style="font-size:11px; color:var(--text-secondary); text-transform:capitalize;">
              {{ auth.user?.role || 'admin' }}
            </div>
            <!-- Session Timer -->
            <div style="font-size:10px; font-family:monospace;" :style="{ color: sessionColor }">
              Session: {{ formatSessionTime() }}
            </div>
          </div>
        </div>

        <!-- Account Settings -->
        <router-link :to="{ name: 'account-settings' }" 
                     style="width:100%; height:40px; border-radius:8px; border:1px solid var(--border-color);
                            background:var(--bg-tertiary); cursor:pointer; display:flex;
                            align-items:center; justify-content:flex-start; padding:0 12px; gap:12px;
                            color:var(--text-secondary); font-size:14px; font-weight:500; transition:all 0.15s;
                            text-decoration:none;"
                     @mouseenter="e => { e.currentTarget.style.background='var(--bg-primary)'; e.currentTarget.style.color='var(--text-primary)' }"
                     @mouseleave="e => { e.currentTarget.style.background='var(--bg-tertiary)'; e.currentTarget.style.color='var(--text-secondary)' }"
                     title="Account Settings">
          <span style="font-size:16px;">⚙️</span>
          <span>Account Settings</span>
        </router-link>

        <!-- Logout -->
        <button
          @click="handleLogout"
          style="width:100%; height:40px; border-radius:8px; border:none;
                 background:rgba(239,68,68,0.1); cursor:pointer; display:flex;
                 align-items:center; justify-content:flex-start; padding:0 12px; gap:12px;
                 color:var(--error-color); font-size:14px; font-weight:500; transition:all 0.15s;"
          @mouseenter="e => { e.currentTarget.style.background='rgba(239,68,68,0.2)'; }"
          @mouseleave="e => { e.currentTarget.style.background='rgba(239,68,68,0.1)'; }"
          title="Logout"
        >
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
  <path d="M13.5 8h-7m0 0l2-2m-2 2l2 2M8 13.5v1a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1h4a1 1 0 011 1v1"/>
</svg>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main style="flex:1; overflow-y:auto;">

      <router-view v-slot="{ Component }">
        <keep-alive>
          <component :is="Component" />
        </keep-alive>
      </router-view>
    </main>

  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter }    from 'vue-router'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import NavBtn           from '@/components/UI/NavBtn.vue'
import { useActivityTracker } from '@/composables/useActivityTracker'

const auth   = useAuthStore()
const router = useRouter()
const currentTime = ref(Date.now())
const isDarkTheme = ref(localStorage.getItem('theme') !== 'light')
const sidebarOpen = ref(false)
let timeInterval = null

// Start activity tracking
useActivityTracker()

// Toggle theme
function toggleTheme() {
  isDarkTheme.value = !isDarkTheme.value
  applyTheme()
}

// Apply theme to document
function applyTheme() {
  const theme = isDarkTheme.value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  
  if (isDarkTheme.value) {
    document.documentElement.classList.remove('light-theme')
    document.documentElement.classList.add('dark-theme')
    document.documentElement.style.setProperty('--bg-primary', '#0A0C10')
    document.documentElement.style.setProperty('--bg-secondary', '#12151C')
    document.documentElement.style.setProperty('--bg-tertiary', '#1A1E28')
    document.documentElement.style.setProperty('--border-color', '#252B38')
    document.documentElement.style.setProperty('--text-primary', '#F1F5F9')
    document.documentElement.style.setProperty('--text-secondary', '#64748B')
    document.documentElement.style.setProperty('--text-muted', '#94A3B8')
    document.documentElement.style.setProperty('--accent-color', '#F59E0B')
    document.documentElement.style.setProperty('--success-color', '#10B981')
    document.documentElement.style.setProperty('--error-color', '#EF4444')
    document.documentElement.style.setProperty('--warning-color', '#F59E0B')
    document.documentElement.style.setProperty('--info-color', '#3B82F6')
    document.body.style.background = 'var(--bg-primary)'
    document.body.style.color = 'var(--text-primary)'
  } else {
    document.documentElement.classList.remove('dark-theme')
    document.documentElement.classList.add('light-theme')
    document.documentElement.style.setProperty('--bg-primary', '#FFFFFF')
    document.documentElement.style.setProperty('--bg-secondary', '#F8FAFC')
    document.documentElement.style.setProperty('--bg-tertiary', '#F1F5F9')
    document.documentElement.style.setProperty('--border-color', '#E2E8F0')
    document.documentElement.style.setProperty('--text-primary', '#1A1E28')
    document.documentElement.style.setProperty('--text-secondary', '#64748B')
    document.documentElement.style.setProperty('--text-muted', '#94A3B8')
    document.documentElement.style.setProperty('--accent-color', '#F59E0B')
    document.documentElement.style.setProperty('--success-color', '#10B981')
    document.documentElement.style.setProperty('--error-color', '#EF4444')
    document.documentElement.style.setProperty('--warning-color', '#F59E0B')
    document.documentElement.style.setProperty('--info-color', '#3B82F6')
    document.body.style.background = 'var(--bg-primary)'
    document.body.style.color = 'var(--text-primary)'
  }
}

// Update current time every second
onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = Date.now()
  }, 1000)
  
  // Apply theme on mount
  applyTheme()
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})

// Format session remaining time
function formatSessionTime() {
  if (!auth.isLoggedIn) return '--:--'
  
  const SESSION_TIMEOUT = 10 * 60 * 1000 // 10 minutes
  const elapsed = currentTime.value - auth.lastActivity
  const remaining = Math.max(0, SESSION_TIMEOUT - elapsed)
  
  const minutes = Math.floor(remaining / 60000)
  const seconds = Math.floor((remaining % 60000) / 1000)
  
  return `${minutes}:${seconds.toString().padStart(2, '0')}`
}

// Session status color
const sessionColor = computed(() => {
  if (!auth.isLoggedIn) return '#64748B'
  
  const SESSION_TIMEOUT = 10 * 60 * 1000
  const elapsed = currentTime.value - auth.lastActivity
  const remaining = SESSION_TIMEOUT - elapsed
  
  if (remaining < 2 * 60 * 1000) return '#EF4444' // Red: less than 2 minutes
  if (remaining < 5 * 60 * 1000) return '#F59E0B' // Orange: less than 5 minutes
  return '#10B981' // Green: 5+ minutes
})

// Sidebar toggle functions
function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

// Close sidebar when route changes on mobile
router.afterEach(() => {
  if (window.innerWidth < 768) {
    closeSidebar()
  }
})

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
/* Mobile menu toggle button */
.mobile-menu-toggle {
  display: none;
  position: fixed;
  top: 16px;
  left: 16px;
  z-index: 1001;
  width: 44px;
  height: 44px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-primary);
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.mobile-menu-toggle:hover {
  background: var(--bg-tertiary);
  transform: scale(1.05);
}

.mobile-menu-toggle.sidebar-open {
  background: var(--accent-color);
  color: #000;
}

/* Mobile overlay */
.mobile-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  backdrop-filter: blur(2px);
}

/* Sidebar responsive behavior */
.sidebar {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

/* Tablet styles */
@media (max-width: 1024px) {
  .sidebar {
    width: 220px;
    padding: 12px;
  }
  
  .sidebar > div:first-child {
    padding: 10px;
    margin-bottom: 6px;
  }
  
  .sidebar > div:first-child div:first-child {
    width: 36px;
    height: 36px;
    font-size: 14px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:first-child {
    font-size: 16px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:last-child {
    font-size: 11px;
  }
}

/* Mobile styles */
@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: flex;
  }
  
  .mobile-overlay {
    display: block;
  }
  
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    z-index: 1001;
    transform: translateX(-100%);
    opacity: 0;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.15);
  }
  
  .sidebar.sidebar-open {
    transform: translateX(0);
    opacity: 1;
  }
  
  .sidebar > div:first-child {
    padding: 16px;
    margin-bottom: 12px;
  }
  
  .sidebar > div:nth-child(2) {
    gap: 24px;
    padding-bottom: 24px;
  }
  
  .sidebar > div:last-child {
    gap: 14px;
    margin-top: 12px;
  }
}

/* Small mobile styles */
@media (max-width: 480px) {
  .mobile-menu-toggle {
    top: 12px;
    left: 12px;
    width: 40px;
    height: 40px;
  }
  
  .sidebar {
    width: 260px;
  }
  
  .sidebar > div:first-child {
    padding: 12px;
    gap: 10px;
  }
  
  .sidebar > div:first-child div:first-child {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:first-child {
    font-size: 14px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:last-child {
    font-size: 10px;
  }
  
  .sidebar > div:nth-child(2) {
    gap: 20px;
  }
  
  .sidebar > div:last-child {
    gap: 12px;
    margin-top: 10px;
  }
  
  .sidebar button {
    height: 36px;
    font-size: 13px;
    padding: 0 10px;
  }
  
  .sidebar button span {
    font-size: 13px;
  }
}

/* Very small mobile styles */
@media (max-width: 360px) {
  .sidebar {
    width: 240px;
  }
  
  .sidebar > div:first-child {
    padding: 10px;
    gap: 8px;
  }
  
  .sidebar > div:first-child div:first-child {
    width: 28px;
    height: 28px;
    font-size: 11px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:first-child {
    font-size: 13px;
  }
  
  .sidebar > div:first-child div:nth-child(2) div:last-child {
    font-size: 9px;
  }
  
  .sidebar > div:nth-child(2) {
    gap: 16px;
  }
  
  .sidebar > div:last-child {
    gap: 10px;
  }
  
  .sidebar button {
    height: 34px;
    font-size: 12px;
    padding: 0 8px;
  }
  
  .sidebar button span {
    font-size: 12px;
  }
}

/* Ensure sidebar has proper height and scrolling on all screen sizes */
@media (min-height: 600px) {
  aside {
    min-height: 100vh;
  }
}

@media (max-height: 800px) {
  aside {
    min-height: 100vh;
    overflow-y: visible;
  }
}

/* Touch-friendly improvements for mobile */
@media (max-width: 768px) {
  .sidebar button,
  .sidebar a {
    min-height: 44px;
    padding: 10px 12px;
    font-size: 14px;
  }
  
  .sidebar button:hover,
  .sidebar a:hover {
    transform: scale(1.02);
  }
}

/* Landscape mobile adjustments */
@media (max-width: 768px) and (max-height: 500px) {
  .sidebar {
    width: 300px;
  }
  
  .sidebar > div:first-child {
    padding: 8px;
    margin-bottom: 8px;
  }
  
  .sidebar > div:nth-child(2) {
    gap: 16px;
  }
  
  .sidebar > div:last-child {
    gap: 8px;
    margin-top: 8px;
  }
}
</style>