<template>
  <div class="account-settings">

    <!-- Header -->
    <div class="page-header">
      <h1>Account settings</h1>
      <p>Manage your profile, security, and preferences</p>
    </div>

    <!-- Avatar card -->
    <div class="avatar-row">
      <div class="avatar-circle" :style="{ background: profile.color }">
        {{ avatarInitials }}
      </div>
      <div class="avatar-info">
        <div class="avatar-name">{{ profile.name || 'Your name' }}</div>
        <div class="avatar-role">{{ profile.role }}</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tab-nav" role="tablist">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        role="tab"
        :aria-selected="activeTab === tab.id"
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- ── Profile ─────────────────────────────────────────────── -->
    <div v-show="activeTab === 'profile'" class="tab-panel">
      <div class="section">
        <div class="section-label">Personal info</div>
        <div class="form-row">
          <div class="field">
            <label for="fname">Full name</label>
            <input id="fname" v-model="profile.name" type="text" placeholder="Enter your full name" />
          </div>
          <div class="field">
            <label>Role</label>
            <input :value="profile.role" type="text" disabled />
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-label">Color theme</div>
        <div class="color-swatches">
          <button
            v-for="c in colorOptions"
            :key="c.value"
            :title="c.name"
            :class="['swatch', { active: profile.color === c.value }]"
            :style="{ background: c.value }"
            @click="profile.color = c.value"
          />
        </div>
      </div>
    </div>

    <!-- ── Security ────────────────────────────────────────────── -->
    <div v-show="activeTab === 'security'" class="tab-panel">
      <div class="section">
        <div class="section-label">Change PIN</div>
        <p class="section-desc">Your PIN is used to unlock the register and confirm sensitive actions.</p>

        <div class="form-row single">
          <div class="field">
            <label>Current PIN</label>
            <div class="pin-cluster" @click="focusPin('cur')">
              <div
                v-for="i in 4" :key="i"
                :class="['pin-dot', { filled: security.currentPin.length >= i }]"
              >{{ security.currentPin.length >= i ? '●' : '·' }}</div>
              <input ref="curPinRef" v-model="security.currentPin"
                type="password" class="pin-hidden" maxlength="4" inputmode="numeric" />
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label>New PIN</label>
            <div class="pin-cluster" @click="focusPin('new')">
              <div
                v-for="i in 4" :key="i"
                :class="['pin-dot', { filled: security.newPin.length >= i }]"
              >{{ security.newPin.length >= i ? '●' : '·' }}</div>
              <input ref="newPinRef" v-model="security.newPin"
                type="password" class="pin-hidden" maxlength="4" inputmode="numeric" />
            </div>
          </div>

          <div class="field">
            <label>Confirm new PIN</label>
            <div class="pin-cluster" @click="focusPin('con')">
              <div
                v-for="i in 4" :key="i"
                :class="['pin-dot', { filled: security.confirmPin.length >= i }]"
              >{{ security.confirmPin.length >= i ? '●' : '·' }}</div>
              <input ref="conPinRef" v-model="security.confirmPin"
                type="password" class="pin-hidden" maxlength="4" inputmode="numeric" />
            </div>
            <div v-if="pinHint" class="field-hint" :class="pinHintClass">{{ pinHint }}</div>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-label">Session</div>
        <div class="form-row">
          <div class="field">
            <label for="timeout">Auto-lock after</label>
            <div class="select-wrap">
              <select id="timeout" v-model.number="preferences.sessionTimeout">
                <option :value="5">5 minutes</option>
                <option :value="10">10 minutes</option>
                <option :value="15">15 minutes</option>
                <option :value="30">30 minutes</option>
                <option :value="60">60 minutes</option>
              </select>
              <span class="select-arrow">▼</span>
            </div>
          </div>
          <div class="field">
            <label>Require PIN on wake</label>
            <div class="toggle-inline">
              <button
                :class="['toggle', { on: preferences.requirePinOnWake }]"
                :aria-pressed="preferences.requirePinOnWake"
                @click="preferences.requirePinOnWake = !preferences.requirePinOnWake"
              />
              <span class="toggle-text">{{ preferences.requirePinOnWake ? 'Enabled' : 'Disabled' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Preferences ──────────────────────────────────────────── -->
    <div v-show="activeTab === 'preferences'" class="tab-panel">
      <div class="section">
        <div class="section-label">Display</div>
        <div class="form-row">
          <div class="field">
            <label for="default-view">Default view</label>
            <div class="select-wrap">
              <select id="default-view" v-model="preferences.defaultView">
                <option value="dashboard">Dashboard</option>
                <option value="tables">Tables</option>
                <option value="direct-order">Direct order</option>
              </select>
              <span class="select-arrow">▼</span>
            </div>
          </div>
          <div class="field">
            <label for="lang">Language</label>
            <div class="select-wrap">
              <select id="lang" v-model="preferences.language">
                <option value="en">English</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
                <option value="si">Sinhala</option>
                <option value="ta">Tamil</option>
              </select>
              <span class="select-arrow">▼</span>
            </div>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-label">Notifications</div>
        <div v-for="notif in notificationOptions" :key="notif.key" class="toggle-row">
          <div>
            <div class="toggle-label">{{ notif.label }}</div>
            <div class="toggle-desc">{{ notif.desc }}</div>
          </div>
          <button
            :class="['toggle', { on: preferences.notifications[notif.key] }]"
            :aria-pressed="preferences.notifications[notif.key]"
            @click="preferences.notifications[notif.key] = !preferences.notifications[notif.key]"
          />
        </div>
      </div>
    </div>

    <!-- ── Footer actions ──────────────────────────────────────── -->
    <div class="page-footer">
      <div class="status-msg" :class="statusClass">{{ statusMsg }}</div>
      <div class="btn-group">
        <button class="btn" @click="resetSettings">Reset to defaults</button>
        <button class="btn primary" :disabled="saving" @click="saveSettings">
          {{ saving ? 'Saving…' : 'Save changes' }}
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const auth = useAuthStore()

// ── Tabs ────────────────────────────────────────────────────────────────────
const activeTab = ref('profile')
const tabs = [
  { id: 'profile',     label: 'Profile'     },
  { id: 'security',    label: 'Security'    },
  { id: 'preferences', label: 'Preferences' }
]

// ── State ────────────────────────────────────────────────────────────────────
const saving     = ref(false)
const statusMsg  = ref('')
const statusType = ref('success')

const profile = ref({ name: '', role: '', color: '#F59E0B' })

const security = ref({ currentPin: '', newPin: '', confirmPin: '' })

const preferences = ref({
  defaultView:      'dashboard',
  language:         'en',
  sessionTimeout:   10,
  requirePinOnWake: true,
  notifications:    { orderAlerts: true, lowStock: true, shiftReminders: false }
})

// ── Options ──────────────────────────────────────────────────────────────────
const colorOptions = [
  { name: 'Amber',  value: '#F59E0B' },
  { name: 'Blue',   value: '#3B82F6' },
  { name: 'Green',  value: '#10B981' },
  { name: 'Purple', value: '#8B5CF6' },
  { name: 'Red',    value: '#EF4444' },
  { name: 'Pink',   value: '#EC4899' },
  { name: 'Indigo', value: '#6366F1' }
]

const notificationOptions = [
  { key: 'orderAlerts',    label: 'Order alerts',       desc: 'Sound and visual alert for new orders'  },
  { key: 'lowStock',       label: 'Low stock warnings', desc: 'Notify when items drop below threshold' },
  { key: 'shiftReminders', label: 'Shift reminders',    desc: 'Reminder 15 min before shift ends'      }
]

// ── PIN refs ─────────────────────────────────────────────────────────────────
const curPinRef = ref(null)
const newPinRef = ref(null)
const conPinRef = ref(null)

function focusPin(which) {
  const map = { cur: curPinRef, new: newPinRef, con: conPinRef }
  map[which]?.value?.focus()
}

// ── Computed ─────────────────────────────────────────────────────────────────
const avatarInitials = computed(() => {
  const parts = profile.value.name.trim().split(/\s+/).filter(Boolean)
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return profile.value.name.slice(0, 2).toUpperCase() || '??'
})

const pinHint = computed(() => {
  const { newPin, confirmPin } = security.value
  if (confirmPin.length < 4 || newPin.length < 4) return ''
  return newPin === confirmPin ? '✓ PINs match' : 'PINs do not match'
})

const pinHintClass = computed(() => ({
  'hint-success': pinHint.value.startsWith('✓'),
  'hint-error':   pinHint.value === 'PINs do not match'
}))

const statusClass = computed(() => ({
  'status-success': statusType.value === 'success',
  'status-error':   statusType.value === 'error'
}))

// ── Methods ──────────────────────────────────────────────────────────────────
function showStatus(msg, type = 'success', duration = 3000) {
  statusMsg.value  = msg
  statusType.value = type
  setTimeout(() => { statusMsg.value = '' }, duration)
}

async function loadSettings() {
  if (!auth.user) return
  
  try {
    const response = await axios.get(`/users/${auth.user.id}`)
    const userData = response.data
    
    // Load profile data
    profile.value.name = userData.name || ''
    profile.value.role = userData.role || ''
    profile.value.color = userData.color || '#F59E0B'
    
    // Load preferences from database
    if (userData.preferences) {
      preferences.value = { ...preferences.value, ...userData.preferences }
    }
    
    // Update auth store
    auth.user.name = userData.name
    auth.user.color = userData.color
    
    // Also save to localStorage as backup
    localStorage.setItem('user_preferences', JSON.stringify(preferences.value))
    
  } catch (err) {
    console.error('Failed to load settings:', err)
    // Fallback to localStorage if database fails
    try {
      const saved = localStorage.getItem('user_preferences')
      if (saved) preferences.value = { ...preferences.value, ...JSON.parse(saved) }
    } catch { /* ignore */ }
  }
}

async function saveSettings() {
  const { newPin, currentPin, confirmPin } = security.value
  if (newPin && newPin.length !== 4) {
    showStatus('New PIN must be 4 digits', 'error')
    activeTab.value = 'security'
    return
  }
  if (newPin && newPin !== confirmPin) {
    showStatus('PINs do not match', 'error')
    activeTab.value = 'security'
    return
  }

  saving.value = true
  try {
    const payload = {
      name:        profile.value.name,
      color:       profile.value.color,
      preferences: { ...preferences.value }
    }
    if (newPin && currentPin) {
      payload.current_pin = currentPin
      payload.new_pin     = newPin
    }

    await axios.put(`/users/${auth.user.id}`, payload)

    auth.user.name  = profile.value.name
    auth.user.color = profile.value.color

    localStorage.setItem('user_preferences', JSON.stringify(preferences.value))

    security.value = { currentPin: '', newPin: '', confirmPin: '' }
    showStatus('✓ Settings saved successfully')
  } catch (err) {
    showStatus(err.response?.data?.message || 'Failed to save settings', 'error')
  } finally {
    saving.value = false
  }
}

function resetSettings() {
  profile.value = {
    name:  auth.user?.name  || '',
    role:  auth.user?.role  || '',
    color: auth.user?.color || '#F59E0B'
  }
  security.value = { currentPin: '', newPin: '', confirmPin: '' }
  preferences.value = {
    defaultView:      'dashboard',
    language:         'en',
    sessionTimeout:   10,
    requirePinOnWake: true,
    notifications:    { orderAlerts: true, lowStock: true, shiftReminders: false }
  }
  showStatus('Reset to defaults', 'success', 2000)
}

// ── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.account-settings {
  padding: 24px;
  max-width: 760px;
  margin: 0 auto;
  background: var(--bg-primary);
  overflow-y: auto;
  max-height: calc(100vh - 120px);
}

/* ── Header ────────────────────────────────────────────────────────────────── */
.page-header { margin-bottom: 24px; }
.page-header h1 {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 4px;
}
.page-header p {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

/* ── Avatar ────────────────────────────────────────────────────────────────── */
.avatar-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  margin-bottom: 24px;
}
.avatar-circle {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  font-weight: 600;
  color: #fff;
  flex-shrink: 0;
  transition: background 0.25s;
}
.avatar-name { font-size: 15px; font-weight: 600; color: var(--text-primary); }
.avatar-role { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

/* ── Tabs ──────────────────────────────────────────────────────────────────── */
.tab-nav {
  display: flex;
  border-bottom: 1px solid var(--border-color);
}
.tab-btn {
  padding: 10px 18px 9px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-secondary);
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: color 0.15s;
  margin-bottom: -1px;
}
.tab-btn.active {
  color: var(--text-primary);
  font-weight: 600;
  border-bottom-color: var(--accent-color, #F59E0B);
}
.tab-btn:hover:not(.active) { color: var(--text-primary); }

/* ── Panels ────────────────────────────────────────────────────────────────── */
.tab-panel { padding-top: 24px; }
.section   { margin-bottom: 28px; }

.section-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.07em;
  margin-bottom: 14px;
}
.section-desc {
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 18px;
  line-height: 1.5;
}

/* ── Form ──────────────────────────────────────────────────────────────────── */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.form-row.single { grid-template-columns: 1fr; }

.field { display: flex; flex-direction: column; gap: 7px; }
.field label { font-size: 13px; font-weight: 500; color: var(--text-secondary); }

.field input,
.field select {
  height: 38px;
  padding: 0 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  width: 100%;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.field input:focus,
.field select:focus {
  border-color: var(--accent-color, #F59E0B);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
}
.field input:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: var(--bg-tertiary);
}

/* ── Select ────────────────────────────────────────────────────────────────── */
.select-wrap { position: relative; }
.select-wrap select { appearance: none; padding-right: 28px; cursor: pointer; }
.select-arrow {
  position: absolute; right: 10px; top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: var(--text-secondary); font-size: 9px;
}

/* ── Swatches ──────────────────────────────────────────────────────────────── */
.color-swatches { display: flex; gap: 10px; flex-wrap: wrap; }
.swatch {
  width: 30px; height: 30px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer; outline: none;
  position: relative;
  transition: transform 0.15s, border-color 0.15s;
}
.swatch:hover { transform: scale(1.15); }
.swatch.active { border-color: var(--text-primary); }
.swatch.active::after {
  content: '';
  position: absolute;
  inset: 4px;
  border-radius: 50%;
  border: 1.5px solid rgba(255,255,255,0.75);
}

/* ── PIN ───────────────────────────────────────────────────────────────────── */
.pin-cluster {
  display: flex; gap: 8px;
  align-items: center;
  cursor: text; position: relative;
}
.pin-dot {
  width: 38px; height: 38px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-secondary);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; color: var(--text-secondary);
  user-select: none;
  transition: border-color 0.15s, color 0.15s;
}
.pin-dot.filled {
  color: var(--text-primary);
  border-color: var(--accent-color, #F59E0B);
}
.pin-hidden {
  position: absolute; opacity: 0;
  width: 1px; height: 1px; pointer-events: none;
}
.field-hint { font-size: 11px; margin-top: 4px; }
.hint-success { color: #10B981; }
.hint-error   { color: #EF4444; }

/* ── Toggles ───────────────────────────────────────────────────────────────── */
.toggle-inline { display: flex; align-items: center; gap: 10px; height: 38px; }
.toggle-text   { font-size: 13px; color: var(--text-secondary); }

.toggle {
  width: 38px; height: 21px;
  background: var(--border-color);
  border-radius: 11px; border: none;
  cursor: pointer; position: relative; flex-shrink: 0;
  transition: background 0.2s;
}
.toggle.on { background: #10B981; }
.toggle::after {
  content: '';
  position: absolute;
  width: 15px; height: 15px;
  border-radius: 50%; background: #fff;
  top: 3px; left: 3px;
  transition: left 0.2s;
}
.toggle.on::after { left: 20px; }

/* ── Notification rows ─────────────────────────────────────────────────────── */
.toggle-row {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 13px 0;
  border-bottom: 1px solid var(--border-color);
}
.toggle-row:last-child { border-bottom: none; }
.toggle-label { font-size: 13px; font-weight: 500; color: var(--text-primary); }
.toggle-desc  { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

/* ── Footer ────────────────────────────────────────────────────────────────── */
.page-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 24px;
  margin-top: 8px;
  border-top: 1px solid var(--border-color);
  gap: 12px;
}
.status-msg   { font-size: 12px; font-weight: 500; min-height: 16px; }
.status-success { color: #10B981; }
.status-error   { color: #EF4444; }

.btn-group { display: flex; gap: 8px; }
.btn {
  height: 36px; padding: 0 18px;
  border-radius: 8px;
  font-size: 13px; font-weight: 500;
  cursor: pointer;
  border: 1px solid var(--border-color);
  background: transparent; color: var(--text-primary);
  transition: background 0.15s, opacity 0.15s;
}
.btn:hover { background: var(--bg-secondary); }
.btn.primary {
  background: var(--text-primary);
  color: var(--bg-primary);
  border-color: transparent;
}
.btn.primary:hover    { opacity: 0.85; }
.btn.primary:disabled { opacity: 0.45; cursor: not-allowed; }

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 560px) {
  .account-settings { padding: 16px; }
  .form-row         { grid-template-columns: 1fr; }
  .page-footer      { flex-direction: column; align-items: flex-start; }
  .btn-group        { width: 100%; }
  .btn              { flex: 1; }
}
</style>