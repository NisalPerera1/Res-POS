<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center gap-8 p-6"
    style="background:#0A0C10; color:#F1F5F9; font-family:'DM Sans',system-ui,sans-serif"
  >
    <!-- Brand -->
    <div class="text-center">
      <div style="font-size:28px; font-weight:700; color:#F59E0B;">
        🍽️ RestoPOS
      </div>
      <p style="font-size:13px; color:#64748B; margin-top:4px;">
        Select your profile to continue
      </p>
    </div>

    <!-- Loading -->
    <div v-if="loadingUsers" style="color:#64748B; font-size:14px;">
      Loading users...
    </div>

    <!-- Error -->
    <div v-else-if="loadError"
      style="color:#EF4444; font-size:13px; text-align:center; max-width:300px">
      {{ loadError }}
      <br />
      <button
        @click="loadUsers"
        style="color:#F59E0B; font-size:12px; margin-top:8px; background:none; border:none; cursor:pointer; text-decoration:underline"
      >
        Retry
      </button>
    </div>

    <!-- User Grid -->
    <div
      v-else
      style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;"
    >
      <button
        v-for="user in users"
        :key="user.id"
        @click="selectUser(user)"
        style="border-radius:12px; padding:16px 12px; display:flex; flex-direction:column;
               align-items:center; gap:8px; cursor:pointer; transition:all 0.15s;
               border-width:1.5px; border-style:solid;"
        :style="{
          background:   selectedUser?.id === user.id ? 'rgba(245,158,11,0.12)' : '#1A1E28',
          borderColor:  selectedUser?.id === user.id ? '#F59E0B' : '#252B38',
        }"
      >
        <!-- Avatar circle -->
        <div
          style="width:48px; height:48px; border-radius:50%; display:flex;
                 align-items:center; justify-content:center; font-weight:700;
                 font-size:15px; color:#000;"
          :style="{ background: avatarColor(user.role) }"
        >
          {{ initials(user.name) }}
        </div>

        <div style="font-size:13px; font-weight:500; color:#F1F5F9;">
          {{ user.name }}
        </div>
        <div style="font-size:11px; color:#64748B; text-transform:capitalize;">
          {{ user.role }}
        </div>
      </button>
    </div>

    <!-- PIN Entry section -->
    <div
      v-if="selectedUser"
      style="display:flex; flex-direction:column; align-items:center; gap:16px;"
    >
      <p style="font-size:13px; color:#64748B;">
        Enter PIN for
        <span style="color:#F1F5F9; font-weight:500;">{{ selectedUser.name }}</span>
      </p>

      <!-- PIN dots -->
      <div style="display:flex; gap:12px;">
        <div
          v-for="i in 4"
          :key="i"
          style="width:16px; height:16px; border-radius:50%; transition:background 0.2s;"
          :style="{ background: pin.length >= i ? '#F59E0B' : '#252B38' }"
        />
      </div>

      <!-- PIN Pad -->
      <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; width:220px;">
        <button
          v-for="key in pinKeys"
          :key="key"
          @click="pressKey(key)"
          :style="{
            gridColumnStart: key === '0' ? '2' : 'auto',
            background: '#1A1E28',
            border: '1px solid #252B38',
            borderRadius: '10px',
            padding: '14px',
            fontSize: ['⌫','↵'].includes(key) ? '13px' : '20px',
            fontWeight: '500',
            color: '#F1F5F9',
            cursor: 'pointer',
            transition: 'all 0.1s',
          }"
          @mouseenter="e => e.currentTarget.style.background = '#1E2432'"
          @mouseleave="e => e.currentTarget.style.background = '#1A1E28'"
          @mousedown="e => e.currentTarget.style.transform = 'scale(0.95)'"
          @mouseup="e => e.currentTarget.style.transform = 'scale(1)'"
        >
          {{ key }}
        </button>
      </div>
    </div>

    <!-- Error message -->
    <p v-if="error" style="color:#EF4444; font-size:13px;">
      {{ error }}
    </p>

    <!-- Hint -->
    <p style="font-size:11px; color:#334155;">
      Demo PINs: Admin=1234 · Cashier=2222 · Kitchen=4444
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter }      from 'vue-router'
import { useAuthStore }   from '@/stores/auth'
import axios              from 'axios'

const router = useRouter()
const auth   = useAuthStore()

const users         = ref([])
const selectedUser  = ref(null)
const pin           = ref('')
const error         = ref('')
const loadingUsers  = ref(true)
const loadError     = ref('')
const pinKeys       = ['1','2','3','4','5','6','7','8','9','⌫','0','↵']

function initials(name) {
  if (!name || typeof name !== 'string') return '?'
  return name.trim().split(' ').filter(Boolean)
    .map(p => p[0]).join('').substring(0, 2).toUpperCase()
}

function avatarColor(role) {
  return {
    admin:   '#F59E0B',
    cashier: '#3B82F6',
    waiter:  '#10B981',
    kitchen: '#8B5CF6',
  }[role] ?? '#64748B'
}

async function loadUsers() {
  loadingUsers.value = true
  loadError.value    = ''
  try {
    const { data } = await axios.get('/users/list')
    console.log('API response:', data)

    const list = Array.isArray(data) ? data
               : Array.isArray(data?.data) ? data.data
               : []

    users.value = list.filter(u => u?.id && u?.name)

    if (users.value.length === 0) {
      loadError.value = 'No users found. Run: php artisan migrate:fresh --seed'
    }
  } catch (e) {
    loadError.value = 'Cannot reach API. Is "php artisan serve" running?'
    console.error(e)
  } finally {
    loadingUsers.value = false
  }
}

function selectUser(user) {
  selectedUser.value = user
  pin.value          = ''
  error.value        = ''
}

async function pressKey(key) {
  error.value = ''
  if (key === '⌫') { pin.value = pin.value.slice(0, -1); return }
  if (key === '↵') { await attemptLogin(); return }
  if (pin.value.length < 4) {
    pin.value += key
    if (pin.value.length === 4) await attemptLogin()
  }
}

async function attemptLogin() {
  if (!selectedUser.value) { error.value = 'Select a user first'; return }
  if (pin.value.length < 4) { error.value = 'PIN must be 4 digits'; return }
  try {
    await auth.loginWithPin(pin.value)
    router.push(auth.user?.role === 'kitchen' ? '/kitchen' : '/')
  } catch (e) {
    error.value = e.response?.data?.errors?.pin?.[0]
                ?? e.response?.data?.message
                ?? 'Invalid PIN. Try again.'
    pin.value = ''
  }
}

onMounted(loadUsers)
</script>