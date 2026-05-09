<template>
  <div class="kitchen-display" style="display:flex; flex-direction:column; height:100%;">
    <!-- Header -->
    <div class="kitchen-header" style="padding:12px 16px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:16px;">
        <span style="width:8px; height:8px; background:var(--success-color); border-radius:50%; animation:pulse 2s infinite;"></span>
        🍳 Kitchen Display
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <button @click="fetchOrders" class="btn-success" style="font-size:12px; background:none; color:var(--success-color); border:none; cursor:pointer; padding:4px 8px; border-radius:4px; transition:all 0.15s;" @mouseenter="e => e.currentTarget.style.background='var(--success-color)'" @mouseleave="e => e.currentTarget.style.background='transparent'" title="Refresh Orders">
          🔄 Refresh
        </button>
        <div style="font-size:12px; font-family:monospace;" class="text-secondary">{{ currentTime }}</div>
      </div>
    </div>

    <!-- Orders Grid -->
    <div style="flex:1; overflow-y:auto; padding:12px;">
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:12px;">
        <div
          v-for="order in kitchenOrders"
          :key="order.id"
          class="kitchen-order-card"
          style="border-radius:12px; overflow:hidden; transition:all 0.3s;"
          :style="{ borderColor: orderBorderClass(order) }"
        >
          <!-- Order Header -->
          <div class="kitchen-order-header" style="padding:12px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:700; font-size:14px;">
              🍽️ {{ order.table?.name ?? (order.order_type === 'takeaway' ? 'Take Away' : 'Direct Order') }}
              <span v-if="order.table_id" class="text-secondary" style="font-size:11px; margin-left:4px;">(Table #{{ order.table_id }})</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span
                style="font-size:10px; font-weight:700; padding:4px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:0.5px;"
                :style="{ background: statusBg(order.status), color: statusColor(order.status) }"
              >
                {{ order.status }}
              </span>
              <span
                style="font-size:10px; font-family:monospace; padding:4px 6px; border-radius:4px;"
                :style="{ background: elapsedBg(order), color: elapsedColor(order) }"
              >
                {{ elapsed(order) }}m
              </span>
            </div>
          </div>

          <!-- Order Items -->
          <div class="kitchen-order-items" style="padding:12px;">
            <div
              v-for="item in getKitchenOrderItems(order)"
              :key="item.id"
              style="display:flex; justify-content:space-between; font-size:14px; padding:6px 0; align-items:center;"
              :style="{ opacity: item.status === 'ready' ? '0.6' : '1' }"
            >
              <div style="flex:1;">
                <div style="font-size:13px; font-weight:500;">
                  {{ item.item_name || item.name }}
                  <span v-if="item.status === 'ready'" class="text-success" style="margin-left:8px;">✅ Ready</span>
                </div>
                <div class="text-secondary" style="font-size:11px; margin-top:2px;">
                  {{ item.quantity }} × Rs.{{ item.unit_price }} = Rs.{{ item.total_price }}
                </div>
                <div v-if="item.addons && item.addons.length > 0" style="margin-top:4px;">
                  <div v-for="addon in item.addons" :key="addon.id" 
                       style="font-size:10px; color:var(--accent-color); margin-top:1px;">
                    🧄 +{{ addon.addon_name }} ({{ addon.quantity }}{{ addon.formatted_unit }}) - Rs.{{ parseFloat(addon.total_price).toFixed(2) }}
                    <span v-if="addon.notes" style="color:var(--text-muted); font-style:italic;"> - {{ addon.notes }}</span>
                  </div>
                </div>
                <div v-if="item.notes" class="text-warning" style="font-size:11px; margin-top:4px; font-style:italic;">
                  📝 {{ item.notes }}
                </div>
              </div>
              <div style="display:flex; align-items:center; gap:4px;">
                <div 
                  :style="{ 
                    background: getItemStatusColor(item.status), 
                    color: '#000', 
                    fontWeight: '700', 
                    fontSize: '10px', 
                    padding: '2px 6px', 
                    borderRadius: '4px',
                    textTransform: 'uppercase'
                  }"
                >
                  {{ item.status }}
                </div>
                <div style="background:var(--accent-color); color:#000; font-weight:700; font-size:12px; width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                  {{ item.quantity }}
                </div>
              </div>
            </div>
          </div>

          <!-- Order Info -->
          <div class="kitchen-order-info" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span class="text-secondary" style="font-size:11px;">Order #{{ order.order_number }}</span>
              <span class="text-secondary" style="font-size:11px;">{{ order.customer_name || 'Guest' }}</span>
            </div>
            <div style="font-weight:700; font-size:14px; color:var(--accent-color); text-align:center;">
              Total: Rs.{{ order.total }}
            </div>
          </div>

          <!-- Action Buttons -->
          <div style="padding:12px; display:flex; gap:8px;">
            <button
              v-if="order.status === 'confirmed'"
              @click="updateStatus(order.id, 'preparing')"
              style="flex:1; padding:8px; border-radius:6px; font-size:12px; font-weight:600; background:var(--info-color); color:#fff; border:1px solid var(--info-color); cursor:pointer; transition:all 0.15s;"
              @mouseenter="e => e.currentTarget.style.background='var(--info-hover)'"
              @mouseleave="e => e.currentTarget.style.background='var(--info-color)'"
            >
              ▶ Start Preparing
            </button>
            <button
              v-if="order.status === 'preparing'"
              @click="updateStatus(order.id, 'ready')"
              style="flex:1; padding:8px; border-radius:6px; font-size:12px; font-weight:600; background:var(--success-color); color:#fff; border:1px solid var(--success-color); cursor:pointer; transition:all 0.15s;"
              @mouseenter="e => e.currentTarget.style.background='var(--success-hover)'"
              @mouseleave="e => e.currentTarget.style.background='var(--success-color)'"
            >
              ✓ Mark Ready
            </button>
            <button
              v-if="order.status === 'ready'"
              @click="updateStatus(order.id, 'served')"
              style="flex:1; padding:8px; border-radius:6px; font-size:12px; font-weight:600; background:var(--bg-tertiary); color:var(--text-secondary); border:1px solid var(--border-color); cursor:pointer; transition:all 0.15s;"
              @mouseenter="e => e.currentTarget.style.background='var(--bg-card)'"
              @mouseleave="e => e.currentTarget.style.background='var(--bg-tertiary)'"
            >
              ✓ Served
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="kitchenOrders.length === 0" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:300px; color:var(--text-secondary); gap:12px;">
        <span style="font-size:48px; opacity:0.3;">👨‍🍳</span>
        <span style="font-size:14px;">No active orders in kitchen</span>
        <span style="font-size:12px; opacity:0.7;">Orders will appear here when sent from POS</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const orders     = ref([])
const currentTime = ref('')
let timer

const kitchenOrders = computed(() =>
  orders.value.filter(o => ['confirmed', 'preparing', 'ready'].includes(o.status))
)

function orderBorderClass(order) {
  const elapsed = getElapsedMinutes(order)
  if (elapsed > 15) return '#EF4444' // red
  if (order.status === 'ready') return '#10B981' // green
  if (order.status === 'preparing') return '#3B82F6' // blue
  return '#F59E0B' // amber
}

function getElapsedMinutes(order) {
  if (!order.kot_sent_at) return 0
  return Math.floor((Date.now() - new Date(order.kot_sent_at).getTime()) / 60000)
}

function statusBg(status) {
  const map = {
    confirmed: 'rgba(245, 158, 11, 0.1)', // amber
    preparing: 'rgba(59, 130, 246, 0.1)',  // blue
    ready:     'rgba(16, 185, 129, 0.1)',  // green
  }
  return map[status] ?? 'rgba(107, 114, 128, 0.1)'
}

function statusColor(status) {
  const map = {
    confirmed: '#F59E0B', // amber
    preparing: '#3B82F6',  // blue
    ready:     '#10B981',  // green
  }
  return map[status] ?? '#64748B'
}

function elapsedBg(order) {
  const m = elapsed(order)
  if (m > 15) return 'rgba(239, 68, 68, 0.1)' // red
  return 'rgba(16, 185, 129, 0.1)' // green
}

function elapsedColor(order) {
  const m = elapsed(order)
  if (m > 15) return '#EF4444' // red
  return '#10B981' // green
}

function elapsed(order) {
  return order.kot_sent_at ? getElapsedMinutes(order) : 0
}

// Get items that should be shown in kitchen (confirmed, preparing, ready items)
function getKitchenOrderItems(order) {
  console.log(`=== GETTING ITEMS FOR ORDER #${order.id} ===`)
  console.log('Order status:', order.status)
  console.log('Raw order items:', order.items)
  console.log('Active items:', order.activeItems)
  console.log('Active items (snake):', order.active_items)
  console.log('Pending items:', order.pendingItems)
  console.log('Pending items (snake):', order.pending_items)
  
  // Try different item sources
  let items = []
  if (order.items && Array.isArray(order.items)) {
    items = order.items
    console.log('Using order.items:', items.length)
  } else if (order.activeItems && Array.isArray(order.activeItems)) {
    items = order.activeItems
    console.log('Using order.activeItems:', items.length)
  } else if (order.active_items && Array.isArray(order.active_items)) {
    items = order.active_items
    console.log('Using order.active_items:', items.length)
  }
  
  // Filter for kitchen display (show confirmed, preparing, ready items - not pending)
  const kitchenItems = items.filter(item => {
    const isVoid = item.is_void === true || item.is_void === 1 || item.is_void === "1"
    const isValidStatus = ['confirmed', 'preparing', 'ready'].includes(item.status)
    
    console.log(`Item ${item.id}: status="${item.status}", is_void=${item.is_void}, show=${!isVoid && isValidStatus}`)
    
    return !isVoid && isValidStatus
  })
  
  console.log('Final kitchen items:', kitchenItems.length)
  return kitchenItems
}

// Get color for item status
function getItemStatusColor(status) {
  const map = {
    confirmed: '#F59E0B',   // amber
    preparing: '#3B82F6',    // blue  
    ready: '#10B981',       // green
  }
  return map[status] ?? '#64748B'
}

async function updateStatus(orderId, status) {
  try {
    console.log('Updating order status:', orderId, 'to:', status)
    await axios.patch(`/orders/${orderId}/status`, { status })
    await fetchOrders()
    console.log('Order status updated successfully')
  } catch (error) {
    console.error('Error updating order status:', error)
    console.error('Error response:', error.response?.data)
    alert('Failed to update order status')
  }
}

async function fetchOrders() {
  try {
    console.log('=== FETCHING KITCHEN ORDERS ===')
    console.log('Current auth token:', localStorage.getItem('auth_token'))
    console.log('Current user:', JSON.parse(localStorage.getItem('auth_user') || '{}'))
    
    // Try different endpoints to find orders
    let response
    try {
      // First try the filtered endpoint
      console.log('Trying filtered endpoint: /orders?status=confirmed,preparing,ready')
      response = await axios.get('/orders', { 
        params: { status: 'confirmed,preparing,ready' } 
      })
    } catch (error) {
      console.log('Filtered endpoint failed, trying all orders...')
      console.log('Error:', error.response?.data || error.message)
      // If that fails, try getting all orders
      response = await axios.get('/orders')
    }
    
    console.log('API Response:', response.data)
    console.log('Response status:', response.status)
    
    // Handle different response formats
    let fetchedOrders = []
    if (response.data && response.data.data) {
      // Paginated response
      fetchedOrders = response.data.data
      console.log('Using paginated data:', fetchedOrders)
    } else if (response.data && Array.isArray(response.data)) {
      // Direct array response
      fetchedOrders = response.data
      console.log('Using direct array:', fetchedOrders)
    } else if (response.data && response.data.orders) {
      // Nested orders array
      fetchedOrders = response.data.orders
      console.log('Using nested orders:', fetchedOrders)
    } else {
      console.log('Unknown response format:', response.data)
      fetchedOrders = []
    }
    
    console.log('Raw orders data:', fetchedOrders)
    console.log('Total orders found:', fetchedOrders.length)
    
    // Filter for kitchen orders
    const kitchenOrdersList = Array.isArray(fetchedOrders) ? fetchedOrders.filter(o => {
      const statusMatch = ['confirmed', 'preparing', 'ready'].includes(o.status)
      console.log(`Order #${o.id}: status="${o.status}" match=${statusMatch}`)
      console.log('  Order items:', o.items)
      console.log('  Order activeItems:', o.activeItems)
      console.log('  Order pendingItems:', o.pendingItems)
      return statusMatch
    }) : []
    
    console.log('Kitchen orders after filtering:', kitchenOrdersList.length)
    console.log('Kitchen orders details:', kitchenOrdersList)
    
    orders.value = kitchenOrdersList
    
    // Log order details for debugging
    if (Array.isArray(kitchenOrdersList)) {
      kitchenOrdersList.forEach(order => {
        console.log(`=== ORDER #${order.id} ===`)
        console.log(`Status: ${order.status}`)
        console.log(`Table: ${order.table?.name}`)
        console.log(`Items count: ${order.items?.length || 0}`)
        console.log(`ActiveItems count: ${order.activeItems?.length || 0}`)
        console.log(`PendingItems count: ${order.pendingItems?.length || 0}`)
        console.log('Items:', order.items)
        console.log('ActiveItems:', order.activeItems)
        console.log('PendingItems:', order.pendingItems)
      })
    }
  } catch (error) {
    console.error('=== ERROR FETCHING ORDERS ===')
    console.error('Error:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    console.error('Error headers:', error.response?.headers)
    orders.value = []
  }
}

onMounted(() => {
  console.log('KitchenDisplay mounted')
  fetchOrders()
  
  // Update time every second
  timer = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString()
  }, 1000)

  // Auto-refresh orders every 10 seconds since Echo is not available
  setInterval(() => {
    console.log('Auto-refreshing orders...')
    fetchOrders()
  }, 10000)

  // Real-time updates via WebSocket
  if (window.Echo) {
    console.log('Setting up real-time updates for kitchen')
    window.Echo.channel('pos-kitchen')
      .listen('.order.updated', (e) => {
        console.log('Received order update:', e)
        fetchOrders()
      })
  } else {
    console.warn('Echo not available for real-time updates - using polling')
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>