<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10;">

    <!-- Header -->
    <div style="padding:12px 16px; border-bottom:1px solid #252B38;
                background:#12151C; display:flex; align-items:center;
                justify-content:space-between;">
      <h1 style="font-size:22px; font-weight:700; color:#F1F5F9; margin:0;">
        NISH FAMILY RESTAURENTS
      </h1>

      <!-- Action buttons -->
      <div style="display:flex; gap:8px;">
        <button
          @click="showAddTableModal = true"
          style="padding:8px 16px; background:#F59E0B; color:#000; border:none;
                 border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;
                 transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          + Add Table
        </button>
       
          <button
        @click="$router.push('/direct')"
        style="padding:6px 14px; background:#10B981; border:none; border-radius:8px;
               color:#000; font-size:12px; font-weight:700; cursor:pointer;
               display:flex; align-items:center; gap:5px; transition:all 0.15s;"
        @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
        @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
      >
        ⚡ Direct Order
      </button>
      </div>

      <!-- Filter tabs -->
      <div style="display:flex; gap:4px; background:#1A1E28;
                  border-radius:8px; padding:3px;">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="activeFilter = f.value"
          :style="{
            padding: '4px 12px',
            borderRadius: '6px',
            fontSize: '12px',
            fontWeight: '500',
            border: 'none',
            cursor: 'pointer',
            transition: 'all 0.15s',
            background: activeFilter === f.value ? '#F59E0B' : 'transparent',
            color:      activeFilter === f.value ? '#000'    : '#64748B',
          }"
        >
          {{ f.label }}
        </button>
      </div>
    </div>


    <!-- Stats Row -->
    <div style="display:flex; gap:8px; padding:12px 16px;">
      <div
        v-for="stat in stats"
        :key="stat.label"
        style="flex:1; background:#1A1E28; border:1px solid #252B38;
               border-radius:10px; padding:10px 14px;"
      >
        <div style="font-size:22px; font-weight:700;"
             :style="{ color: stat.color }">
          {{ stat.value }}
        </div>
        <div style="font-size:11px; color:#64748B; margin-top:2px;">
          {{ stat.label }}
        </div>
      </div>
    </div>

    <!-- Table Grid -->
    <div style="flex:1; overflow-y:auto; padding:0 16px 16px;">
      <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">
        <div
          v-for="table in filteredTables"
          :key="table.id"
          @click="openTable(table)"
          :style="{
            background:   tableBackground(table.status),
            border:       '2px solid ' + tableBorder(table.status),
            borderRadius: '12px',
            padding:      '14px 10px',
            cursor:       'pointer',
            transition:   'all 0.15s',
            textAlign:    'center',
            position:     'relative',
          }"
          @mouseenter="e => e.currentTarget.style.transform='translateY(-2px)'"
          @mouseleave="e => e.currentTarget.style.transform='translateY(0)'"
        >
          <!-- Actions Menu -->
          <div
            @click.stop
            style="position:absolute; top:8px; right:8px; display:flex; gap:4px;"
          >
            <button
              @click="editTable(table)"
              style="width:24px; height:24px; background:#3B82F6; color:#fff; border:none; border-radius:4px; font-size:10px; cursor:pointer; display:flex; align-items:center; justify-content:center;"
              title="Edit Table"
            >
              ✏️
            </button>
            <button
              v-if="table.status === 'free'"
              @click="deleteTable(table)"
              style="width:24px; height:24px; background:#EF4444; color:#fff; border:none; border-radius:4px; font-size:10px; cursor:pointer; display:flex; align-items:center; justify-content:center;"
              title="Delete Table"
            >
              🗑️
            </button>
            <button
              v-if="table.status === 'occupied'"
              @click="closeTable(table)"
              style="width:24px; height:24px; background:#10B981; color:#fff; border:none; border-radius:4px; font-size:10px; cursor:pointer; display:flex; align-items:center; justify-content:center;"
              title="Close Table"
            >
              ✅
            </button>
          </div>

          <!-- Icon -->
          <div style="font-size:26px; margin-bottom:6px;">
            {{ tableIcon(table.status) }}
          </div>

          <!-- Name -->
          <div style="font-size:14px; font-weight:700; color:#F1F5F9;">
            {{ table.name }}
          </div>

          <!-- Customer Name if assigned -->
          <div v-if="table.customer_name" style="font-size:11px; color:#F59E0B; margin-top:2px;">
            👤 {{ table.customer_name }}
          </div>

          <!-- Section & capacity -->
          <div style="font-size:11px; color:#64748B; margin-top:2px;">
            {{ table.section }} · {{ table.capacity }}p
          </div>

          <!-- Status badge -->
          <div
            style="margin-top:8px; font-size:10px; font-weight:700;
                   text-transform:uppercase; letter-spacing:0.06em;
                   padding:3px 8px; border-radius:5px; display:inline-block;"
            :style="{
              background: statusBg(table.status),
              color:      statusColor(table.status),
            }"
          >
            {{ table.status }}
          </div>

          <!-- Order info if occupied -->
          <div
            v-if="table.current_order_id"
            style="font-size:10px; color:#64748B; margin-top:4px;"
          >
            #{{ table.current_order_id }}
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-if="filteredTables.length === 0"
        style="text-align:center; padding:60px 20px; color:#64748B;"
      >
        <div style="font-size:40px; margin-bottom:12px; opacity:0.4;">🪑</div>
        <div style="font-size:14px;">No {{ activeFilter }} tables found</div>
      </div>
    </div>

    <!-- Add Table Modal -->
    <Teleport to="body">
      <div v-if="showAddTableModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:50;">
        <div style="background:#12151C; border:1px solid #252B38; border-radius:12px; padding:20px; width:400px; max-width:90vw;">
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Add New Table</h3>
          
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Table Name</label>
              <input 
                v-model="newTable.name" 
                type="text" 
                placeholder="e.g., T-06"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Section</label>
              <input 
                v-model="newTable.section" 
                type="text" 
                placeholder="e.g., Main Hall"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Capacity</label>
              <input 
                v-model.number="newTable.capacity" 
                type="number" 
                min="1" 
                max="20"
                placeholder="4"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:16px; justify-content:flex-end;">
            <button 
              @click="showAddTableModal = false; newTable = {}"
              style="padding:8px 16px; background:transparent; color:#64748B; border:1px solid #252B38; border-radius:6px; cursor:pointer;"
            >
              Cancel
            </button>
            <button 
              @click="addTable"
              style="padding:8px 16px; background:#F59E0B; color:#000; border:none; border-radius:6px; cursor:pointer; font-weight:600;"
            >
              Add Table
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Direct Order Modal -->
    <Teleport to="body">
      <div v-if="showDirectOrderModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:50;">
        <div style="background:#12151C; border:1px solid #252B38; border-radius:12px; padding:20px; width:400px; max-width:90vw;">
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Create Direct Order</h3>
          
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Customer Name</label>
              <input 
                v-model="directOrder.customer_name" 
                type="text" 
                placeholder="Optional"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Order Type</label>
              <select 
                v-model="directOrder.order_type"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
                <option value="takeaway">Take Away</option>
                <option value="dine_in">Dine In (No Table)</option>
              </select>
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Notes</label>
              <textarea 
                v-model="directOrder.notes"
                placeholder="Special instructions..."
                rows="2"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px; resize:vertical;"
              ></textarea>
            </div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:16px; justify-content:flex-end;">
            <button 
              @click="showDirectOrderModal = false; directOrder = {}"
              style="padding:8px 16px; background:transparent; color:#64748B; border:1px solid #252B38; border-radius:6px; cursor:pointer;"
            >
              Cancel
            </button>
            <button 
              @click="createDirectOrder"
              style="padding:8px 16px; background:#10B981; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;"
            >
              Start Order
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Edit Table Modal -->
    <Teleport to="body">
      <div v-if="showEditTableModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:50;">
        <div style="background:#12151C; border:1px solid #252B38; border-radius:12px; padding:20px; width:400px; max-width:90vw;">
          <h3 style="font-size:18px; font-weight:700; color:#F1F5F9; margin:0 0 16px 0;">Edit Table</h3>
          
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Table Name</label>
              <input 
                v-model="editingTable.name" 
                type="text" 
                placeholder="e.g., T-06"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Section</label>
              <input 
                v-model="editingTable.section" 
                type="text" 
                placeholder="e.g., Main Hall"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>
            
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Capacity</label>
              <input 
                v-model.number="editingTable.capacity" 
                type="number" 
                min="1" 
                max="20"
                placeholder="4"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>

            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Customer Name</label>
              <input 
                v-model="editingTable.customer_name" 
                type="text" 
                placeholder="Optional"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px;"
              >
            </div>

            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Notes</label>
              <textarea 
                v-model="editingTable.notes"
                placeholder="Special notes..."
                rows="2"
                style="width:100%; padding:8px 12px; background:#1A1E28; border:1px solid #252B38; border-radius:6px; color:#F1F5F9; font-size:14px; resize:vertical;"
              ></textarea>
            </div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:16px; justify-content:flex-end;">
            <button 
              @click="showEditTableModal = false; editingTable = null"
              style="padding:8px 16px; background:transparent; color:#64748B; border:1px solid #252B38; border-radius:6px; cursor:pointer;"
            >
              Cancel
            </button>
            <button 
              @click="updateTable"
              style="padding:8px 16px; background:#3B82F6; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;"
            >
              Update Table
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router       = useRouter()
const tables       = ref([])
const activeFilter = ref('all')

// Modal states
const showAddTableModal = ref(false)
const showDirectOrderModal = ref(false)
const showEditTableModal = ref(false)

// Form data
const newTable = ref({
  name: '',
  section: '',
  capacity: 4
})

const editingTable = ref(null)

const directOrder = ref({
  customer_name: '',
  order_type: 'takeaway',
  notes: ''
})

const filters = [
  { value: 'all',      label: 'All'      },
  { value: 'free',     label: 'Free'     },
  { value: 'occupied', label: 'Occupied' },
  { value: 'reserved', label: 'Reserved' },
]

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
    value: '$' + tables.value
      .filter(t => t.currentOrder)
      .reduce((s, t) => s + parseFloat(t.currentOrder?.total ?? 0), 0)
      .toFixed(0),
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
  return {
    free:     '#1A1E28',
    occupied: 'rgba(59,130,246,0.08)',
    reserved: 'rgba(139,92,246,0.08)',
    cleaning: 'rgba(245,158,11,0.08)',
  }[status] ?? '#1A1E28'
}

function tableBorder(status) {
  return {
    free:     '#252B38',
    occupied: '#3B82F6',
    reserved: '#8B5CF6',
    cleaning: '#F59E0B',
  }[status] ?? '#252B38'
}

function tableIcon(status) {
  return {
    free:     '⬜',
    occupied: '🪑',
    reserved: '📌',
    cleaning: '🧹',
  }[status] ?? '⬜'
}

function statusBg(status) {
  return {
    free:     'rgba(16,185,129,0.12)',
    occupied: 'rgba(59,130,246,0.12)',
    reserved: 'rgba(139,92,246,0.12)',
    cleaning: 'rgba(245,158,11,0.12)',
  }[status] ?? 'rgba(100,116,139,0.12)'
}

function statusColor(status) {
  return {
    free:     '#10B981',
    occupied: '#3B82F6',
    reserved: '#8B5CF6',
    cleaning: '#F59E0B',
  }[status] ?? '#64748B'
}

// ── Actions ───────────────────────────────────────────
async function openTable(table) {
  if (table.status === 'reserved') return

  console.log('=== OPENING TABLE ===')
  console.log('Table:', table)
  
  if (table.status === 'free') {
    try {
      console.log('Creating new order for table:', table.id)
      const { data } = await axios.post('/orders', {
        table_id: table.id,
        type:     'dine_in',
        guests:   1,
      })
      console.log('Order created successfully:', data)
      
      // Save table to localStorage before navigation
      localStorage.setItem('pos_selected_table', JSON.stringify({ id: table.id, name: table.name }))
      console.log('Table saved to localStorage before navigation')
      
      router.push(`/pos/${table.id}`)
    } catch (e) {
      console.error('Failed to create order:', e)
      alert('Failed to open table: ' + (e.response?.data?.message ?? e.message))
    }
  } else {
    console.log('Table has existing order, navigating to POS')
    
    // Save table to localStorage before navigation
    localStorage.setItem('pos_selected_table', JSON.stringify({ id: table.id, name: table.name }))
    console.log('Table saved to localStorage before navigation (existing order)')
    
    router.push(`/pos/${table.id}`)
  }
}

// Add new table
async function addTable() {
  try {
    const { data } = await axios.post('/tables', newTable.value)
    tables.value.push(data)
    showAddTableModal.value = false
    newTable.value = { name: '', section: '', capacity: 4 }
    console.log('Table added successfully:', data.name)
  } catch (e) {
    console.error('Failed to add table:', e)
    alert('Failed to add table: ' + (e.response?.data?.message ?? e.message))
  }
}

// Create direct order (without table)
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
    console.error('Failed to create direct order:', e)
    alert('Failed to create order: ' + (e.response?.data?.message ?? e.message))
  }
}

// Edit table
function editTable(table) {
  editingTable.value = table
  showEditTableModal.value = true
}

// Update table
async function updateTable() {
  try {
    const { data } = await axios.put(`/tables/${editingTable.value.id}`, editingTable.value)
    const index = tables.value.findIndex(t => t.id === editingTable.value.id)
    if (index >= 0) tables.value[index] = data
    showEditTableModal.value = false
    editingTable.value = null
  } catch (e) {
    console.error('Failed to update table:', e)
    alert('Failed to update table: ' + (e.response?.data?.message ?? e.message))
  }
}

// Delete table
async function deleteTable(table) {
  if (!confirm(`Are you sure you want to delete "${table.name}"?`)) return
  
  try {
    await axios.delete(`/tables/${table.id}`)
    tables.value = tables.value.filter(t => t.id !== table.id)
  } catch (e) {
    console.error('Failed to delete table:', e)
    alert('Failed to delete table: ' + (e.response?.data?.message ?? e.message))
  }
}

// Close table (clear order and set to free)
async function closeTable(table) {
  if (!confirm(`Are you sure you want to close "${table.name}"? This will clear the current order and cart items.`)) return
  
  try {
    // Update table status to free
    await axios.put(`/tables/${table.id}`, { status: 'free' })
    
    // Update local table
    const index = tables.value.findIndex(t => t.id === table.id)
    if (index >= 0) {
      tables.value[index] = { ...tables.value[index], status: 'free', current_order_id: null, customer_name: null }
    }
    
    // Clear localStorage if this was the current table
    const currentTableId = localStorage.getItem('pos_selected_table')
    if (currentTableId) {
      const savedTable = JSON.parse(currentTableId)
      if (savedTable.id === table.id) {
        localStorage.removeItem('pos_current_order')
        localStorage.removeItem('pos_selected_table')
        console.log('Cleared localStorage for closed table:', table.name)
      }
    }
    
    console.log('Table closed successfully:', table.name)
  } catch (e) {
    console.error('Failed to close table:', e)
    alert('Failed to close table: ' + (e.response?.data?.message ?? e.message))
  }
}

// ── Lifecycle ─────────────────────────────────────────
onMounted(async () => {
  console.log('TableView mounted - loading tables...')
  try {
    const { data } = await axios.get('/tables')
    console.log('Tables loaded successfully:', data.length)
    tables.value = data
  } catch (e) {
    console.error('Failed to load tables:', e)
    console.error('Response status:', e.response?.status)
    console.error('Response data:', e.response?.data)
    
    // If authentication fails, redirect to login
    if (e.response?.status === 401) {
      console.warn('Authentication failed - redirecting to login')
      router.push('/login')
      return
    }
  }

  // Real-time table updates
  if (window.Echo) {
    window.Echo.channel('pos-tables').listen('.table.updated', (e) => {
      const idx = tables.value.findIndex(t => t.id === e.table.id)
      if (idx >= 0) tables.value[idx] = e.table
      else tables.value.push(e.table)
    })
  }
})
</script>