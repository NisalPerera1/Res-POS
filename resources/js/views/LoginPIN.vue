<template>
  <div class="login-root">
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
        <div class="panel-users" :class="{ dimmed: !!selectedUser && isMobile }">
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

        <!-- Vertical divider (desktop only) -->
        <div class="divider-v" />

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
              <div class="pin-user-info">
                <div class="pin-name">{{ selectedUser.name }}</div>
                <div class="pin-role" :style="{ color: roleColor(selectedUser.role) }">
                  {{ selectedUser.role }}
                </div>
              </div>
              <button class="change-btn" @click="clearSelection">Change</button>
            </div>

            <!-- Horizontal divider (mobile: between user header and PIN) -->
            <div class="divider-h" />

            <!-- PIN dots -->
            <div class="pin-dots">
              <div
                v-for="i in 4" :key="i"
                class="dot"
                :class="{ filled: pin.length >= i, error: pinError }"
              />
            </div>

            <!-- PIN label -->
            <div class="pin-hint" :class="{ 'pin-hint--error': pinError }">
              {{ pinError ? pinError : 'Enter your 4-digit PIN' }}
            </div>

            <!-- Keyboard hint -->
            <div class="keyboard-hint">
              💡 You can also use your keyboard: 0–9, Backspace, Enter, Esc
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
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter }    from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios            from 'axios'

const router = useRouter()
const auth   = useAuthStore()

const users        = ref([])
const selectedUser = ref(null)
const pin          = ref('')
const pinError     = ref('')
const loadingUsers = ref(true)
const loadError    = ref('')
const pinKeys      = ['1','2','3','4','5','6','7','8','9','⌫','0','↵']

// Detect mobile for conditional dimming
const isMobile = computed(() => window.innerWidth < 768)

// ── Keyboard ──────────────────────────────────────────────
function handleKeyboardInput(event) {
  if (!selectedUser.value) return
  const key = event.key
  if (/^[0-9]$/.test(key) || ['Backspace','Enter','Delete'].includes(key)) {
    event.preventDefault()
  }
  if (/^[0-9]$/.test(key))          pressKey(key)
  else if (key === 'Backspace' || key === 'Delete') pressKey('⌫')
  else if (key === 'Enter')          pressKey('↵')
  else if (key === 'Escape')         clearSelection()
}

// ── Helpers ───────────────────────────────────────────────
function initials(name) {
  if (!name) return '?'
  return name.trim().split(' ').filter(Boolean)
    .map(p => p[0]).join('').substring(0, 2).toUpperCase()
}
function firstName(name) {
  return name?.split(' ')[0] ?? name
}
function roleColor(role) {
  return { admin:'#F59E0B', cashier:'#3B82F6', waiter:'#10B981', kitchen:'#A78BFA' }[role] ?? '#64748B'
}
function roleGradient(role) {
  return {
    admin:   'linear-gradient(135deg,#F59E0B,#D97706)',
    cashier: 'linear-gradient(135deg,#3B82F6,#1D4ED8)',
    waiter:  'linear-gradient(135deg,#10B981,#047857)',
    kitchen: 'linear-gradient(135deg,#A78BFA,#7C3AED)',
  }[role] ?? 'linear-gradient(135deg,#475569,#334155)'
}

// ── Data ──────────────────────────────────────────────────
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

// ── Interaction ───────────────────────────────────────────
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
  document.addEventListener('keydown', handleKeyboardInput)
})
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyboardInput)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap');

/* ─── CSS Variables ─────────────────────────────────────── */
:root {
  --bg-base:     #080A0F;
  --bg-card:     #0F1218;
  --bg-item:     #161B26;
  --border:      #1A1F2E;
  --border-mid:  #1E2535;
  --border-hi:   #2A3347;
  --accent:      #F59E0B;
  --accent-dim:  rgba(245,158,11,0.07);
  --accent-glow: rgba(245,158,11,0.4);
  --text-hi:     #E2E8F0;
  --text-mid:    #CBD5E1;
  --text-lo:     #475569;
  --text-mute:   #334155;
  --error:       #EF4444;
}

/* ─── Reset ─────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
button { cursor: pointer; font-family: inherit; border: none; background: none; }

/* ─── Root ──────────────────────────────────────────────── */
.login-root {
  min-height: 100vh;
  min-height: 100dvh; /* dynamic viewport on mobile */
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-base);
  font-family: 'DM Sans', system-ui, sans-serif;
  position: relative;
  overflow: hidden;
}

/* ─── Ambient BG ────────────────────────────────────────── */
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

/* ─── Scroll wrapper ────────────────────────────────────── */
.login-wrap {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 780px;
  padding: 40px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 28px;
}

/* ─── Brand ─────────────────────────────────────────────── */
.brand { text-align: center; }
.brand-icon {
  font-size: 36px;
  line-height: 1;
  margin-bottom: 8px;
}
.brand-name {
  font-family: 'Syne', sans-serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--accent);
  letter-spacing: -0.02em;
}
.brand-sub {
  font-size: 13px;
  color: var(--text-lo);
  margin-top: 4px;
  font-weight: 300;
}

/* ─── States ────────────────────────────────────────────── */
.state-msg {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-lo);
  font-size: 13px;
}
.spinner {
  width: 16px; height: 16px;
  border: 2px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

.state-error {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--error);
  font-size: 13px;
  background: rgba(239,68,68,0.06);
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(239,68,68,0.15);
}
.retry-btn {
  color: var(--accent);
  font-size: 12px;
  text-decoration: underline;
  padding: 0;
  margin-left: 4px;
}

/* ─── Two-panel shell ───────────────────────────────────── */
.two-panel {
  display: flex;
  flex-direction: row;
  width: 100%;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  /* let content dictate height — no fixed min-height */
}

/* ─── Left: user grid ───────────────────────────────────── */
.panel-users {
  flex: 1 1 0;
  min-width: 0;
  padding: 24px;
  transition: opacity 0.2s;
}
.panel-users.dimmed { opacity: 0.45; }

.panel-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-mute);
  margin-bottom: 16px;
  font-weight: 500;
}

.user-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.user-card {
  background: var(--bg-item);
  border: 1px solid var(--border-mid);
  border-radius: 14px;
  padding: 16px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  transition: all 0.15s;
}
.user-card:hover {
  border-color: var(--border-hi);
  background: #1A2030;
  transform: translateY(-2px);
}
.user-card.active {
  border-color: var(--accent);
  background: var(--accent-dim);
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
  color: var(--text-mid);
  text-align: center;
  word-break: break-word;
}
.user-role {
  font-size: 10px;
  text-transform: capitalize;
  font-weight: 400;
  letter-spacing: 0.04em;
  text-align: center;
}

/* ─── Vertical divider (desktop) ───────────────────────── */
.divider-v {
  width: 1px;
  background: var(--border);
  flex-shrink: 0;
  align-self: stretch;
}

/* ─── Horizontal divider (mobile, inside PIN panel) ─────── */
.divider-h {
  display: none;
  width: 100%;
  height: 1px;
  background: var(--border);
  margin: 16px 0;
}

/* ─── Right: PIN panel ──────────────────────────────────── */
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

.pin-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #2D3748;
  font-size: 13px;
}

/* ─── PIN header ────────────────────────────────────────── */
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
.pin-user-info {
  flex: 1;
  min-width: 0;
}
.pin-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-hi);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pin-role {
  font-size: 11px;
  text-transform: capitalize;
}
.change-btn {
  background: none;
  border: 1px solid var(--border-mid);
  border-radius: 6px;
  color: var(--text-lo);
  font-size: 11px;
  padding: 3px 8px;
  transition: all 0.15s;
  white-space: nowrap;
  flex-shrink: 0;
}
.change-btn:hover {
  border-color: var(--border-hi);
  color: #94A3B8;
}

/* ─── PIN dots ──────────────────────────────────────────── */
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
  border: 1.5px solid var(--border-hi);
  transition: all 0.15s;
}
.dot.filled {
  background: var(--accent);
  border-color: var(--accent);
  box-shadow: 0 0 8px var(--accent-glow);
}
.dot.error {
  background: var(--error);
  border-color: var(--error);
  animation: shake 0.3s ease;
}
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25%      { transform: translateX(-4px); }
  75%      { transform: translateX(4px); }
}

.pin-hint {
  font-size: 11px;
  color: var(--text-lo);
  margin-bottom: 10px;
  min-height: 16px;
  transition: color 0.15s;
  text-align: center;
}
.pin-hint--error { color: var(--error); }

.keyboard-hint {
  font-size: 10px;
  color: #64748B;
  margin-bottom: 16px;
  text-align: center;
  padding: 6px 10px;
  background: rgba(100,116,139,0.06);
  border-radius: 6px;
  border: 1px solid rgba(100,116,139,0.12);
  line-height: 1.4;
}

/* ─── Numpad ────────────────────────────────────────────── */
.numpad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  width: 100%;
  max-width: 220px;
}
.num-key {
  background: var(--bg-item);
  border: 1px solid var(--border-mid);
  border-radius: 12px;
  padding: 14px 0;
  font-size: 20px;
  font-weight: 400;
  color: var(--text-mid);
  font-family: 'Syne', sans-serif;
  transition: all 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.num-key:hover {
  background: #1A2030;
  border-color: var(--border-hi);
  color: #F1F5F9;
}
.num-key.pressed {
  transform: scale(0.93);
  background: var(--border-mid);
}
.key-zero  { grid-column: 2; }
.key-action { font-size: 14px; color: #64748B; }
.key-enter  {
  color: var(--accent);
  border-color: rgba(245,158,11,0.25);
  background: rgba(245,158,11,0.06);
}
.key-enter:hover {
  background: rgba(245,158,11,0.12);
  border-color: rgba(245,158,11,0.4);
}

/* ─── Demo hint ─────────────────────────────────────────── */
.demo-hint {
  font-size: 11px;
  color: var(--text-mute);
  letter-spacing: 0.02em;
  text-align: center;
}

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS
   ═══════════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE — mobile-first stacking
   Strategy: on mobile the two panels stack vertically.
   Everything is compacted so the full numpad fits on screen
   without scrolling (target: iPhone 12 Pro 390×844).
   ═══════════════════════════════════════════════════════════ */

@media (max-width: 768px) {

  /* Allow root to scroll if content overflows on very small devices */
  .login-root {
    align-items: flex-start;
    overflow-y: auto;
  }

  /* Compact outer wrapper */
  .login-wrap {
    padding: 12px 12px 20px;
    gap: 10px;
    min-height: 100dvh;
    justify-content: flex-start;
  }

  /* Shrink brand */
  .brand        { gap: 0; }
  .brand-icon   { font-size: 22px; margin-bottom: 2px; }
  .brand-name   { font-size: 18px; }
  .brand-sub    { font-size: 11px; margin-top: 2px; }

  /* Stack panels */
  .two-panel {
    flex-direction: column;
    border-radius: 14px;
    width: 100%;
  }

  /* Hide vertical divider, show horizontal divider inside PIN panel */
  .divider-v { display: none; }
  .divider-h { display: block; margin: 10px 0; }

  /* ── User grid panel ── */
  .panel-users {
    padding: 12px 12px 10px;
    width: 100%;
  }
  .panel-label {
    font-size: 9px;
    margin-bottom: 8px;
  }
  .user-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;
  }
  .user-card {
    padding: 10px 6px;
    gap: 5px;
    border-radius: 10px;
  }
  .avatar    { width: 36px; height: 36px; font-size: 12px; }
  .user-name { font-size: 11px; }
  .user-role { font-size: 9px; }

  /* ── PIN panel ── */
  .panel-pin {
    width: 100%;
    padding: 10px 12px 14px;
    transform: none !important;
    justify-content: flex-start;
    align-items: stretch;
    border-top: 1px solid var(--border);
    gap: 0;
  }
  .panel-pin.visible {
    transform: none;
  }

  .pin-header  { margin-bottom: 0; }
  .pin-avatar  { width: 32px; height: 32px; font-size: 11px; }
  .pin-name    { font-size: 13px; }
  .pin-role    { font-size: 10px; }

  .pin-dots    { gap: 12px; margin-bottom: 6px; justify-content: center; }
  .dot         { width: 13px; height: 13px; }

  .pin-hint    { font-size: 11px; margin-bottom: 6px; text-align: center; }

  /* Hide keyboard hint on mobile — irrelevant on touch devices */
  .keyboard-hint { display: none; }

  /* Numpad: full-width, square-ish keys */
  .numpad {
    max-width: 100%;
    width: 100%;
    gap: 7px;
    margin: 0 auto;
    align-self: center;
  }
  .num-key {
    padding: 13px 0;
    font-size: 20px;
    border-radius: 10px;
  }

  .demo-hint { font-size: 10px; }
}

/* ── 3-col user grid when ≤ 5 users or narrow phones ── */
@media (max-width: 480px) {
  .login-wrap  { padding: 10px 10px 16px; gap: 8px; }

  .user-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
  }
  .user-card   { padding: 9px 5px; }
  .avatar      { width: 34px; height: 34px; font-size: 11px; }

  .panel-pin   { padding: 8px 10px 12px; }
  .divider-h   { margin: 8px 0; }

  .numpad      { gap: 6px; }
  .num-key     { padding: 12px 0; font-size: 18px; border-radius: 9px; }

  .pin-dots    { gap: 10px; }
  .dot         { width: 12px; height: 12px; }
}

/* ── Very small phones ≤ 360px ── */
@media (max-width: 360px) {
  .user-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 5px;
  }
  .avatar    { width: 30px; height: 30px; font-size: 10px; }
  .user-name { font-size: 10px; }

  .num-key   { padding: 10px 0; font-size: 16px; }
}
</style>