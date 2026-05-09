<template>
  <div class="table-view-container" :class="isDarkTheme ? 'dark-theme' : 'light-theme'" style="display:flex; flex-direction:column; height:100%; background:var(--bg-primary); font-family:system-ui,-apple-system,sans-serif;">

    <!-- Header -->
    <div style="padding:10px 12px; border-bottom:1px solid var(--border-color);
                background:var(--bg-secondary); display:flex; flex-direction:column; gap:8px;">

      <!-- Top Row: Title + Add Table -->
      <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0;">
        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
          <h1 style="font-size:16px; font-weight:700; color:var(--text-primary); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            NISH FAMILY
          </h1>
          <span style="font-size:11px; color:var(--text-secondary); font-weight:500; margin-left:4px;">
            Restaurant POS
          </span>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
          <button @click="showAddTableModal = true"
            style="padding:6px 10px; background:var(--success-color); border:1px solid var(--success-color);
                   color:#fff; font-size:11px; font-weight:600; border-radius:6px;
                   cursor:pointer; white-space:nowrap; -webkit-tap-highlight-color:transparent;
                   transition:all 0.15s;"
            @mouseenter="e => { e.currentTarget.style.background='var(--success-hover)' }"
            @mouseleave="e => { e.currentTarget.style.background='var(--success-color)' }"
            @touchstart="e => e.currentTarget.style.opacity='0.85'"
            @touchend="e => e.currentTarget.style.opacity='1'"
          >
            + Add Table
          </button>
          <button @click="$router.push({ name: 'direct-order' })"
            style="padding:6px 10px; background:var(--accent-color); border:1px solid var(--accent-color);
                   color:#fff; font-size:11px; font-weight:600; border-radius:6px;
                   cursor:pointer; white-space:nowrap; -webkit-tap-highlight-color:transparent;
                   transition:all 0.15s;"
            @mouseenter="e => { e.currentTarget.style.background='var(--accent-hover)' }"
            @mouseleave="e => { e.currentTarget.style.background='var(--accent-color)' }"
            @touchstart="e => e.currentTarget.style.opacity='0.85'"
            @touchend="e => e.currentTarget.style.opacity='1'"
          >
            Direct
          </button>
        </div>
      </div>

      <!-- Search Bar -->
      <div style="display:flex; gap:8px; align-items:center; padding:0 12px;">
        <div style="flex:1; position:relative;">
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            @keydown.enter="onSearchEnter"
            placeholder="Search tables..."
            style="width:100%; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:8px; padding:10px 36px 10px 40px; color:var(--text-primary);
                   font-size:14px; outline:none; box-sizing:border-box; font-family:inherit;"
            :style="{
              borderColor: searchQuery ? '#F59E0B' : 'var(--border-color)',
            }"
          />
          <div v-if="searchQuery"
            @click="clearSearch"
            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
                   color:var(--text-secondary); cursor:pointer; font-size:16px;
                   padding:4px; border-radius:4px; display:flex; align-items:center;
                   justify-content:center;">
            ×
          </div>
        </div>
        <button
          v-if="searchQuery"
          @click="clearSearch"
          style="padding:8px 12px; background:var(--bg-tertiary); border:1px solid var(--border-color);
                 border-radius:8px; color:var(--text-secondary); cursor:pointer; font-size:12px;
                 white-space:nowrap; -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
          Clear
        </button>
      </div>

      <!-- Stats Row -->
      <div class="stats-row">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="stat-card"
        >
          <div class="stat-value" :style="{ color: stat.color }">
            {{ stat.value }}
          </div>
          <div class="stat-label">
            {{ stat.label }}
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="activeFilter = f.value"
          :class="['filter-tab', { active: activeFilter === f.value }]"
        >
          {{ f.label }}
          <span class="filter-count" v-if="f.count !== undefined"> ({{ f.count }})</span>
        </button>
      </div>
    </div>

    <!-- Table Grid -->
    <div style="flex:1; overflow-y:auto; padding:10px; -webkit-overflow-scrolling:touch;">
      <!-- Search Results Message -->
      <div v-if="searchQuery && filteredTables.length === 0"
        style="text-align:center; padding:40px 20px; color:var(--text-secondary);">
        <div style="font-size:24px; opacity:0.2; margin-bottom:12px;">🔍</div>
        <div style="font-size:14px; margin-bottom:4px;">No tables found</div>
        <div style="font-size:12px; color:var(--text-muted);">
          Try different keywords or clear search to see all tables
        </div>
      </div>

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
            pointerEvents: 'auto',
          }"
          @touchstart="e => e.currentTarget.style.opacity='0.8'"
          @touchend="e => e.currentTarget.style.opacity='1'"
          @touchcancel="e => e.currentTarget.style.opacity='1'"
        >
          <!-- Action Buttons -->
          <div
            @click.stop
            style="position:absolute; top:6px; right:6px; display:flex; gap:3px; z-index:10;"
          >
            <button
              @click.stop="editTable(table)"
              style="width:28px; height:28px; background:#3B82F6; color:#fff; border:none;
                     border-radius:6px; font-size:12px; cursor:pointer;
                     display:flex; align-items:center; justify-content:center;
                     -webkit-tap-highlight-color:transparent; touch-action:manipulation;
                     position:relative; z-index:11;"
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

          <!-- Table Image Preview -->
          <div class="table-image-preview">
            <div class="table-image-bg">
              <img v-if="table.image"
                   :src="getTableImageUrl(table.image)"
                   :alt="table.name"
                   class="table-image"
                   @load="console.log('Image loaded successfully:', table.image, 'URL:', getTableImageUrl(table.image))"
                   @error="e => { console.error('Image failed to load:', table.image, 'URL:', getTableImageUrl(table.image)); console.error('Error details:', e); e.target.style.display='none'; }"
              />
              <div v-if="!table.image" class="table-icon">
                {{ tableIcon(table.status) }}
              </div>
            </div>
          </div>

          <!-- Name -->
          <div class="table-name" v-html="highlightText(table.name, searchQuery)">
          </div>

          <!-- Customer -->
          <div v-if="table.customer_name" class="table-customer" v-html="highlightText('?? ' + table.customer_name, searchQuery)">
          </div>

          <!-- Section & Capacity -->
          <div class="table-details" v-html="highlightText(table.section + ' · ' + table.capacity + 'p', searchQuery)">
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
                  display:flex; align-items:center; justify-content:center;
                  z-index:50; padding:16px;"
           @click.self="showAddTableModal = false">
        <div style="background:#12151C; border:1px solid #252B38;
                    border-radius:16px; padding:20px 16px;
                    width:100%; max-width:480px;">
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
            
            <!-- Table Image Upload -->
            <div>
              <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Table Image</label>
              <div style="display:flex; flex-direction:column; gap:8px;">
                <!-- Current Image Preview -->
                <div v-if="editingTable.image || selectedImagePreview"
                     style="position:relative; width:100%; height:120px; border-radius:8px; overflow:hidden; background:#1A1E28;">
                  <img v-if="editingTable.image"
                       :src="getTableImageUrl(editingTable.image)"
                       :alt="editingTable.name"
                       style="width:100%; height:100%; object-fit:cover;"
                       @error="e => e.target.style.display='none'" />
                  <img v-else-if="selectedImagePreview"
                       :src="selectedImagePreview"
                       :alt="editingTable.name"
                       style="width:100%; height:100%; object-fit:cover;" />
                  <div v-if="!editingTable.image && !selectedImagePreview"
                       style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#64748B; font-size:14px;">
                    No image uploaded
                  </div>
                </div>
                
                <!-- Image Upload Input -->
                <div style="display:flex; gap:8px; align-items:center;">
                  <input
                    ref="imageInput"
                    type="file"
                    accept="image/*"
                    @change="onImageSelect"
                    style="display:none;"
                  />
                  <button
                    @click="$refs.imageInput.click()"
                    style="flex:1; padding:10px; background:#3B82F6; color:#fff; border:none;
                           border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;
                           display:flex; align-items:center; justify-content:center; gap:6px;">
                    <span>?</span> Choose Image
                  </button>
                  <button
                    v-if="editingTable.image || selectedImagePreview"
                    @click="removeTableImage"
                    style="padding:10px; background:#EF4444; color:#fff; border:none;
                           border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">
                    Remove
                  </button>
                </div>
                
                <!-- Image Upload Status -->
                <div v-if="imageUploadStatus"
                     style="font-size:11px; color:#64748B; margin-top:4px;">
                  {{ imageUploadStatus }}
                </div>
              </div>
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

// Theme State - responds to sidebar toggle
const isDarkTheme = ref(localStorage.getItem('theme') !== 'light')

// Watch for theme changes from localStorage (sidebar toggle)
function watchThemeChanges() {
  setInterval(() => {
    const shouldBeDark = localStorage.getItem('theme') !== 'light'
    if (isDarkTheme.value !== shouldBeDark) {
      isDarkTheme.value = shouldBeDark
    }
  }, 500)
}
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
const showDirectOrderModal = ref(false)
const searchQuery = ref('')
const showEditTableModal    = ref(false)

// Form data
const newTable = ref({ name: '', section: '', capacity: 4 })
const editingTable = ref(null)
const directOrder  = ref({ customer_name: '', order_type: 'takeaway', notes: '' })
const selectedImageFile = ref(null)
const selectedImagePreview = ref('')
const imageUploadStatus = ref('')

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

// ── Search Functions ───────────────────────────────────
function onSearchInput(event) {
  const query = event.target.value
  console.log('?? Search input:', query)
  if (query && query.trim()) {
    searchQuery.value = query
    console.log('?? Search query set:', searchQuery.value)
  } else {
    clearSearch()
  }
}

function onSearchEnter() {
  // Focus on first search result when Enter is pressed
  if (filteredTables.value.length > 0) {
    const firstTable = filteredTables.value[0]
    if (firstTable) {
      openTable(firstTable)
    }
  }
}

function clearSearch() {
  searchQuery.value = ''
}

function highlightText(text, query) {
  if (!query) return text
  const regex = new RegExp(`(${query})`, 'gi')
  return text.replace(regex, match => `<mark style="background:#F59E0B; color:#000; padding:1px 2px; border-radius:2px;">${match}</mark>`)
}

function getTableImageUrl(image) {
  if (!image) return ''
  // Try different URL paths to find the correct one
  const url = `/storage/tables/${image}`
  console.log('Generated image URL:', url)
  return url
}

// Image Upload Functions
function onImageSelect(event) {
  const file = event.target.files[0]
  if (!file) return
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    imageUploadStatus.value = 'Please select an image file'
    return
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    imageUploadStatus.value = 'Image must be less than 5MB'
    return
  }
  
  selectedImageFile.value = file
  imageUploadStatus.value = `Selected: ${file.name}`
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    selectedImagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

function removeTableImage() {
  selectedImageFile.value = null
  selectedImagePreview.value = ''
  imageUploadStatus.value = 'Image removed'
  
  // Clear file input
  if (this.$refs?.imageInput) {
    this.$refs.imageInput.value = ''
  }
}

async function uploadTableImage(file) {
  if (!file) return null
  
  const formData = new FormData()
  formData.append('image', file)
  
  try {
    const response = await axios.post('/tables/upload-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data.filename
  } catch (error) {
    console.error('Image upload failed:', error)
    throw error
  }
}

// ── Computed ───────────────────────────────────────
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
])

const filteredTables = computed(() => {
  const query = searchQuery.value?.toLowerCase().trim()
  console.log('?? Table search debug:', { query, activeFilter: activeFilter.value, totalTables: tables.value.length })
  
  if (activeFilter.value === 'all') {
    if (query) {
      const filtered = tables.value.filter(t => 
        t.name.toLowerCase().includes(query) ||
        t.section.toLowerCase().includes(query) ||
        (t.customer_name && t.customer_name.toLowerCase().includes(query))
      )
      console.log('?? Filtered tables (all):', filtered.length, 'from', tables.value.length)
      console.log('?? Sample tables:', tables.value.slice(0, 3).map(t => ({ name: t.name, section: t.section, customer_name: t.customer_name })))
      return filtered
    }
    return tables.value
  } else {
    if (query) {
      const filtered = tables.value.filter(t => 
        t.status === activeFilter.value &&
        (
          t.name.toLowerCase().includes(query) ||
          t.section.toLowerCase().includes(query) ||
          (t.customer_name && t.customer_name.toLowerCase().includes(query))
        )
      )
      console.log('?? Filtered tables (filtered):', filtered.length, 'from', tables.value.length)
      return filtered
    }
    return tables.value.filter(t => t.status === activeFilter.value)
  }
})

// ── Style helpers ─────────────────────────────────────// Style helpers - now use CSS variables
function tableBackground(status) {
  return { 
    free:'var(--table-bg-free)', 
    occupied:'var(--table-bg-occupied)', 
    reserved:'var(--table-bg-reserved)', 
    cleaning:'var(--table-bg-cleaning)' 
  }[status] ?? 'var(--table-bg-free)'
}
function tableBorder(status) {
  return { 
    free:'var(--table-border-free)', 
    occupied:'var(--table-border-occupied)', 
    reserved:'var(--table-border-reserved)', 
    cleaning:'var(--table-border-cleaning)' 
  }[status] ?? 'var(--table-border-free)'
}
function tableIcon(status) {
  return { free:'⬜', occupied:'🪑', reserved:'📌', cleaning:'🧹' }[status] ?? '⬜'
}
function statusBg(status) {
  return { free:'rgba(16,185,129,0.12)', occupied:'rgba(59,130,246,0.12)', reserved:'rgba(139,92,246,0.12)', cleaning:'rgba(245,158,11,0.12)' }[status] ?? 'rgba(100,116,139,0.12)'
}
function statusColor(status) {
  return { free:'var(--success-color)', occupied:'var(--table-border-occupied)', reserved:'var(--table-border-reserved)', cleaning:'var(--table-border-cleaning)' }[status] ?? 'var(--text-muted)'
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
  console.log('Edit button clicked for table:', table.name)
  editingTable.value = { ...table }
  showEditTableModal.value = true
}


async function updateTable() {
  try {
    imageUploadStatus.value = 'Updating table...'
    
    // Upload image if selected
    let imageFilename = editingTable.value.image
    if (selectedImageFile.value) {
      imageUploadStatus.value = 'Uploading image...'
      imageFilename = await uploadTableImage(selectedImageFile.value)
    }
    
    // Update table data
    const tableData = {
      ...editingTable.value,
      image: imageFilename
    }
    
    const { data } = await axios.put(`/tables/${editingTable.value.id}`, tableData)
    console.log('Table updated response:', data)
    const index = tables.value.findIndex(t => t.id === editingTable.value.id)
    if (index >= 0) {
      tables.value[index] = data
      console.log('Table updated in local state:', tables.value[index])
    }
    
    // Reset modal state
    showEditTableModal.value = false
    editingTable.value = null
    selectedImageFile.value = null
    selectedImagePreview.value = ''
    imageUploadStatus.value = ''
    
  } catch (e) {
    imageUploadStatus.value = 'Update failed'
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
    if (e.response?.status === 401) router.push({ name: 'login' })
  }
}

onMounted(async () => {
  window.addEventListener('resize', updateWidth)
  await loadTables()
  refreshInterval.value = setInterval(loadTables, 30000)
  watchThemeChanges()

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
/* Theme variables */
.table-view-container {
  /* Dark mode defaults */
  --bg-primary: #0f0f0f;
  --bg-secondary: #161616;
  --bg-tertiary: #1e1e1e;
  --bg-quaternary: #252525;
  --border-color: #2a2a2a;
  --border-secondary: #333;
  --text-primary: #f0ede8;
  --text-secondary: #9a9590;
  --text-muted: #6b6762;
  --accent-color: #D85A30;
  --accent-hover: #e8733f;
  --success-color: #10B981;
  --success-hover: #059669;
  --table-bg-free: #1A1E28;
  --table-border-free: #252B38;
  --table-bg-occupied: rgba(59,130,246,0.08);
  --table-border-occupied: #3B82F6;
  --table-bg-reserved: rgba(139,92,246,0.08);
  --table-border-reserved: #8B5CF6;
  --table-bg-cleaning: rgba(245,158,11,0.08);
  --table-border-cleaning: #F59E0B;
}

/* Light theme overrides */
.table-view-container.light-theme {
  --bg-primary: #F5F0E8;
  --bg-secondary: #FFFFFF;
  --bg-tertiary: #F0EBE1;
  --bg-quaternary: #E8E0D4;
  --border-color: #DDD5C8;
  --border-secondary: #C8BBAA;
  --text-primary: #1A1410;
  --text-secondary: #5C4F42;
  --text-muted: #8C7B6B;
  --accent-color: #C4441A;
  --accent-hover: #D85A30;
  --success-color: #1A7A3C;
  --success-hover: #157A3C;
  --table-bg-free: #F0EBE1;
  --table-border-free: #E0D5C8;
  --table-bg-occupied: #EEF3FF;
  --table-border-occupied: #1A5FC4;
  --table-bg-reserved: #F3EEFF;
  --table-border-reserved: #6B3FCC;
  --table-bg-cleaning: #FFF5DC;
  --table-border-cleaning: #B8760A;
}

/* Stats row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 0 10px;
}

.stat-card {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 80px;
  transition: all 0.2s ease;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 6px;
}

.stat-label {
  font-size: 12px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

/* Filter tabs */
.filter-tabs {
  display: flex;
  gap: 4px;
  background: var(--bg-tertiary);
  border-radius: 8px;
  padding: 3px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.filter-tab {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  color: var(--text-muted);
  white-space: nowrap;
  flex: 1;
  min-height: 36px;
  -webkit-tap-highlight-color: transparent;
}

.filter-tab.active {
  background: var(--accent-color);
  color: #000;
}

.filter-count {
  font-size: 11px;
  opacity: 0.8;
}

/* Table cards */
.table-image-preview {
  position: relative;
  margin-bottom: 4px;
  margin-top: 2px;
}

.table-image-bg {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
  background: var(--bg-tertiary);
  margin: 0 auto;
}

.table-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.table-icon {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
}

.table-name {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

.table-customer {
  font-size: 10px;
  color: var(--accent-color);
  margin-top: 2px;
  font-weight: 600;
}

.table-details {
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 2px;
}

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