<template>
  <div class="login-root">
    <!-- Ambient background grid -->
    <div class="bg-grid" />
    <div class="bg-glow" />

    <div class="login-wrap">

      <!-- ── Brand ── -->
      <div class="brand">
        <div class="brand-icon">🍽️</div>
        <div class="brand-name">RestoPOS</div>
        <div class="brand-sub">Who's working today?</div>
      </div>

      <!-- ── Loading ── -->
      <div v-if="loadingUsers" class="state-msg">
        <span class="spinner" />
        <span>Loading staff…</span>
      </div>

      <!-- ── Load Error ── -->
      <div v-else-if="loadError" class="state-error">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <circle cx="10" cy="10" r="9" stroke="#EF4444" stroke-width="1.5"/>
          <path d="M10 6v4M10 13.5v.5" stroke="#EF4444" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span>{{ loadError }}</span>
        <button class="retry-btn" @click="loadUsers">Retry</button>
      </div>

      <!-- ── Two-panel layout ── -->
      <div v-else class="two-panel">

        <!-- LEFT: user grid -->
        <div class="panel-users" :class="{ dimmed: !!selectedUser }">
          <div class="panel-label">Select profile</div>
          <div class="user-grid">
            <button
              v-for="user in users"
              :key="user.id"
              class="user-card"
              :class="{ active: selectedUser?.id === user.id }"
              @click="selectUser(user)"
            >
              <div class="avatar" :style="{ background: roleGradient(user.role) }">
                {{ initials(user.name) }}
              </div>
              <div class="user-name">{{ firstName(user.name) }}</div>
              <div class="user-role" :style="{ color: roleColor(user.role) }">
                {{ user.role }}
              </div>
            </button>
          </div>
        </div>

        <!-- Divider -->
        <div class="divider" />

        <!-- RIGHT: PIN panel -->
        <div class="panel-pin" :class="{ visible: !!selectedUser }">

          <!-- Empty state -->
          <div v-if="!selectedUser" class="pin-placeholder">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
              <rect x="6" y="14" width="20" height="14" rx="3" stroke="#334155" stroke-width="1.5"/>
              <path d="M10 14v-4a6 6 0 0 1 12 0v4" stroke="#334155" stroke-width="1.5" stroke-linecap="round"/>
              <circle cx="16" cy="21" r="2" fill="#334155"/>
            </svg>
            <span>Select a profile</span>
          </div>

          <template v-else>
            <!-- Selected user header -->
            <div class="pin-header">
              <div class="pin-avatar" :style="{ background: roleGradient(selectedUser.role) }">
                {{ initials(selectedUser.name) }}
              </div>
              <div>
                <div class="pin-name">{{ selectedUser.name }}</div>
                <div class="pin-role" :style="{ color: roleColor(selectedUser.role) }">
                  {{ selectedUser.role }}
                </div>
              </div>
              <button class="change-btn" @click="clearSelection">Change</button>
            </div>

            <!-- PIN dots -->
            <div class="pin-dots">
              <div
                v-for="i in 4" :key="i"
                class="dot"
                :class="{ filled: pin.length >= i, error: pinError }"
              />
            </div>

            <!-- PIN label -->
            <div class="pin-hint">
              {{ pinError ? pinError : 'Enter your 4-digit PIN' }}
            </div>

            <!-- Keyboard hint -->
            <div class="keyboard-hint">
              💡 You can also use your keyboard: 0-9, Backspace, Enter, Esc
            </div>

            <!-- Numpad -->
            <div class="numpad">
              <button
                v-for="key in pinKeys" :key="key"
                class="num-key"
                :class="{
                  'key-action': key === '⌫' || key === '↵',
                  'key-enter':  key === '↵',
                  'key-zero':   key === '0',
                }"
                @click="pressKey(key)"
                @mousedown="e => e.currentTarget.classList.add('pressed')"
                @mouseup="e => e.currentTarget.classList.remove('pressed')"
                @mouseleave="e => e.currentTarget.classList.remove('pressed')"
              >
                <template v-if="key === '⌫'">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M7 4H15a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H7l-4-5 4-5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                    <path d="M10 7l3 3M13 7l-3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                  </svg>
                </template>
                <template v-else-if="key === '↵'">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3 9h10M9 5l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </template>
                <template v-else>{{ key }}</template>
              </button>
            </div>
          </template>
        </div>
      </div>

      <!-- ── Demo hint ── -->
      <div class="demo-hint">
        Demo PINs — Admin: 1234 · Cashier: 2222 · Kitchen: 4444
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter }      from 'vue-router'
import { useAuthStore }   from '@/stores/auth'
import axios              from 'axios'

const router = useRouter()
const auth   = useAuthStore()

const users        = ref([])
const selectedUser = ref(null)
const pin          = ref('')
const pinError     = ref('')
const loadingUsers = ref(true)
const loadError    = ref('')
const pinKeys      = ['1','2','3','4','5','6','7','8','9','⌫','0','↵']

// ── Keyboard Event Handler ───────────────────────────────────────
function handleKeyboardInput(event) {
  // Only handle keyboard input when a user is selected
  if (!selectedUser.value) return
  
  const key = event.key
  
  // Prevent default for number keys and special keys
  if (/^[0-9]$/.test(key) || key === 'Backspace' || key === 'Enter' || key === 'Delete') {
    event.preventDefault()
  }
  
  // Handle number keys
  if (/^[0-9]$/.test(key)) {
    pressKey(key)
  }
  // Handle backspace and delete
  else if (key === 'Backspace' || key === 'Delete') {
    pressKey('⌫')
  }
  // Handle enter
  else if (key === 'Enter') {
    pressKey('↵')
  }
  // Handle escape to clear selection
  else if (key === 'Escape') {
    clearSelection()
  }
}

// ── Helpers ────────────────────────────────────────────────
function initials(name) {
  if (!name) return '?'
  return name.trim().split(' ').filter(Boolean)
    .map(p => p[0]).join('').substring(0, 2).toUpperCase()
}

function firstName(name) {
  return name?.split(' ')[0] ?? name
}

function roleColor(role) {
  return { admin: '#F59E0B', cashier: '#3B82F6', waiter: '#10B981', kitchen: '#A78BFA' }[role] ?? '#64748B'
}

function roleGradient(role) {
  return {
    admin:   'linear-gradient(135deg,#F59E0B,#D97706)',
    cashier: 'linear-gradient(135deg,#3B82F6,#1D4ED8)',
    waiter:  'linear-gradient(135deg,#10B981,#047857)',
    kitchen: 'linear-gradient(135deg,#A78BFA,#7C3AED)',
  }[role] ?? 'linear-gradient(135deg,#475569,#334155)'
}

// ── Data ───────────────────────────────────────────────────
async function loadUsers() {
  loadingUsers.value = true
  loadError.value    = ''
  try {
    const { data } = await axios.get('/users/list')
    const list = Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : []
    users.value = list.filter(u => u?.id && u?.name)
    if (users.value.length === 0)
      loadError.value = 'No users found. Run: php artisan migrate:fresh --seed'
  } catch {
    loadError.value = 'Cannot reach API. Is "php artisan serve" running?'
  } finally {
    loadingUsers.value = false
  }
}

// ── Interaction ────────────────────────────────────────────
function selectUser(user) {
  selectedUser.value = user
  pin.value          = ''
  pinError.value     = ''
}

function clearSelection() {
  selectedUser.value = null
  pin.value          = ''
  pinError.value     = ''
}

async function pressKey(key) {
  pinError.value = ''
  if (key === '⌫') { pin.value = pin.value.slice(0, -1); return }
  if (key === '↵') { await attemptLogin(); return }
  if (pin.value.length < 4) {
    pin.value += key
    if (pin.value.length === 4) await attemptLogin()
  }
}

async function attemptLogin() {
  if (!selectedUser.value) return
  if (pin.value.length < 4) { pinError.value = 'Enter all 4 digits'; return }
  try {
    await auth.loginWithPin(pin.value)
    router.push(auth.user?.role === 'kitchen' ? '/kitchen' : '/')
  } catch (e) {
    pinError.value = e.response?.data?.errors?.pin?.[0]
                  ?? e.response?.data?.message
                  ?? 'Wrong PIN — try again'
    pin.value = ''
  }
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
  loadUsers()
  // Add keyboard event listener
  document.addEventListener('keydown', handleKeyboardInput)
})

onUnmounted(() => {
  // Remove keyboard event listener
  document.removeEventListener('keydown', handleKeyboardInput)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap');

/* ── Root ── */
.login-root {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #080A0F;
  font-family: 'DM Sans', system-ui, sans-serif;
  position: relative;
  overflow: hidden;
}

/* ── Ambient BG ── */
.bg-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(245,158,11,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(245,158,11,0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
}
.bg-glow {
  position: absolute;
  top: -200px;
  left: 50%;
  transform: translateX(-50%);
  width: 600px;
  height: 400px;
  background: radial-gradient(ellipse, rgba(245,158,11,0.06) 0%, transparent 70%);
  pointer-events: none;
}

/* ── Wrap ── */
.login-wrap {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 760px;
  padding: 40px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 32px;
}

/* ── Brand ── */
.brand { text-align: center; }
.brand-icon { font-size: 36px; line-height: 1; margin-bottom: 8px; }
.brand-name {
  font-family: 'Syne', sans-serif;
  font-size: 26px;
  font-weight: 700;
  color: #F59E0B;
  letter-spacing: -0.02em;
}
.brand-sub {
  font-size: 13px;
  color: #475569;
  margin-top: 4px;
  font-weight: 300;
}

/* ── States ── */
.state-msg {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #475569;
  font-size: 13px;
}
.spinner {
  width: 16px; height: 16px;
  border: 2px solid #1E293B;
  border-top-color: #F59E0B;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

.state-error {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #EF4444;
  font-size: 13px;
  background: rgba(239,68,68,0.06);
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(239,68,68,0.15);
}
.retry-btn {
  background: none;
  border: none;
  color: #F59E0B;
  cursor: pointer;
  font-size: 12px;
  text-decoration: underline;
  font-family: inherit;
  padding: 0;
  margin-left: 4px;
}

/* ── Two panel ── */
.two-panel {
  display: flex;
  gap: 0;
  width: 100%;
  background: #0F1218;
  border: 1px solid #1A1F2E;
  border-radius: 20px;
  overflow: hidden;
  min-height: 360px;
}

.panel-users {
  flex: 1;
  padding: 24px;
  transition: opacity 0.2s;
}
.panel-users.dimmed { opacity: 0.6; }

.panel-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #334155;
  margin-bottom: 16px;
  font-weight: 500;
}

.divider {
  width: 1px;
  background: #1A1F2E;
  flex-shrink: 0;
}

.panel-pin {
  width: 280px;
  flex-shrink: 0;
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0;
  opacity: 0;
  transform: translateX(8px);
  transition: opacity 0.25s, transform 0.25s;
  pointer-events: none;
}
.panel-pin.visible {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}

/* ── User grid ── */
.user-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.user-card {
  background: #161B26;
  border: 1px solid #1E2535;
  border-radius: 14px;
  padding: 16px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.user-card:hover {
  border-color: #2A3347;
  background: #1A2030;
  transform: translateY(-2px);
}
.user-card.active {
  border-color: #F59E0B;
  background: rgba(245,158,11,0.07);
  transform: translateY(-2px);
}

.avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 14px;
  color: #fff;
  flex-shrink: 0;
}

.user-name {
  font-size: 13px;
  font-weight: 500;
  color: #CBD5E1;
}
.user-role {
  font-size: 10px;
  text-transform: capitalize;
  font-weight: 400;
  letter-spacing: 0.04em;
}

/* ── PIN panel ── */
.pin-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #2D3748;
  font-size: 13px;
}

.pin-header {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  margin-bottom: 20px;
}
.pin-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: #fff;
  flex-shrink: 0;
}
.pin-name {
  font-size: 14px;
  font-weight: 500;
  color: #E2E8F0;
  line-height: 1.2;
}
.pin-role {
  font-size: 11px;
  text-transform: capitalize;
}
.change-btn {
  margin-left: auto;
  background: none;
  border: 1px solid #1E2535;
  border-radius: 6px;
  color: #475569;
  font-size: 11px;
  padding: 3px 8px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.change-btn:hover {
  border-color: #334155;
  color: #94A3B8;
}

/* ── PIN dots ── */
.pin-dots {
  display: flex;
  gap: 14px;
  margin-bottom: 10px;
}
.dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #1A1F2E;
  border: 1.5px solid #2A3347;
  transition: all 0.15s;
}
.dot.filled {
  background: #F59E0B;
  border-color: #F59E0B;
  box-shadow: 0 0 8px rgba(245,158,11,0.4);
}
.dot.error {
  background: #EF4444;
  border-color: #EF4444;
  animation: shake 0.3s ease;
}
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

.pin-hint {
  font-size: 11px;
  color: #334155;
  margin-bottom: 12px;
  min-height: 16px;
  transition: color 0.15s;
  color: v-bind("pinError ? '#EF4444' : '#475569'");
}

.keyboard-hint {
  font-size: 10px;
  color: #64748B;
  margin-bottom: 16px;
  text-align: center;
  padding: 6px 10px;
  background: rgba(100, 116, 139, 0.06);
  border-radius: 6px;
  border: 1px solid rgba(100, 116, 139, 0.12);
  line-height: 1.3;
}

/* ── Numpad ── */
.numpad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  width: 220px;
}

.num-key {
  background: #161B26;
  border: 1px solid #1E2535;
  border-radius: 12px;
  padding: 14px 0;
  font-size: 20px;
  font-weight: 400;
  color: #CBD5E1;
  cursor: pointer;
  font-family: 'Syne', sans-serif;
  transition: all 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.num-key:hover {
  background: #1A2030;
  border-color: #2A3347;
  color: #F1F5F9;
}
.num-key.pressed {
  transform: scale(0.93);
  background: #1E2535;
}
.num-key.key-zero {
  grid-column: 2;
}
.num-key.key-action {
  font-size: 14px;
  color: #64748B;
}
.num-key.key-enter {
  color: #F59E0B;
  border-color: rgba(245,158,11,0.25);
  background: rgba(245,158,11,0.06);
}
.num-key.key-enter:hover {
  background: rgba(245,158,11,0.12);
  border-color: rgba(245,158,11,0.4);
}

/* ── Demo hint ── */
.demo-hint {
  font-size: 11px;
  color: #1E2535;
  letter-spacing: 0.02em;
}
</style>