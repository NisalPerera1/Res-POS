<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- Header -->
    <div style="padding:10px 12px; border-bottom:1px solid #252B38;
                background:#12151C; display:flex; flex-direction:column; gap:8px;">

      <!-- Top Row: Title + Add Table -->
      <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
        <h1 style="font-size:16px; font-weight:700; color:#F1F5F9; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
          🍽️ NISH FAMILY
        </h1>
        <div style="display:flex; gap:6px; flex-shrink:0;">
          <button
            @click="showAddTableModal = true"
            style="padding:9px 14px; background:#F59E0B; color:#000; border:none;
                   border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;
                   min-height:44px; display:flex; align-items:center; gap:4px;
                   -webkit-tap-highlight-color:transparent;"
            @touchstart="e => e.currentTarget.style.filter='brightness(0.9)'"
            @touchend="e => e.currentTarget.style.filter='brightness(1)'"
          >
            + Table
          </button>
          <button
            @click="$router.push('/direct')"
            style="padding:9px 14px; background:#10B981; border:none; border-radius:8px;
                   color:#fff; font-size:13px; font-weight:700; cursor:pointer;
                   min-height:44px; display:flex; align-items:center; gap:4px;
                   box-shadow:0 2px 8px rgba(16,185,129,0.3);
                   -webkit-tap-highlight-color:transparent;"
            @touchstart="e => e.currentTarget.style.opacity='0.85'"
            @touchend="e => e.currentTarget.style.opacity='1'"
          >
            ⚡ Direct
          </button>
        </div>
      </div>

      <!-- Stats Row -->
      <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:6px;">
        <div
          v-for="stat in stats"
          :key="stat.label"
          style="background:#1A1E28; border:1px solid #252B38;
                 border-radius:8px; padding:8px 6px; text-align:center;"
        >
          <div style="font-size:18px; font-weight:800; line-height:1;"
               :style="{ color: stat.color }">
            {{ stat.value }}
          </div>
          <div style="font-size:10px; color:#64748B; margin-top:2px; font-weight:500;">
            {{ stat.label }}
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div style="display:flex; gap:4px; background:#1A1E28;
                  border-radius:8px; padding:3px; overflow-x:auto;
                  -webkit-overflow-scrolling:touch; scrollbar-width:none;">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="activeFilter = f.value"
          :style="{
            padding: '8px 16px',
            borderRadius: '6px',
            fontSize: '12px',
            fontWeight: '600',
            border: 'none',
            cursor: 'pointer',
            transition: 'all 0.15s',
            background: activeFilter === f.value ? '#F59E0B' : 'transparent',
            color:      activeFilter === f.value ? '#000'    : '#64748B',
            whiteSpace: 'nowrap',
            flex: '1',
            minHeight: '36px',
            WebkitTapHighlightColor: 'transparent',
          }"
        >
          {{ f.label }}
          <span style="font-size:11px; opacity:0.8;" v-if="f.count !== undefined"> ({{ f.count }})</span>
        </button>
      </div>
    </div>

    <!-- Table Grid -->
    <div style="flex:1; overflow-y:auto; padding:10px; -webkit-overflow-scrolling:touch;">
      <div :style="{
        display: 'grid',
        gridTemplateColumns: gridCols,
        gap: '8px',
      }">
        <div
          v-for="table in filteredTables"
          :key="table.id"
          @click="openTable(table)"
          :style="{
            background:   tableBackground(table.status),
            border:       '2px solid ' + tableBorder(table.status),
            borderRadius: '12px',
            padding:      '12px 8px 10px',
            cursor:       'pointer',
            transition:   'opacity 0.15s',
            textAlign:    'center',
            position:     'relative',
            WebkitTapHighlightColor: 'transparent',
            userSelect: 'none',
          }"
          @touchstart="e => e.currentTarget.style.opacity='0.8'"
          @touchend="e => e.currentTarget.style.opacity='1'"
          @touchcancel="e => e.currentTarget.style.opacity='1'"
        >
          <!-- Action Buttons -->
          <div
            @click.stop
            style="position:absolute; top:6px; right:6px; display:flex; gap:3px;"
          >
            <button
              @click.stop="editTable(table)"
              style="width:28px; height:28px; background:#3B82F6; color:#fff; border:none;
                     border-radius:6px; font-size:12px; cursor:pointer;
                     display:flex; align-items:center; justify-content:center;
                     -webkit-tap-highlight-color:transparent;"
              title="Edit"
            >✏️</button>
            <button
              v-if="table.status === 'free'"
              @click.stop="deleteTable(table)"
              style="width:28px; height:28px; background:#EF4444; color:#fff; border:none;
                     border-radius:6px; font-size:12px; cursor:pointer;
                     display:flex; align-items:center; justify-content:center;
                     -webkit-tap-highlight-color:transparent;"
              title="Delete"
            >🗑️</button>
            <button
              v-if="table.status === 'occupied'"
              @click.stop="closeTable(table)"
              style="width:28px; height:28px; background:#10B981; color:#fff; border:none;
                     border-radius:6px; font-size:12px; cursor:pointer;
                     display:flex; align-items:center; justify-content:center;
                     -webkit-tap-highlight-color:transparent;"
              title="Close"
            >✅</button>
          </div>

          <!-- Icon -->
          <div style="font-size:24px; margin-bottom:4px; margin-top:2px;">
            {{ tableIcon(table.status) }}
          </div>

          <!-- Name -->
          <div style="font-size:14px; font-weight:700; color:#F1F5F9; line-height:1.2;">
            {{ table.name }}
          </div>

          <!-- Customer -->
          <div v-if="table.customer_name" style="font-size:10px; color:#F59E0B; margin-top:2px; font-weight:600;">
            👤 {{ table.customer_name }}
          </div>

          <!-- Section & Capacity -->
          <div style="font-size:10px; color:#64748B; margin-top:2px;">
            {{ table.section }} · {{ table.capacity }}p
          </div>

          <!-- Status Badge -->
          <div
            style="margin-top:6px; font-size:9px; font-weight:700;
                   text-transform:uppercase; letter-spacing:0.06em;
                   padding:3px 8px; border-radius:5px; display:inline-block;"
            :style="{
              background: statusBg(table.status),
              color:      statusColor(table.status),
            }"
          >
            {{ table.status }}
          </div>

          <!-- Order ID -->
          <div v-if="table.current_order_id" style="font-size:9px; color:#64748B; margin-top:3px;">
            #{{ table.current_order_id }}
          </div>

          <!-- Revenue -->
          <div v-if="table.today_revenue && table.today_revenue > 0"
               style="font-size:10px; color:#10B981; margin-top:2px; font-weight:700;">
            Rs. {{ parseFloat(table.today_revenue).toFixed(0) }}
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="filteredTables.length === 0"
        style="text-align:center; padding:60px 20px; color:#64748B;"
      >
        <div style="font-size:40px; margin-bottom:12px; opacity:0.4;">🪑</div>
        <div style="font-size:14px;">No {{ activeFilter }} tables found</div>
      </div>

      <!-- Bottom padding for iOS safe area -->
      <div style="height:env(safe-area-inset-bottom, 16px);"></div>
    </div>

    <!-- ── MODALS ── -->

    <!-- Add Table Modal -->
    <Teleport to="body">
      <div v-if="showAddTableModal"
           style="position:fixed; inset:0; background:rgba(0,0,0,0.7);
                  display:flex; align-items:flex-end; justify-content:center;
                  z-index:50; padding:0;"
           @click.self="showAddTableModal = false">
        <div style="background:#12151C; border:1px solid #252B38;
                    border-radius:20px 20px 0 0; padding:20px 16px;
                    width:100%; max-width:480px;
                    padding-bottom:calc(20px + env(safe-area-inset-bottom, 0px));">
          <!-- Handle -->
          <div style="width:40px; height:4px; background:#252B38; border-radius:2px; margin:0 auto 16px;"></div>
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Add New Table</h3>

          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Table Name</label>
              <input v-model="newTable.name" type="text" placeholder="e.g., T-06"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Section</label>
              <input v-model="newTable.section" type="text" placeholder="e.g., Main Hall"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Capacity</label>
              <input v-model.number="newTable.capacity" type="number" min="1" max="20" placeholder="4"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:16px;">
            <button @click="showAddTableModal = false; newTable = {}"
              style="flex:1; padding:14px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:10px; cursor:pointer;
                     font-size:15px; font-weight:600; min-height:50px;">
              Cancel
            </button>
            <button @click="addTable"
              style="flex:2; padding:14px; background:#F59E0B; color:#000; border:none;
                     border-radius:10px; cursor:pointer; font-size:15px; font-weight:700;
                     min-height:50px;">
              Add Table
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Edit Table Modal -->
    <Teleport to="body">
      <div v-if="showEditTableModal"
           style="position:fixed; inset:0; background:rgba(0,0,0,0.7);
                  display:flex; align-items:flex-end; justify-content:center;
                  z-index:50;"
           @click.self="showEditTableModal = false; editingTable = null">
        <div style="background:#12151C; border:1px solid #252B38;
                    border-radius:20px 20px 0 0; padding:20px 16px;
                    width:100%; max-width:480px; max-height:90vh; overflow-y:auto;
                    padding-bottom:calc(20px + env(safe-area-inset-bottom, 0px));
                    -webkit-overflow-scrolling:touch;">
          <div style="width:40px; height:4px; background:#252B38; border-radius:2px; margin:0 auto 16px;"></div>
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Edit Table</h3>

          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Table Name</label>
              <input v-model="editingTable.name" type="text" placeholder="e.g., T-06"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Section</label>
              <input v-model="editingTable.section" type="text" placeholder="e.g., Main Hall"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Capacity</label>
              <input v-model.number="editingTable.capacity" type="number" min="1" max="20"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Customer Name</label>
              <input v-model="editingTable.customer_name" type="text" placeholder="Optional"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Notes</label>
              <textarea v-model="editingTable.notes" placeholder="Special notes..." rows="2"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; resize:vertical;
                       box-sizing:border-box;"></textarea>
            </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:16px;">
            <button @click="showEditTableModal = false; editingTable = null"
              style="flex:1; padding:14px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:10px; cursor:pointer;
                     font-size:15px; font-weight:600; min-height:50px;">
              Cancel
            </button>
            <button @click="updateTable"
              style="flex:2; padding:14px; background:#3B82F6; color:#fff; border:none;
                     border-radius:10px; cursor:pointer; font-size:15px; font-weight:700;
                     min-height:50px;">
              Update Table
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Direct Order Modal -->
    <Teleport to="body">
      <div v-if="showDirectOrderModal"
           style="position:fixed; inset:0; background:rgba(0,0,0,0.7);
                  display:flex; align-items:flex-end; justify-content:center;
                  z-index:50;"
           @click.self="showDirectOrderModal = false; directOrder = {}">
        <div style="background:#12151C; border:1px solid #252B38;
                    border-radius:20px 20px 0 0; padding:20px 16px;
                    width:100%; max-width:480px;
                    padding-bottom:calc(20px + env(safe-area-inset-bottom, 0px));">
          <div style="width:40px; height:4px; background:#252B38; border-radius:2px; margin:0 auto 16px;"></div>
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Create Direct Order</h3>

          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Customer Name</label>
              <input v-model="directOrder.customer_name" type="text" placeholder="Optional"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; box-sizing:border-box;">
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Order Type</label>
              <!-- Touch-friendly toggle -->
              <div style="display:flex; gap:6px;">
                <button
                  v-for="opt in orderTypes" :key="opt.value"
                  @click="directOrder.order_type = opt.value"
                  :style="{
                    flex: '1', padding: '12px 8px',
                    background: directOrder.order_type === opt.value ? '#10B981' : '#1A1E28',
                    color: directOrder.order_type === opt.value ? '#fff' : '#64748B',
                    border: '1px solid ' + (directOrder.order_type === opt.value ? '#10B981' : '#252B38'),
                    borderRadius: '8px', cursor: 'pointer',
                    fontSize: '13px', fontWeight: '600',
                    minHeight: '48px',
                    WebkitTapHighlightColor: 'transparent',
                  }"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Notes</label>
              <textarea v-model="directOrder.notes" placeholder="Special instructions..." rows="2"
                style="width:100%; padding:12px; background:#1A1E28; border:1px solid #252B38;
                       border-radius:8px; color:#F1F5F9; font-size:16px; resize:vertical;
                       box-sizing:border-box;"></textarea>
            </div>
          </div>

          <div style="display:flex; gap:8px; margin-top:16px;">
            <button @click="showDirectOrderModal = false; directOrder = {}"
              style="flex:1; padding:14px; background:transparent; color:#64748B;
                     border:1px solid #252B38; border-radius:10px; cursor:pointer;
                     font-size:15px; font-weight:600; min-height:50px;">
              Cancel
            </button>
            <button @click="createDirectOrder"
              style="flex:2; padding:14px; background:#10B981; color:#fff; border:none;
                     border-radius:10px; cursor:pointer; font-size:15px; font-weight:700;
                     min-height:50px;">
              Start Order
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, onActivated, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router       = useRouter()
const tables       = ref([])
const activeFilter = ref('all')
const todayTotalRevenue  = ref(0)
const todayTableRevenue  = ref(0)
const todayDirectRevenue = ref(0)

// Screen width for responsive grid
const windowWidth = ref(window.innerWidth)
const updateWidth = () => { windowWidth.value = window.innerWidth }

// Modal states
const showAddTableModal     = ref(false)
const showDirectOrderModal  = ref(false)
const showEditTableModal    = ref(false)

// Form data
const newTable = ref({ name: '', section: '', capacity: 4 })
const editingTable = ref(null)
const directOrder  = ref({ customer_name: '', order_type: 'takeaway', notes: '' })

const orderTypes = [
  { value: 'takeaway', label: '🥡 Take Away' },
  { value: 'dine_in',  label: '🍽️ Dine In'   },
]

const filters = computed(() => [
  { value: 'all',      label: 'All',      count: tables.value.length },
  { value: 'free',     label: 'Free',     count: tables.value.filter(t => t.status === 'free').length },
  { value: 'occupied', label: 'Occupied', count: tables.value.filter(t => t.status === 'occupied').length },
  { value: 'reserved', label: 'Reserved', count: tables.value.filter(t => t.status === 'reserved').length },
])

// Responsive grid: 2 cols on small phones, 3 on medium, 4 on large screens
const gridCols = computed(() => {
  if (windowWidth.value < 360)  return 'repeat(2, 1fr)'
  if (windowWidth.value < 600)  return 'repeat(3, 1fr)'
  if (windowWidth.value < 900)  return 'repeat(4, 1fr)'
  return 'repeat(5, 1fr)'
})

// ── Computed ──────────────────────────────────────────
const stats = computed(() => [
  {
    label: 'Free',
    value: tables.value.filter(t => t.status === 'free').length,
    color: '#10B981',
  },
  {
    label: 'Occupied',
    value: tables.value.filter(t => t.status === 'occupied').length,
    color: '#3B82F6',
  },
  {
    label: 'Reserved',
    value: tables.value.filter(t => t.status === 'reserved').length,
    color: '#8B5CF6',
  },
  {
    label: 'Revenue',
    value: 'Rs.' + Math.round(todayTotalRevenue.value || 0),
    color: '#F59E0B',
  },
])

const filteredTables = computed(() =>
  activeFilter.value === 'all'
    ? tables.value
    : tables.value.filter(t => t.status === activeFilter.value)
)

// ── Style helpers ─────────────────────────────────────
function tableBackground(status) {
  return { free:'#1A1E28', occupied:'rgba(59,130,246,0.08)', reserved:'rgba(139,92,246,0.08)', cleaning:'rgba(245,158,11,0.08)' }[status] ?? '#1A1E28'
}
function tableBorder(status) {
  return { free:'#252B38', occupied:'#3B82F6', reserved:'#8B5CF6', cleaning:'#F59E0B' }[status] ?? '#252B38'
}
function tableIcon(status) {
  return { free:'⬜', occupied:'🪑', reserved:'📌', cleaning:'🧹' }[status] ?? '⬜'
}
function statusBg(status) {
  return { free:'rgba(16,185,129,0.12)', occupied:'rgba(59,130,246,0.12)', reserved:'rgba(139,92,246,0.12)', cleaning:'rgba(245,158,11,0.12)' }[status] ?? 'rgba(100,116,139,0.12)'
}
function statusColor(status) {
  return { free:'#10B981', occupied:'#3B82F6', reserved:'#8B5CF6', cleaning:'#F59E0B' }[status] ?? '#64748B'
}

// ── Actions ───────────────────────────────────────────
async function openTable(table) {
  if (table.status === 'reserved') return
  if (table.status === 'free') {
    try {
      const { data } = await axios.post('/orders', { table_id: table.id, type: 'dine_in', guests: 1 })
      localStorage.setItem('pos_selected_table', JSON.stringify({ id: table.id, name: table.name }))
      router.push(`/pos/${table.id}`)
    } catch (e) {
      alert('Failed to open table: ' + (e.response?.data?.message ?? e.message))
    }
  } else {
    localStorage.setItem('pos_selected_table', JSON.stringify({ id: table.id, name: table.name }))
    router.push(`/pos/${table.id}`)
  }
}

async function addTable() {
  try {
    const { data } = await axios.post('/tables', newTable.value)
    tables.value.push(data)
    showAddTableModal.value = false
    newTable.value = { name: '', section: '', capacity: 4 }
  } catch (e) {
    alert('Failed to add table: ' + (e.response?.data?.message ?? e.message))
  }
}

async function createDirectOrder() {
  try {
    const { data } = await axios.post('/orders', {
      table_id: null,
      type: directOrder.value.order_type,
      customer_name: directOrder.value.customer_name,
      notes: directOrder.value.notes,
      guests: 1,
    })
    showDirectOrderModal.value = false
    directOrder.value = { customer_name: '', order_type: 'takeaway', notes: '' }
    router.push(`/pos/direct/${data.id}`)
  } catch (e) {
    alert('Failed to create order: ' + (e.response?.data?.message ?? e.message))
  }
}

function editTable(table) {
  editingTable.value = { ...table }
  showEditTableModal.value = true
}

async function updateTable() {
  try {
    const { data } = await axios.put(`/tables/${editingTable.value.id}`, editingTable.value)
    const index = tables.value.findIndex(t => t.id === editingTable.value.id)
    if (index >= 0) tables.value[index] = data
    showEditTableModal.value = false
    editingTable.value = null
  } catch (e) {
    alert('Failed to update table: ' + (e.response?.data?.message ?? e.message))
  }
}

async function deleteTable(table) {
  if (!confirm(`Delete "${table.name}"?`)) return
  try {
    await axios.delete(`/tables/${table.id}`)
    tables.value = tables.value.filter(t => t.id !== table.id)
  } catch (e) {
    alert('Failed to delete table: ' + (e.response?.data?.message ?? e.message))
  }
}

async function closeTable(table) {
  if (!confirm(`Close "${table.name}"? This will clear the current order.`)) return
  try {
    await axios.put(`/tables/${table.id}`, { status: 'free' })
    const index = tables.value.findIndex(t => t.id === table.id)
    if (index >= 0) {
      tables.value[index] = { ...tables.value[index], status: 'free', current_order_id: null, customer_name: null }
    }
    const saved = localStorage.getItem('pos_selected_table')
    if (saved) {
      const parsed = JSON.parse(saved)
      if (parsed.id === table.id) {
        localStorage.removeItem('pos_current_order')
        localStorage.removeItem('pos_selected_table')
      }
    }
  } catch (e) {
    alert('Failed to close table: ' + (e.response?.data?.message ?? e.message))
  }
}

// ── Data loading ──────────────────────────────────────
const refreshInterval = ref(null)

async function loadTables() {
  try {
    const { data } = await axios.get('/tables')
    if (data.tables && data.today_total_revenue !== undefined) {
      tables.value            = data.tables
      todayTotalRevenue.value = data.today_total_revenue
      todayTableRevenue.value = data.today_table_revenue
      todayDirectRevenue.value = data.today_direct_revenue
    } else {
      tables.value = Array.isArray(data) ? data : []
      todayTotalRevenue.value = 0
      todayTableRevenue.value = 0
      todayDirectRevenue.value = 0
    }
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  }
}

onMounted(async () => {
  window.addEventListener('resize', updateWidth)
  await loadTables()
  refreshInterval.value = setInterval(loadTables, 30000)

  if (window.Echo) {
    window.Echo.channel('pos-tables').listen('.table.updated', (e) => {
      const idx = tables.value.findIndex(t => t.id === e.table.id)
      if (idx >= 0) tables.value[idx] = e.table
      else tables.value.push(e.table)
    })
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth)
  if (refreshInterval.value) clearInterval(refreshInterval.value)
})

onActivated(async () => {
  await loadTables()
})
</script>

<style scoped>
/* Prevent double-tap zoom on buttons */
button {
  touch-action: manipulation;
}

/* Hide scrollbars on filter row but keep scrollable */
div::-webkit-scrollbar {
  display: none;
}

/* Smooth scroll on iOS */
.scroll-container {
  -webkit-overflow-scrolling: touch;
}

/* Input zoom prevention on iOS — keep font-size: 16px on all inputs */
input, select, textarea {
  font-size: 16px !important;
}
</style>