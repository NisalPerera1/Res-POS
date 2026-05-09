<template>
  <div style="display:flex; flex-direction:column; height:100%; background:var(--bg-primary); overflow:hidden; font-family:system-ui,-apple-system,sans-serif;">

    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between;
                padding:12px 16px; border-bottom:1px solid var(--border-color); background:var(--bg-secondary);
                flex-shrink:0; gap:12px;">

      <div style="display:flex; align-items:center; gap:12px; min-width:0;">
        <button @click="$router.push({ name: 'tables' })"
          style="font-size:13px; color:var(--text-secondary); background:var(--bg-tertiary); border:1px solid var(--border-color);
                 cursor:pointer; padding:8px 12px; border-radius:8px; min-height:36px;
                 white-space:nowrap; -webkit-tap-highlight-color:transparent; touch-action:manipulation; flex-shrink:0;"
        >Back</button>

        <div style="text-align:center; min-width:0; flex:1;">
          <div style="font-weight:700; font-size:16px; color:var(--text-primary);">Recent Orders</div>
          <div style="font-size:11px; color:var(--text-secondary);">View and print receipts</div>
        </div>
      </div>

      <div v-if="loading" style="font-size:12px; color:var(--text-secondary); padding:8px 12px;">
        Loading...
      </div>
    </div>

    <!-- Filters -->
    <div style="padding:12px 16px; border-bottom:1px solid var(--border-color); background:var(--bg-secondary); flex-shrink:0;">
      <div style="display:flex; flex-direction:column; gap:12px;">
        <!-- First Row: Method & Search -->
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <div style="font-size:12px; color:var(--text-secondary); font-weight:600;">Filter:</div>
          
          <select v-model="filters.method" @change="loadOrders"
            style="padding:6px 10px; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:6px; color:var(--text-primary); font-size:12px; cursor:pointer;">
            <option value="">All Methods</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="online">Online</option>
            <option value="voucher">Voucher</option>
            <option value="complimentary">Complimentary</option>
          </select>
          
          <input v-model="filters.search" @input="debounceSearch"
            type="text" placeholder="Search order number or customer..."
            style="flex:1; min-width:200px; padding:6px 10px; background:var(--bg-tertiary);
                   border:1px solid var(--border-color); border-radius:6px; color:var(--text-primary);
                   font-size:12px; outline:none;"
            @focus="e => e.target.style.borderColor='var(--accent-color)'"
            @blur="e => e.target.style.borderColor='var(--border-color)'"
          />
        </div>
        
        <!-- Second Row: Date Buttons & Picker -->
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <div style="font-size:12px; color:var(--text-secondary); font-weight:600;">Date:</div>
          
          <button @click="setDateToToday"
            :class="{ 'active': isToday }"
            style="padding:6px 12px; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:6px; color:var(--text-primary); font-size:12px; font-weight:600; cursor:pointer;
                   transition:all .15s;"
            @mouseenter="e => e.currentTarget.style.borderColor='var(--accent-color)'"
            @mouseleave="e => e.currentTarget.style.borderColor='var(--border-color)'"
          >Today</button>
          
          <button @click="setDateToYesterday"
            :class="{ 'active': isYesterday }"
            style="padding:6px 12px; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:6px; color:var(--text-primary); font-size:12px; font-weight:600; cursor:pointer;
                   transition:all .15s;"
            @mouseenter="e => e.currentTarget.style.borderColor='var(--accent-color)'"
            @mouseleave="e => e.currentTarget.style.borderColor='var(--border-color)'"
          >Yesterday</button>
          
          <input v-model="filters.date" @change="loadOrders"
            type="date" 
            style="padding:6px 10px; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:6px; color:var(--text-primary); font-size:12px; cursor:pointer;"
            @focus="e => e.target.style.borderColor='var(--accent-color)'"
            @blur="e => e.target.style.borderColor='var(--border-color)'"
          />
          
          <button @click="clearDateFilters"
            v-if="filters.date"
            style="padding:6px 10px; background:rgba(239,68,68,0.1); color:var(--error-color);
                   border:1px solid rgba(239,68,68,0.3); border-radius:6px;
                   font-size:11px; font-weight:600; cursor:pointer;"
            @mouseenter="e => e.currentTarget.style.background='rgba(239,68,68,0.2)'"
            @mouseleave="e => e.currentTarget.style.background='rgba(239,68,68,0.1)'"
          >Clear</button>
        </div>
      </div>
    </div>

    <!-- Orders List -->
    <div style="flex:1; overflow-y:auto; padding:16px;">
      
      <!-- Loading -->
      <div v-if="loading && orders.length === 0"
        style="display:flex; align-items:center; justify-content:center; height:200px; color:var(--text-secondary);">
        <div style="text-align:center;">
          <div style="font-size:24px; margin-bottom:12px;">Loading orders...</div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && orders.length === 0"
        style="display:flex; align-items:center; justify-content:center; height:200px; color:var(--text-secondary);">
        <div style="text-align:center;">
          <div style="font-size:48px; opacity:0.2; margin-bottom:12px;">Order</div>
          <div style="font-size:14px;">No recent orders found</div>
        </div>
      </div>

      <!-- Orders Table -->
      <div v-else>
        <!-- Table Header -->
        <div style="display:grid; grid-template-columns: 100px 1fr 120px 80px 100px 120px 150px; gap:12px; padding:12px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px 8px 0 0; font-weight:600; font-size:12px; color:var(--text-secondary);">
          <div>Order #</div>
          <div>Customer & Details</div>
          <div>Date & Time</div>
          <div>Type</div>
          <div>Subtotal</div>
          <div>Total Paid</div>
          <div>Actions</div>
        </div>
        
        <!-- Table Rows -->
        <div v-for="order in orders" :key="order.id"
          style="display:grid; grid-template-columns: 100px 1fr 120px 80px 100px 120px 150px; gap:12px; padding:12px; background:var(--bg-tertiary); border:1px solid var(--border-color); border-top:none; transition:background-color .15s;"
          @click="openModal(order)"
          @mouseenter="e => e.currentTarget.style.backgroundColor='var(--bg-secondary)'"
          @mouseleave="e => e.currentTarget.style.backgroundColor='var(--bg-tertiary)'"
        >
          <!-- Order Number -->
          <div style="font-size:14px; font-weight:700; color:var(--text-primary);">{{ order.order_number }}</div>
          
          <!-- Customer & Details -->
          <div>
            <div style="font-size:13px; color:var(--text-primary); margin-bottom:2px;">{{ order.customer_name || 'Walk-in' }}</div>
            <div style="font-size:11px; color:var(--text-secondary);">
              {{ order.table ? `Table: ${order.table}` : 'Direct Order' }}
            </div>
          </div>
          
          <!-- Date & Time -->
          <div style="font-size:12px; color:var(--text-secondary);">
            {{ formatDateTime(order.created_at) }}
          </div>
          
          <!-- Type -->
          <div>
            <div style="padding:4px 8px; border-radius:6px; font-size:11px; font-weight:600; text-align:center; display:inline-block;"
              :style="{
                background: order.status === 'paid' ? 'rgba(16,185,129,0.1)' : 
                         order.status === 'completed' ? 'rgba(59,130,246,0.1)' : 
                         order.status === 'cancelled' ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)',
                color: order.status === 'paid' ? '#10B981' : 
                       order.status === 'completed' ? '#3B82F6' : 
                       order.status === 'cancelled' ? '#EF4444' : '#F59E0B',
                border: `1px solid ${
                  order.status === 'paid' ? 'rgba(16,185,129,0.3)' : 
                  order.status === 'completed' ? 'rgba(59,130,246,0.3)' : 
                  order.status === 'cancelled' ? 'rgba(239,68,68,0.3)' : 'rgba(245,158,11,0.3)'
                }`
              }"
            >
              {{ (order.status || 'PENDING').toUpperCase() }}
            </div>
          </div>
          
          <!-- Subtotal -->
          <div style="font-size:13px; color:var(--text-secondary);">
            Rs. {{ (parseFloat(order.total || 0) * 0.9).toFixed(2) }}
          </div>
          
          <!-- Total Paid -->
          <div style="font-size:14px; font-weight:700; color:var(--accent-color);">
            Rs. {{ parseFloat(order.total || 0).toFixed(2) }}
          </div>
          
          <!-- Actions -->
          <div style="display:flex; gap:6px;">
            <button @click.stop="printReceipt(order)"
              style="padding:6px 12px; background:rgba(59,130,246,0.1); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.25); border-radius:6px;
                     font-size:11px; font-weight:600; cursor:pointer;"
              @mouseenter="e => e.currentTarget.style.background='rgba(59,130,246,0.2)'"
              @mouseleave="e => e.currentTarget.style.background='rgba(59,130,246,0.1)'"
            >Print</button>
            <button @click.stop="openModal(order)"
              style="padding:6px 12px; background:rgba(245,158,11,0.1); color:#F59E0B;
                     border:1px solid rgba(245,158,11,0.25); border-radius:6px;
                     font-size:11px; font-weight:600; cursor:pointer;"
              @mouseenter="e => e.currentTarget.style.background='rgba(245,158,11,0.2)'"
              @mouseleave="e => e.currentTarget.style.background='rgba(245,158,11,0.1)'"
            >Details</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Details Modal -->
    <Teleport to="body">
      <div v-if="selectedOrder"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;
               padding:16px;"
        @click="selectedOrder = null"
      >
        <div style="background:var(--bg-tertiary); border:1px solid var(--border-color); border-radius:16px;
                    width:500px; max-width:95vw; max-height:90vh;
                    display:flex; flex-direction:column;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);"
          @click.stop
        >
          <!-- Modal Header -->
          <div style="padding:16px 20px; border-bottom:1px solid var(--border-color);
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div>
              <div style="font-size:17px; font-weight:700; color:var(--text-primary);">
                Order {{ selectedOrder.order_number }}
              </div>
              <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">
                {{ formatDateTime(selectedOrder.created_at) }}
              </div>
            </div>
            <button @click="selectedOrder = null"
              style="width:30px; height:30px; background:transparent; border:none; color:var(--text-secondary);
                     cursor:pointer; border-radius:6px; font-size:18px; display:flex;
                     align-items:center; justify-content:center;"
              @mouseenter="e => e.currentTarget.style.background='var(--bg-secondary)'"
              @mouseleave="e => e.currentTarget.style.background='transparent'"
            >×</button>
          </div>

          <!-- Modal Content -->
          <div style="flex:1; overflow-y:auto; padding:20px;">
            <!-- Order Info -->
            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px;
                        padding:12px; margin-bottom:16px;">
              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:12px; color:var(--text-secondary);">Type</span>
                <span style="font-size:12px; color:var(--text-primary);">{{ selectedOrder.type || 'Table Order' }}</span>
              </div>
              <div v-if="selectedOrder.table" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:12px; color:var(--text-secondary);">Table</span>
                <span style="font-size:12px; color:var(--text-primary);">{{ selectedOrder.table }}</span>
              </div>
              <div v-if="selectedOrder.customer_name" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:12px; color:var(--text-secondary);">Customer</span>
                <span style="font-size:12px; color:var(--text-primary);">{{ selectedOrder.customer_name }}</span>
              </div>
              <div v-if="selectedOrder.payment_method" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:12px; color:var(--text-secondary);">Payment</span>
                <span style="font-size:12px; color:var(--text-primary);">{{ selectedOrder.payment_method }}</span>
              </div>
              <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:12px; color:var(--text-secondary);">Status</span>
                <div style="padding:4px 8px; border-radius:6px; font-size:11px; font-weight:600;"
                  :style="{
                    background: selectedOrder.status === 'paid' ? 'rgba(16,185,129,0.1)' : 
                             selectedOrder.status === 'completed' ? 'rgba(59,130,246,0.1)' : 
                             selectedOrder.status === 'cancelled' ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)',
                    color: selectedOrder.status === 'paid' ? '#10B981' : 
                           selectedOrder.status === 'completed' ? '#3B82F6' : 
                           selectedOrder.status === 'cancelled' ? '#EF4444' : '#F59E0B',
                    border: `1px solid ${
                      selectedOrder.status === 'paid' ? 'rgba(16,185,129,0.3)' : 
                      selectedOrder.status === 'completed' ? 'rgba(59,130,246,0.3)' : 
                      selectedOrder.status === 'cancelled' ? 'rgba(239,68,68,0.3)' : 'rgba(245,158,11,0.3)'
                    }`
                  }"
                >
                  {{ (selectedOrder.status || 'PENDING').toUpperCase() }}
                </div>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span style="font-size:14px; font-weight:600; color:var(--text-primary);">Total</span>
                <span style="font-size:16px; font-weight:700; color:var(--accent-color);">
                  Rs. {{ parseFloat(selectedOrder.total || 0).toFixed(2) }}
                </span>
              </div>
            </div>

            <!-- Items -->
            <div style="margin-bottom:16px;">
              <div style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">Order Items</div>
              <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; overflow:hidden;">
                <div v-if="itemsLoading" style="padding:20px; text-align:center; color:var(--text-secondary); font-size:13px;">
                  Loading items...
                </div>
                <div v-else-if="selectedOrderItems.length === 0" style="padding:20px; text-align:center; color:var(--text-secondary); font-size:13px;">
                  No items found
                </div>
                <div v-else v-for="item in selectedOrderItems" :key="item.id" 
                  style="padding:10px 12px; border-bottom:1px solid var(--border-color);">
                  <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                      <div style="font-size:13px; color:var(--text-primary);">
                        {{ item.quantity }}x {{ item.name }}
                      </div>
                      <div v-if="item.modifiers && item.modifiers.length > 0"
                        style="font-size:11px; color:var(--text-secondary); margin-top:2px;">
                        {{ item.modifiers.map(m => m.name).join(', ') }}
                      </div>
                      <div v-if="item.notes"
                        style="font-size:11px; color:var(--text-secondary); margin-top:2px; font-style:italic;">
                        * {{ item.notes }}
                      </div>
                    </div>
                    <div style="font-size:13px; color:var(--text-primary); font-weight:600;">
                      Rs. {{ parseFloat(item.total_price || 0).toFixed(2) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div style="display:flex; gap:8px;">
              <button @click="printReceipt(selectedOrder)"
                style="flex:1; padding:12px; background:#3B82F6; color:#fff; border:none;
                       border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;"
                @mouseenter="e => e.currentTarget.style.background='#2563EB'"
                @mouseleave="e => e.currentTarget.style.background='#3B82F6'"
              >Print Receipt</button>
              <button @click="selectedOrder = null"
                style="flex:1; padding:12px; background:transparent; color:var(--text-secondary);
                       border:1px solid var(--border-color); border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;"
                @mouseenter="e => e.currentTarget.style.background='var(--bg-secondary)'"
                @mouseleave="e => e.currentTarget.style.background='transparent'"
              >Close</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

// State
const loading = ref(false)
const itemsLoading = ref(false)
const orders = ref([])
const selectedOrder = ref(null)
const selectedOrderItems = ref([])

// Filters - same as Reports transactions
const filters = ref({
  method: '',
  search: '',
  date: new Date().toISOString().split('T')[0] // Default to today
})

// Computed properties for button states
const isToday = computed(() => {
  return filters.value.date === new Date().toISOString().split('T')[0]
})

const isYesterday = computed(() => {
  const yesterday = new Date()
  yesterday.setDate(yesterday.getDate() - 1)
  return filters.value.date === yesterday.toISOString().split('T')[0]
})

// Date helper functions
function setDateToToday() {
  filters.value.date = new Date().toISOString().split('T')[0]
  loadOrders()
}

function setDateToYesterday() {
  const yesterday = new Date()
  yesterday.setDate(yesterday.getDate() - 1)
  filters.value.date = yesterday.toISOString().split('T')[0]
  loadOrders()
}

// Debounce search
let searchTimeout = null
function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadOrders()
  }, 500)
}

// Clear date filters
function clearDateFilters() {
  filters.value.date = ''
  loadOrders()
}

// Load orders - exact same as Reports loadTransactions
async function loadOrders() {
  loading.value = true
  try {
    const params = { limit: 50, ...filters.value }
    Object.keys(params).forEach(k => { if (params[k] === '') delete params[k] })
    const { data } = await axios.get('/reports/transactions', { params })

    orders.value = (data.transactions ?? []).map(txn => ({
      id: txn.id,
      order_number: txn.order.order_number,
      customer_name: txn.order.customer_name,
      table: txn.order.table?.name || null,
      total: txn.amount,
      status: txn.order.payment_status || 'paid',
      type: txn.order.table ? 'Table' : 'Direct',
      created_at: txn.paid_at,
      receipt_number: txn.receipt_number,
      payment_method: txn.method_label,
      cashier: txn.cashier
    }))
  } catch (error) {
    console.error('Failed to load orders:', error)
    orders.value = []
  } finally {
    loading.value = false
  }
}

// Load order items
async function loadOrderItems(orderId) {
  itemsLoading.value = true
  try {
    const { data } = await axios.get(`/orders/${orderId}/receipt`)
    selectedOrderItems.value = data.items || []
  } catch (error) {
    console.error('Failed to load order items:', error)
    selectedOrderItems.value = []
  } finally {
    itemsLoading.value = false
  }
}

// Open modal
async function openModal(order) {
  selectedOrder.value = order
  selectedOrderItems.value = []
  await loadOrderItems(order.id)
}

// Print receipt
async function printReceipt(order) {
  try {
    const { data } = await axios.get(`/orders/${order.id}/receipt`)
    const receiptData = data
    
    const printWindow = window.open('', '_blank', 'width=400,height=700')
    printWindow.document.write(`
      <html><head>
        <title>Receipt ${order.order_number}</title>
        <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:monospace;font-size:12px;color:#000;background:#fff;padding:10px;max-width:80mm}
        .c{text-align:center}.b{font-weight:bold}.m{color:#555}
        .row{display:flex;justify-content:space-between;margin-bottom:4px}
        .div{border-top:1px dashed #aaa;margin:8px 0}
        .tot{font-size:15px;font-weight:bold}
        </style>
      </head><body>
        <div class="c b" style="font-size:16px;margin-bottom:4px">${receiptData?.receipt?.restaurant_name ?? 'Restaurant POS'}</div>
        <div class="c m" style="font-size:10px;margin-bottom:8px">${receiptData?.receipt?.printed_at}</div>
        <div class="div"></div>
        <div class="row"><span class="m">Order</span><span class="b">${receiptData?.order?.order_number}</span></div>
        ${receiptData?.order?.table ? `<div class="row"><span class="m">Table</span><span>${receiptData.order.table}</span></div>` : ''}
        ${receiptData?.order?.customer_name ? `<div class="row"><span class="m">Customer</span><span>${receiptData.order.customer_name}</span></div>` : ''}
        <div class="row"><span class="m">Cashier</span><span>${receiptData?.receipt?.cashier}</span></div>
        <div class="div"></div>
        ${(receiptData?.items ?? []).map(item => `
          <div class="row">
            <span>${item.quantity}x ${item.name}${item.modifiers?.length ? ' (' + item.modifiers.map(m => m.name).join(', ') + ')' : ''}</span>
            <span>Rs.${item.total_price}</span>
          </div>
          ${item.notes ? `<div class="m" style="font-size:10px;padding-left:8px">* ${item.notes}</div>` : ''}
        `).join('')}
        <div class="div"></div>
        <div class="row"><span class="m">Subtotal</span><span>Rs.${receiptData?.totals?.subtotal}</span></div>
        <div class="row">
          <span class="m">Service (${receiptData?.totals?.tax_rate}%)</span>
          <span>${parseFloat(receiptData?.totals?.tax_rate) === 0 ? 'Waived' : 'Rs.' + receiptData?.totals?.tax_amount}</span>
        </div>
        ${parseFloat(receiptData?.totals?.discount_amount) > 0 ? `<div class="row"><span>Discount</span><span>-Rs.${receiptData.totals.discount_amount}</span></div>` : ''}
        <div class="div"></div>
        <div class="row tot"><span>TOTAL</span><span>Rs.${receiptData?.totals?.total}</span></div>
        <div class="div"></div>
        ${(receiptData?.payments ?? []).map(payment => `
          <div class="row"><span class="b">${payment.method_label}</span><span class="b">Rs.${payment.amount}</span></div>
          ${payment.method === 'cash' && parseFloat(payment.tendered) > parseFloat(payment.amount) ? `
            <div class="row"><span class="m">Tendered</span><span>Rs.${payment.tendered}</span></div>
            <div class="row"><span class="m">Change</span><span class="b">Rs.${payment.change_amount}</span></div>
          ` : ''}
          ${payment.reference ? `<div class="row"><span class="m">Ref</span><span>${payment.reference}</span></div>` : ''}
        `).join('')}
        <div class="div"></div>
        <div class="c m" style="font-size:10px">${receiptData?.payments?.[0]?.receipt_number}</div>
        <div class="c" style="margin-top:12px">Thank you for your visit!</div>
      </body></html>
    `)
    printWindow.document.close()
    printWindow.focus()
    setTimeout(() => { printWindow.print(); printWindow.close() }, 250)
  } catch (error) {
    console.error('Failed to print receipt:', error)
    alert('Failed to print receipt')
  }
}

// Format date/time
function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Load on mount
onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.active {
  background: #F59E0B !important;
  color: #000 !important;
  border-color: #F59E0B !important;
}
</style>
