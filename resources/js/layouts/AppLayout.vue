<template>
  <div style="display:flex; height:100vh; overflow:hidden; background:#0A0C10;">

    <!-- Sidebar -->
    <aside style="width:56px; background:#12151C; border-right:1px solid #252B38;
                  display:flex; flex-direction:column; align-items:center;
                  padding:12px 0; gap:4px; flex-shrink:0;">

      <!-- Brand -->
      <div style="width:36px; height:36px; background:#F59E0B; border-radius:8px;
                  display:flex; align-items:center; justify-content:center;
                  font-weight:700; font-size:11px; color:#000; margin-bottom:12px;">
        POS
      </div>

      <!-- Nav buttons -->
      <NavBtn to="/"        icon="grid"      label="Tables"  />
      <NavBtn to="/direct" icon="arrow"      label="direct" />
      <NavBtn to="/kitchen" icon="chef"      label="Kitchen" />

      <NavBtn to="/menu"    icon="menu"      label="Menu"    v-if="auth.isAdmin" />
      <NavBtn to="/reports" icon="chart"     label="Reports" v-if="auth.isAdmin" />

      <div style="flex:1;" />

      <!-- User Info -->
      <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:12px; gap:4px;" :title="auth.user?.role + ' - ' + auth.user?.name">
        <div style="width:32px; height:32px; background:#1A1E28; border-radius:50%; border:1px solid #252B38; display:flex; align-items:center; justify-content:center; color:#F59E0B; font-weight:700; font-size:14px; text-transform:uppercase;">
          {{ auth.user?.name?.charAt(0) || 'U' }}
        </div>
        <div style="font-size:10px; color:#64748B; font-family:monospace; max-width:48px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
          {{ auth.user?.name || 'admin' }}
        </div>
      </div>

      <!-- Logout -->
      <button
        @click="handleLogout"
        style="width:36px; height:36px; border-radius:8px; border:none;
               background:transparent; cursor:pointer; display:flex;
               align-items:center; justify-content:center;
               color:#64748B; font-size:18px; transition:all 0.15s;"
        @mouseenter="e => { e.currentTarget.style.background='rgba(239,68,68,0.1)'; e.currentTarget.style.color='#EF4444' }"
        @mouseleave="e => { e.currentTarget.style.background='transparent'; e.currentTarget.style.color='#64748B' }"
        title="Logout"
      >
        ⏻
      </button>
    </aside>

    <!-- Main content -->
    <main style="flex:1; overflow:hidden; position:relative;">

      <router-view />
    </main>

  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter }    from 'vue-router'
import NavBtn           from '@/components/UI/NavBtn.vue'

const auth   = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>