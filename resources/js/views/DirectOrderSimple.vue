<template>
  <div style="display:flex; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- ── LEFT: Menu ── -->
    <div style="flex:1; display:flex; flex-direction:column;
                border-right:1px solid #252B38; overflow:hidden; min-width:0;">

      <!-- Top Bar -->
      <div style="display:flex; align-items:center; justify-content:space-between;
                  padding:10px 16px; border-bottom:1px solid #252B38; background:#12151C; flex-shrink:0;">
        <button @click="$router.push('/')"
          style="font-size:12px; color:#64748B; background:#1A1E28; border:1px solid #252B38;
                 cursor:pointer; padding:6px 12px; border-radius:6px;">← Back</button>

        <div style="text-align:center;">
          <div style="font-weight:700; font-size:15px; color:#F1F5F9;">⚡ Direct Order</div>
          <div style="font-size:11px; color:#64748B; margin-top:2px;">
            {{ currentOrder?.order_number ?? '...' }}
          </div>
        </div>

        <!-- Order type buttons -->
        <div style="display:flex; gap:4px;">
          <button
            v-for="t in orderTypes" :key="t.value"
            @click="updateOrderType(t.value)"
            style="padding:4px 10px; border-radius:6px; font-size:11px;
                   font-weight:600; border:1px solid #252B38; cursor:pointer; transition:all 0.15s;"
            :style="{
              background: selectedType === t.value ? t.color : 'transparent',
              color:      selectedType === t.value ? '#000'  : '#64748B',
              borderColor:selectedType === t.value ? t.color : '#252B38',
            }"
          >{{ t.label }}</button>
        </div>
      </div>

      <!-- Category Bar -->
      <div style="display:flex; gap:8px; padding:10px 14px; border-bottom:1px solid #252B38;
                  overflow-x:auto; flex-shrink:0;">
        <button
          v-for="cat in menuStore.categories" :key="cat.id"
          @click="menuStore.setActiveCategory(cat.id)"
          style="padding:7px 16px; border-radius:20px; font-size:12px; font-weight:500;
                 border:1px solid #252B38; white-space:nowrap; cursor:pointer; transition:all 0.15s; flex-shrink:0;"
          :style="{
            background:  menuStore.activeCategory === cat.id ? '#F59E0B' : 'transparent',
            color:       menuStore.activeCategory === cat.id ? '#000'    : '#64748B',
            borderColor: menuStore.activeCategory === cat.id ? '#F59E0B' : '#252B38',
          }"
        >{{ cat.name }}</button>
      </div>

      <!-- Menu Items -->
      <div v-if="currentOrder" style="flex:1; overflow-y:auto; padding:12px;">
        <div v-if="loading || menuStore.loading"
          style="display:flex; align-items:center; justify-content:center; height:100%; color:#64748B;">
          <span style="font-size:13px;">Loading menu...</span>
        </div>

        <div v-else style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
          <button
            v-for="item in currentItems" :key="item.id"
            @click="addItem(item)"
            :disabled="!item.is_available"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:12px;
                   padding:14px 10px; text-align:center; cursor:pointer;
                   display:flex; flex-direction:column; align-items:center; transition:all 0.15s;
                   position:relative;"
            :style="{ opacity: item.is_available ? '1' : '0.4' }"
            @mouseenter="e => { if(item.is_available) {
              e.currentTarget.style.transform='translateY(-2px)'
              e.currentTarget.style.borderColor = item.modifier_groups?.length > 0
                ? '#8B5CF6' : item.is_instant ? '#10B981' : '#F59E0B'
            }}"
            @mouseleave="e => {
              e.currentTarget.style.transform='translateY(0)'
              e.currentTarget.style.borderColor='#252B38'
            }"
          >
            <!-- Instant badge -->
            <div v-if="item.is_instant"
              style="position:absolute; top:6px; right:6px; font-size:9px; font-weight:700;
                     background:rgba(16,185,129,0.15); color:#10B981; padding:1px 5px;
                     border-radius:4px; text-transform:uppercase; letter-spacing:0.05em;">
              ⚡ Instant
            </div>

            <!-- Modifier badge -->
            <div v-if="item.modifier_groups && item.modifier_groups.length > 0"
              style="position:absolute; top:6px; left:6px; font-size:9px; font-weight:700;
                     background:rgba(139,92,246,0.15); color:#8B5CF6; padding:1px 5px;
                     border-radius:4px;">
              ⚙️
            </div>

            <div style="font-size:28px; margin-bottom:6px;">{{ item.icon ?? '🍽️' }}</div>
            <div style="font-size:12px; font-weight:500; color:#F1F5F9; line-height:1.3;">{{ item.name }}</div>
            <div style="font-size:14px; font-weight:700; margin-top:5px;"
              :style="{ color: item.is_instant ? '#10B981' : '#F59E0B' }">
              Rs. {{ item.price }}
            </div>

            <!-- Modifier indicator -->
            <div v-if="item.modifier_groups && item.modifier_groups.length > 0"
              style="font-size:9px; color:#8B5CF6; margin-top:3px;
                     display:flex; align-items:center; gap:2px;">
              <span>{{ item.modifier_groups.length }} option{{ item.modifier_groups.length > 1 ? 's' : '' }}</span>
            </div>
          </button>
        </div>
      </div>

      <!-- Create New Order (when no current order) -->
      <div v-else style="flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
        <div style="text-align:center; margin-bottom:20px;">
          <h3 style="color:#F1F5F9; margin:0 0 8px 0;">Start New Direct Order</h3>
          <p style="color:#64748B; font-size:12px; margin:0;">Create a new direct order to begin</p>
        </div>

        <!-- Create New Order Button -->
        <button 
          @click="createNewDirectOrder"
          style="padding:12px 24px; background:#10B981; color:#fff; 
                 border:none; border-radius:8px; font-size:14px; font-weight:600; 
                 cursor:pointer; display:flex; align-items:center; 
                 justify-content:center; gap:8px;"
        >
          ➕ Create New Direct Order
        </button>
      </div>
    </div>

    <!-- ── RIGHT: Cart ── -->
    <div v-if="currentOrder" style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;">

      <!-- Customer Name Input -->
      <div style="padding:12px 14px; border-bottom:1px solid #252B38; flex-shrink:0;">
        <div style="font-size:11px; color:#64748B; margin-bottom:6px; font-weight:600;
                    text-transform:uppercase; letter-spacing:0.06em;">Customer</div>
        <input
          v-model="customerName"
          placeholder="Walk-in / Name (optional)"
          style="width:100%; background:#1A1E28; border:1px solid #252B38; border-radius:8px;
                 padding:8px 12px; color:#F1F5F9; font-size:13px; outline:none;
                 font-family:inherit; transition:border 0.15s;"
          @focus="e => e.target.style.borderColor='#F59E0B'"
          @blur="e => e.target.style.borderColor='#252B38'"
          @change="updateCustomerName"
        />
      </div>

      <!-- Cart Header -->
      <div style="padding:10px 14px; border-bottom:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <div style="font-size:11px; color:#64748B;">
            {{ totalItemCount }} item(s) · Round {{ currentRound }}
          </div>
          <button @click="refreshOrder"
            style="background:none; border:none; color:#64748B; font-size:12px; cursor:pointer;">🔄</button>
        </div>
      </div>

      <!-- Cart Items -->
      <div style="flex:1; overflow-y:auto; padding:10px;">

        <!-- Empty -->
        <div v-if="orderItems.length === 0"
          style="display:flex; flex-direction:column; align-items:center;
                 justify-content:center; height:100%; color:#64748B; gap:8px;">
          <span style="font-size:40px; opacity:0.2;">⚡</span>
          <span style="font-size:13px;">Add items to start</span>
          <span style="font-size:11px; color:#334155;">Instant items charge immediately</span>
        </div>

        <template v-else>
          <!-- Cart items will be rendered here -->
          <div v-for="item in orderItems" :key="item.id"
            style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                   background:#1A1E28; border:1px solid #252B38;">
            <div style="display:flex; align-items:center; gap:8px;">
              <div style="flex:1; font-size:12px; font-weight:500; color:#F1F5F9;">
                {{ item.item_name }}
              </div>
              <div style="font-size:11px; font-weight:700; color:#F59E0B;">
                ×{{ item.quantity }}
              </div>
              <div style="font-size:11px; color:#64748B; min-width:44px; text-align:right;">
                Rs. {{ parseFloat(item.total_price).toFixed(2) }}
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Cart Footer -->
      <div style="padding:12px 14px; border-top:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Subtotal</span><span>Rs. {{ currentOrder?.subtotal ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Tax (10%)</span><span>Rs. {{ currentOrder?.tax_amount ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:15px;
                    border-top:1px solid #252B38; padding-top:8px; margin-top:4px;">
          <span style="color:#F1F5F9;">Total</span>
          <span style="color:#F59E0B;">Rs. {{ currentOrder?.total ?? '0.00' }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div style="padding:12px 14px; border-top:1px solid #252B38; flex-shrink:0;">
        <button 
          v-if="unsentItems.length > 0"
          @click="sendKOT"
          style="width:100%; padding:8px; background:#F59E0B; color:#000; border:none;
                 border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;
                 margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:6px;"
        >
          🍳 Send KOT ({{ unsentItems.length }} items)
        </button>

        <button 
          @click="showPayment = true"
          style="width:100%; padding:8px; background:#10B981; color:#fff; border:none;
                 border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;
                 display:flex; align-items:center; justify-content:center; gap:6px;"
          @mouseenter="e => { if(orderItems.length > 0) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          💳 Charge Rs. {{ currentOrder?.total ?? '0.00' }}
        </button>
      </div>
    </div>

    <!-- Payment Modal -->
    <Teleport to="body">
      <div v-if="showPayment"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;">
        <PaymentModal :order="currentOrder" @paid="onPaid" @cancel="showPayment = false" />
      </div>
    </Teleport>

    <!-- Modifier Selector Modal -->
    <Teleport to="body">
      <ModifierSelector
        v-if="modifierItem"
        :item="modifierItem"
        @confirm="onModifierConfirm"
        @cancel="modifierItem = null"
      />
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; top:20px; right:20px; padding:12px 16px; border-radius:8px;
               font-size:13px; font-weight:500; z-index:100; animation:slideIn 0.3s ease;"
        :style="{
          background: toast.type === 'success' ? '#10B981' : toast.type === 'error' ? '#EF4444' : '#3B82F6',
          color: '#fff'
        }">
        {{ toast.message }}
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter }                from 'vue-router'
import { useOrderStore }            from '@/stores/orders'
import { useMenuStore }             from '@/stores/menu'
import axios                       from 'axios'
import PaymentModal                 from '@/components/PaymentModal.vue'
import ModifierSelector            from '@/components/ModifierSelector.vue'

const router       = useRouter()
const orderStore   = useOrderStore()
const menuStore    = useMenuStore()

// Reactive data
const loading        = ref(false)
const showPayment    = ref(false)
const modifierItem   = ref(null)
const toast          = ref({ show: false, message: '', type: 'success' })
const selectedType   = ref('takeaway')
const customerName  = ref('')

// Order types
const orderTypes = [
  { value: 'takeaway', label: 'Takeaway', color: '#10B981' },
  { value: 'dine_in',   label: 'Dine In',  color: '#F59E0B' },
  { value: 'bar',       label: 'Bar',      color: '#8B5CF6' },
  { value: 'counter',   label: 'Counter',  color: '#3B82F6' },
  { value: 'delivery',  label: 'Delivery', color: '#EF4444' },
]

// Computed
const currentOrder = computed(() => orderStore.currentOrder)
const currentItems = computed(() => menuStore.getItemsByCategory(menuStore.activeCategory))

const orderItems = computed(() => {
  const items = currentOrder.value?.items ?? []
  return items.filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })
})

const unsentItems = computed(() =>
  orderItems.value.filter(i => !i.kot_round)
)

const sentItems = computed(() =>
  orderItems.value.filter(i => i.kot_round)
)

const instantItems = computed(() =>
  orderItems.value.filter(i => i.is_instant)
)

const currentRound = computed(() => {
  if (sentItems.value.length === 0) return 1
  const max = Math.max(...sentItems.value.map(i => i.kot_round))
  return unsentItems.value.length > 0 ? max + 1 : max
})

const totalItemCount = computed(() =>
  orderItems.value.reduce((s, i) => s + i.quantity, 0)
)

// Actions
async function createNewDirectOrder() {
  try {
    const { data } = await axios.post('/direct-orders', {
      type: selectedType.value,
      customer_name: customerName.value || 'Walk-in',
    })
    
    orderStore.setOrder(data)
    showToast(`Created ${data.order_number} ✓`, 'success')
  } catch (e) {
    showToast('Failed to create order', 'error')
  }
}

async function updateOrderType(type) {
  if (!currentOrder.value) {
    selectedType.value = type
    return
  }
  
  try {
    await axios.patch(`/direct-orders/${currentOrder.value.id}/type`, { type })
    orderStore.currentOrder.type = type
    showToast(`Order type updated to ${type} ✓`, 'success')
  } catch (e) {
    const message = e.response?.data?.message || 'Failed to update order type'
    showToast(message, 'error')
    console.error('Update type error:', e)
  }
}

async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return

  const hasModifiers = menuItem.modifier_groups && menuItem.modifier_groups.length > 0
  if (hasModifiers) {
    modifierItem.value = menuItem
    return
  }

  try {
    await orderStore.addItem(currentOrder.value.id, {
      menu_item_id: menuItem.id,
      quantity:     1,
      is_instant:   menuItem.is_instant,
    })

    if (menuItem.is_instant) {
      showToast(`${menuItem.name} added ⚡`, 'success')
    }
  } catch (e) {
    showToast('Failed to add item', 'error')
  }
}

async function onModifierConfirm(payload) {
  modifierItem.value = null
  try {
    await orderStore.addItem(currentOrder.value.id, {
      menu_item_id:       payload.menu_item_id,
      quantity:           payload.quantity,
      selected_modifiers: payload.selected_modifiers,
      notes:              payload.notes,
      is_instant:         payload.is_instant,
    })
    showToast('Added to cart ✓', 'success')
  } catch (e) {
    showToast('Failed to add item', 'error')
  }
}

async function sendKOT() {
  if (unsentItems.value.length === 0) {
    showToast('No new items to send', 'error')
    return
  }
  
  try {
    const res = await orderStore.sendKOT(currentOrder.value.id)
    showToast(`Round ${res.round}: ${res.items_sent} item(s) sent to kitchen! 🍳`, 'success')
  } catch (e) {
    showToast('Failed to send KOT', 'error')
  }
}

async function refreshOrder() {
  if (currentOrder.value?.id) {
    await orderStore.fetchOrder(currentOrder.value.id)
  }
}

async function updateCustomerName() {
  if (!currentOrder.value) return
  try {
    await axios.patch(`/direct-orders/${currentOrder.value.id}/customer`, {
      customer_name: customerName.value || 'Walk-in',
    })
  } catch (e) {
    console.warn('Failed to update customer name:', e)
  }
}

async function onPaid() {
  showPayment.value = false
  orderStore.clearOrder()
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// Lifecycle
onMounted(async () => {
  loading.value = true
  
  // Load menu
  await menuStore.fetchMenu()
  
  // Check for existing order in localStorage
  const savedOrder = localStorage.getItem('pos_current_order')
  if (savedOrder) {
    try {
      const orderData = JSON.parse(savedOrder)
      if (!orderData.table_id) {
        console.log('Loading saved direct order:', orderData.order_number)
        await orderStore.fetchOrder(orderData.id)
        // Sync selected type with order type
        selectedType.value = orderData.type || 'takeaway'
        customerName.value = orderData.customer_name || ''
      } else {
        orderStore.clearOrder()
      }
    } catch (e) {
      console.warn('Failed to load saved order:', e)
      orderStore.clearOrder()
    }
  }
  
  loading.value = false
})
</script>

<style scoped>
@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
</style>
