<template>
  <div style="display:flex; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- ── LEFT: Menu Panel ── -->
    <div style="flex:1; display:flex; flex-direction:column; border-right:1px solid #252B38; overflow:hidden; min-width:0;">

      <!-- Top Bar -->
      <div style="display:flex; align-items:center; justify-content:space-between;
                  padding:10px 16px; border-bottom:1px solid #252B38; background:#12151C;
                  flex-shrink:0;">
        <button
          @click="$router.push('/')"
          style="font-size:12px; color:#64748B; background:#1A1E28; border:1px solid #252B38;
                 cursor:pointer; padding:6px 12px; border-radius:6px; transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.color='#F1F5F9'"
          @mouseleave="e => e.currentTarget.style.color='#64748B'"
        >← Back</button>

        <div style="text-align:center;">
          <div style="font-weight:700; font-size:15px; color:#F1F5F9;">
            {{ table ? table.name : 'Loading...' }}
          </div>
          <div style="font-size:11px; color:#64748B; margin-top:2px;">
            {{ currentOrder?.order_number ?? '...' }}
          </div>
        </div>

        <div
          style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:6px;"
          :style="tableStatusStyle"
        >
          {{ table?.status ?? '...' }}
        </div>
      </div>

      <!-- Category Bar -->
      <div style="display:flex; gap:8px; padding:10px 14px; border-bottom:1px solid #252B38;
                  overflow-x:auto; flex-shrink:0;">
        <button
          v-for="cat in menuStore.categories"
          :key="cat.id"
          @click="menuStore.setActiveCategory(cat.id)"
          style="padding:7px 16px; border-radius:20px; font-size:12px; font-weight:500;
                 border:1px solid #252B38; white-space:nowrap; transition:all 0.15s;
                 flex-shrink:0; cursor:pointer;"
          :style="{
            background:  menuStore.activeCategory === cat.id ? '#F59E0B' : 'transparent',
            color:       menuStore.activeCategory === cat.id ? '#000'    : '#64748B',
            borderColor: menuStore.activeCategory === cat.id ? '#F59E0B' : '#252B38',
          }"
        >{{ cat.name }}</button>
      </div>

      <!-- Menu Items -->
      <div style="flex:1; overflow-y:auto; padding:12px;">
        <div v-if="loading || menuStore.loading"
          style="display:flex; flex-direction:column; align-items:center;
                 justify-content:center; height:100%; color:#64748B; gap:8px;">
          <span style="font-size:40px; opacity:0.3;">⏳</span>
          <span style="font-size:13px;">Loading menu...</span>
        </div>

        <div v-else style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
          <button
            v-for="item in currentItems"
            :key="item.id"
            @click="addItem(item)"
            :disabled="!item.is_available"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:12px;
                   padding:14px 10px; text-align:center; transition:all 0.15s; cursor:pointer;
                   display:flex; flex-direction:column; align-items:center;"
            :style="{ opacity: item.is_available ? '1' : '0.4',
                      cursor:  item.is_available ? 'pointer' : 'not-allowed' }"
            @mouseenter="e => { if(item.is_available) { e.currentTarget.style.transform='translateY(-2px)'; e.currentTarget.style.borderColor='#F59E0B' } }"
            @mouseleave="e => { e.currentTarget.style.transform='translateY(0)'; e.currentTarget.style.borderColor='#252B38' }"
          >
            <div style="font-size:28px; margin-bottom:6px;">{{ item.icon ?? '🍽️' }}</div>
            <div style="font-size:12px; font-weight:500; color:#F1F5F9; line-height:1.3;">{{ item.name }}</div>
            <div style="font-size:14px; font-weight:700; color:#F59E0B; margin-top:5px;">${{ item.price }}</div>
            <div v-if="item.is_popular"
              style="font-size:9px; background:rgba(16,185,129,0.12); color:#10B981;
                     border-radius:4px; padding:2px 7px; margin-top:4px;">Popular</div>
          </button>
        </div>
      </div>
    </div>

    <!-- ── RIGHT: Smart Cart ── -->
    <div style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;">

      <!-- Cart Header -->
      <div style="padding:14px 16px; border-bottom:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <div>
            <div style="font-weight:700; font-size:15px; color:#F1F5F9;">
              🧾 {{ table?.name ?? 'Order' }}
            </div>
            <div style="font-size:11px; color:#64748B; margin-top:3px;">
              {{ totalItemCount }} item(s) ·
              <span style="color:#F59E0B;">Round {{ currentRound }}</span>
            </div>
          </div>
          <button
            @click="refreshOrder"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:6px;
                   padding:4px 8px; color:#64748B; font-size:11px; cursor:pointer;"
            title="Refresh"
          >🔄</button>
        </div>
      </div>

      <!-- Cart Items -->
      <div style="flex:1; overflow-y:auto; padding:10px; display:flex; flex-direction:column; gap:0;">

        <!-- Loading -->
        <div v-if="loading"
          style="display:flex; align-items:center; justify-content:center;
                 height:100%; color:#64748B; gap:8px;">
          <span style="font-size:13px;">Loading order...</span>
        </div>

        <!-- Empty -->
        <div v-else-if="orderItems.length === 0"
          style="display:flex; flex-direction:column; align-items:center;
                 justify-content:center; height:100%; color:#64748B; gap:10px;">
          <span style="font-size:44px; opacity:0.25;">🛒</span>
          <span style="font-size:13px;">Tap menu items to add</span>
        </div>

        <template v-else>

          <!-- ── SENT ROUNDS ── -->
          <div v-for="round in sentRounds" :key="'r' + round.number" style="margin-bottom:12px;">

            <!-- Round divider -->
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#64748B; white-space:nowrap;">
                Round {{ round.number }}
              </div>
              <div style="flex:1; height:1px; background:#252B38;"></div>
              <div style="font-size:10px; color:#64748B; white-space:nowrap; font-family:monospace;">
                {{ round.sentTime }}
              </div>
            </div>

            <!-- Sent items -->
            <div
              v-for="item in round.items"
              :key="item.id"
              style="border-radius:8px; padding:10px 12px; margin-bottom:5px;
                     background:#1A1E28; border:1px solid #252B38; border-left-width:3px;"
              :style="{ borderLeftColor: statusColor(item.status) }"
            >
              <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">

                <!-- Left: status dot + name -->
                <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                  <div
                    style="width:9px; height:9px; border-radius:50%; flex-shrink:0;"
                    :style="{ background: statusColor(item.status) }"
                  ></div>
                  <div style="min-width:0;">
                    <div style="font-size:12px; font-weight:500; color:#F1F5F9;
                                white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                      {{ item.item_name }}
                    </div>
                    <div v-if="item.notes"
                      style="font-size:10px; color:#64748B; margin-top:1px;">
                      📝 {{ item.notes }}
                    </div>
                  </div>
                </div>

                <!-- Right: qty + price -->
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                  <div style="font-size:11px; font-weight:700; padding:2px 7px;
                               border-radius:5px; min-width:24px; text-align:center;"
                    :style="{ background: statusBg(item.status), color: statusColor(item.status) }"
                  >×{{ item.quantity }}</div>
                  <div style="font-size:12px; font-weight:700; color:#94A3B8; min-width:44px; text-align:right;">
                    ${{ parseFloat(item.total_price).toFixed(2) }}
                  </div>
                </div>

              </div>

              <!-- Status badge row -->
              <div style="display:flex; align-items:center; justify-content:space-between; margin-top:7px;">
                <span
                  style="font-size:9px; font-weight:700; text-transform:uppercase;
                         letter-spacing:0.06em; padding:2px 8px; border-radius:4px;"
                  :style="{ background: statusBg(item.status), color: statusColor(item.status) }"
                >{{ statusLabel(item.status) }}</span>

                <!-- Advance status button (kitchen staff action) -->
                <button
                  v-if="item.status === 'preparing' || item.status === 'ready'"
                  @click="advanceStatus(item)"
                  style="font-size:10px; padding:2px 8px; border-radius:4px; border:none;
                         cursor:pointer; font-weight:600; transition:all 0.15s;"
                  :style="{
                    background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                    color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                  }"
                >
                  {{ item.status === 'preparing' ? '✓ Mark Ready' : '✓ Served' }}
                </button>
              </div>
            </div>
          </div>

          <!-- ── UNSENT ITEMS (new, not yet sent) ── -->
          <div v-if="unsentItems.length > 0">

            <!-- New items divider -->
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#F59E0B; white-space:nowrap;">
                ✦ New Items
              </div>
              <div style="flex:1; height:1px; background:rgba(245,158,11,0.3);"></div>
              <div style="font-size:10px; color:#F59E0B; white-space:nowrap;">
                Not sent yet
              </div>
            </div>

            <!-- Unsent item cards -->
            <div
              v-for="item in unsentItems"
              :key="item.id"
              style="border-radius:8px; padding:10px 12px; margin-bottom:5px;
                     background:#1A1E28; border:1.5px solid rgba(245,158,11,0.25);
                     border-left:3px solid #F59E0B;"
            >
              <!-- Name + price row -->
              <div style="display:flex; align-items:start; justify-content:space-between; gap:8px;">
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; font-weight:600; color:#F1F5F9; line-height:1.3;">
                    {{ item.item_name }}
                  </div>
                  <div v-if="item.notes" style="font-size:10px; color:#64748B; margin-top:2px;">
                    📝 {{ item.notes }}
                  </div>
                </div>
                <div style="font-size:13px; font-weight:700; color:#F59E0B; flex-shrink:0;">
                  ${{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>

              <!-- Controls row -->
              <div style="display:flex; align-items:center; gap:6px; margin-top:9px;">

                <!-- Qty controls -->
                <button @click="decreaseQty(item)"
                  style="width:26px; height:26px; border-radius:6px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:16px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;
                         line-height:1; transition:all 0.1s;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >−</button>

                <span style="font-size:13px; font-weight:700; min-width:22px;
                             text-align:center; color:#F1F5F9;">{{ item.quantity }}</span>

                <button @click="increaseQty(item)"
                  style="width:26px; height:26px; border-radius:6px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:16px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;
                         line-height:1; transition:all 0.1s;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >+</button>

                <!-- Notes -->
                <button @click="openNotes(item)"
                  style="padding:4px 8px; border-radius:5px; border:1px solid #252B38;
                         background:transparent; color:#64748B; font-size:11px; cursor:pointer;
                         transition:all 0.15s;"
                  @mouseenter="e => e.currentTarget.style.color='#F1F5F9'"
                  @mouseleave="e => e.currentTarget.style.color='#64748B'"
                >📝 Note</button>

                <!-- Void -->
                <button @click="voidOrderItem(item)"
                  style="margin-left:auto; background:none; border:none; color:#EF4444;
                         font-size:11px; cursor:pointer; padding:4px 6px;"
                  @mouseenter="e => e.currentTarget.style.color='#F87171'"
                  @mouseleave="e => e.currentTarget.style.color='#EF4444'"
                >✕</button>
              </div>
            </div>

            <!-- Send to Kitchen button -->
            <button
              @click="sendKOT"
              style="width:100%; margin-top:6px; padding:12px; border-radius:9px;
                     background:rgba(59,130,246,0.12); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.35); font-size:13px;
                     font-weight:700; cursor:pointer; transition:all 0.15s;
                     display:flex; align-items:center; justify-content:center; gap:6px;"
              @mouseenter="e => e.currentTarget.style.background='rgba(59,130,246,0.22)'"
              @mouseleave="e => e.currentTarget.style.background='rgba(59,130,246,0.12)'"
            >
              📋 Send {{ unsentItems.length }} item(s) to Kitchen
            </button>
          </div>

        </template>
      </div>

      <!-- ── Totals ── -->
      <div v-if="orderItems.length > 0"
        style="padding:12px 16px; border-top:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; justify-content:space-between;
                    font-size:12px; color:#64748B; margin-bottom:4px;">
          <span>Subtotal</span>
          <span>${{ currentOrder?.subtotal ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;
                    font-size:12px; color:#64748B; margin-bottom:4px;">
          <span>Service Charge (10%)</span>
          <span>${{ currentOrder?.tax_amount ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700;
                    font-size:15px; border-top:1px solid #252B38;
                    padding-top:10px; margin-top:6px;">
          <span style="color:#F1F5F9;">Total</span>
          <span style="color:#F59E0B;">${{ currentOrder?.total ?? '0.00' }}</span>
        </div>
      </div>

      <!-- ── Action Buttons ── -->
      <div style="padding:12px; flex-shrink:0; display:flex; flex-direction:column; gap:8px;">

        <!-- Unsent warning -->
        <div v-if="unsentItems.length > 0 && sentItems.length > 0"
          style="font-size:11px; color:#F59E0B; text-align:center; padding:6px 10px;
                 background:rgba(245,158,11,0.08); border-radius:6px;
                 border:1px solid rgba(245,158,11,0.2);">
          ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
        </div>

        <button
          @click="showPayment = true"
          :disabled="orderItems.length === 0"
          style="width:100%; padding:13px; border-radius:9px; font-size:14px;
                 font-weight:700; background:#F59E0B; color:#000; border:none;
                 cursor:pointer; transition:all 0.15s;"
          :style="{ opacity: orderItems.length > 0 ? '1' : '0.4',
                    cursor:  orderItems.length > 0 ? 'pointer' : 'not-allowed' }"
          @mouseenter="e => { if(orderItems.length > 0) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          💳 Charge ${{ currentOrder?.total ?? '0.00' }}
        </button>
      </div>
    </div>

    <!-- ── Payment Modal ── -->
    <Teleport to="body">
      <div v-if="showPayment"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;">
        <PaymentModal
          :order="currentOrder"
          @paid="onPaid"
          @cancel="showPayment = false"
        />
      </div>
    </Teleport>

    <!-- ── Notes Modal ── -->
    <Teleport to="body">
      <div v-if="notesItem"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:60;"
        @click.self="notesItem = null"
      >
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px;
                    padding:22px; width:300px;">
          <div style="font-size:14px; font-weight:700; color:#F1F5F9; margin-bottom:14px;">
            📝 Note for {{ notesItem.item_name }}
          </div>
          <textarea
            v-model="notesText"
            rows="3"
            placeholder="e.g. No onions, well done, extra sauce..."
            style="width:100%; background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:10px 12px; color:#F1F5F9; font-size:13px; resize:none;
                   font-family:inherit; outline:none; transition:border 0.15s;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          ></textarea>
          <div style="display:flex; gap:8px; margin-top:12px;">
            <button @click="notesItem = null"
              style="flex:1; padding:10px; background:#12151C; border:1px solid #252B38;
                     border-radius:8px; color:#64748B; font-size:13px; cursor:pointer;">
              Cancel
            </button>
            <button @click="saveNotes"
              style="flex:2; padding:10px; background:#F59E0B; border:none;
                     border-radius:8px; color:#000; font-size:13px;
                     font-weight:700; cursor:pointer;">
              Save Note
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Toast ── -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; bottom:24px; right:24px; z-index:100;
               padding:12px 20px; border-radius:9px; font-weight:600;
               font-size:13px; display:flex; align-items:center; gap:8px;
               transition:all 0.3s;"
        :style="{
          background: toast.type === 'success' ? '#10B981' : '#EF4444',
          color: '#fff',
        }"
      >
        {{ toast.type === 'success' ? '✅' : '⚠️' }} {{ toast.message }}
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter }                   from 'vue-router'
import { useOrderStore }                         from '@/stores/orders'
import { useMenuStore }                          from '@/stores/menu'
import axios                                     from 'axios'
import PaymentModal                              from '@/components/PaymentModal.vue'

const route      = useRoute()
const router     = useRouter()
const orderStore = useOrderStore()
const menuStore  = useMenuStore()

// ── State ─────────────────────────────────────────────────
const table        = ref(null)
const loading      = ref(false)
const loadingTable = ref(true)
const showPayment  = ref(false)
const notesItem    = ref(null)
const notesText    = ref('')
const toast        = ref({ show: false, message: '', type: 'success' })

// ── Computed ──────────────────────────────────────────────
const currentOrder = computed(() => orderStore.currentOrder)
const currentItems = computed(() => menuStore.getItemsByCategory(menuStore.activeCategory))

// ✅ Filter voided items — handles boolean, integer, string
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
  orderItems.value.filter(i => !!i.kot_round)
)

const sentRounds = computed(() => {
  const groups = {}
  sentItems.value.forEach(item => {
    const r = item.kot_round
    if (!groups[r]) {
      groups[r] = {
        number:   r,
        sentTime: item.kot_sent_at
          ? new Date(item.kot_sent_at).toLocaleTimeString([], {
              hour: '2-digit', minute: '2-digit'
            })
          : '',
        items: [],
      }
    }
    groups[r].items.push(item)
  })
  return Object.values(groups).sort((a, b) => a.number - b.number)
})

const currentRound = computed(() => {
  if (sentItems.value.length === 0) return 1
  const max = Math.max(...sentItems.value.map(i => i.kot_round))
  return unsentItems.value.length > 0 ? max + 1 : max
})

const totalItemCount = computed(() =>
  orderItems.value.reduce((s, i) => s + i.quantity, 0)
)

const tableStatusStyle = computed(() => {
  const map = {
    free:     { background: 'rgba(16,185,129,0.12)', color: '#10B981' },
    occupied: { background: 'rgba(59,130,246,0.12)', color: '#3B82F6' },
    reserved: { background: 'rgba(139,92,246,0.12)', color: '#8B5CF6' },
    cleaning: { background: 'rgba(245,158,11,0.12)', color: '#F59E0B' },
  }
  return map[table.value?.status] ?? map.occupied
})

// ── Status helpers ────────────────────────────────────────
function statusColor(status) {
  return { pending:'#F59E0B', preparing:'#3B82F6', ready:'#10B981', served:'#64748B' }[status] ?? '#64748B'
}
function statusBg(status) {
  return {
    pending:   'rgba(245,158,11,0.12)',
    preparing: 'rgba(59,130,246,0.12)',
    ready:     'rgba(16,185,129,0.12)',
    served:    'rgba(100,116,139,0.12)',
  }[status] ?? 'rgba(100,116,139,0.12)'
}
function statusLabel(status) {
  return { pending:'🟡 Pending', preparing:'🔵 Preparing', ready:'🟢 Ready', served:'⚫ Served' }[status] ?? status
}

// ── Cart actions ──────────────────────────────────────────
async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return
  try {
    // Only merge with unsent items (no kot_round)
    const existing = orderItems.value.find(
      i => i.menu_item_id === menuItem.id && !i.kot_round
    )
    if (existing) {
      await orderStore.updateItemQty(currentOrder.value.id, existing.id, existing.quantity + 1)
    } else {
      await orderStore.addItem(currentOrder.value.id, {
        menu_item_id: menuItem.id,
        quantity:     1,
      })
    }
  } catch (e) {
    showToast('Failed to add: ' + (e.response?.data?.message ?? e.message), 'error')
  }
}

async function increaseQty(item) {
  try {
    await orderStore.updateItemQty(currentOrder.value.id, item.id, item.quantity + 1)
  } catch (e) {
    showToast('Failed to update quantity', 'error')
  }
}

async function decreaseQty(item) {
  try {
    if (item.quantity <= 1) {
      await orderStore.voidItem(currentOrder.value.id, item.id)
    } else {
      await orderStore.updateItemQty(currentOrder.value.id, item.id, item.quantity - 1)
    }
  } catch (e) {
    showToast('Failed to update quantity', 'error')
  }
}

async function voidOrderItem(item) {
  if (item.kot_round) {
    if (!confirm(`"${item.item_name}" was already sent to kitchen. Remove anyway?`)) return
  }
  try {
    await orderStore.voidItem(currentOrder.value.id, item.id)
    showToast('Item removed', 'success')
  } catch (e) {
    showToast('Failed to remove item', 'error')
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
    showToast(e.response?.data?.message ?? 'Failed to send KOT', 'error')
  }
}

async function advanceStatus(item) {
  const next = { preparing: 'ready', ready: 'served' }[item.status]
  if (!next) return
  try {
    await axios.patch(
      `/orders/${currentOrder.value.id}/items/${item.id}`,
      { status: next }
    )
    await orderStore.fetchOrder(currentOrder.value.id)
    showToast(`Marked as ${next}`, 'success')
  } catch (e) {
    showToast('Failed to update status', 'error')
  }
}

async function refreshOrder() {
  if (currentOrder.value?.id) {
    await orderStore.fetchOrder(currentOrder.value.id)
    showToast('Refreshed ✓', 'success')
  }
}

// ── Notes ─────────────────────────────────────────────────
function openNotes(item) {
  notesItem.value = item
  notesText.value = item.notes ?? ''
}

async function saveNotes() {
  if (!notesItem.value) return
  try {
    await axios.patch(
      `/orders/${currentOrder.value.id}/items/${notesItem.value.id}`,
      { notes: notesText.value }
    )
    await orderStore.fetchOrder(currentOrder.value.id)
    notesItem.value = null
    showToast('Note saved', 'success')
  } catch (e) {
    showToast('Failed to save note', 'error')
  }
}

// ── Payment ───────────────────────────────────────────────
async function onPaid() {
  showPayment.value = false
  orderStore.clearOrder()
  router.push('/')
}

// ── Toast ─────────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  const tableId = parseInt(route.params.tableId)
  loading.value      = true
  loadingTable.value = true

  try {
    const [tableRes] = await Promise.all([
      axios.get(`/tables/${tableId}`),
      menuStore.fetchMenu(),
    ])

    table.value        = tableRes.data
    loadingTable.value = false

    const orderId = tableRes.data.current_order_id

    console.log('=== POS MOUNT ===')
    console.log('Table:', tableRes.data.name, '| current_order_id:', orderId)

    if (orderId) {
      // ✅ Always fetch from API — never use stale store data
      // This is what makes items survive page refresh
      await orderStore.fetchOrder(orderId)
    } else {
      // ✅ Table has no order — clear any stale data and create fresh
      orderStore.clearOrder()
      await orderStore.createOrder({
        table_id: tableId,
        type:     'dine_in',
      })
    }

    console.log('Final items:', orderStore.currentOrder?.items?.length ?? 0)

  } catch (e) {
    loadingTable.value = false
    showToast('Failed to load: ' + (e.response?.data?.message ?? e.message), 'error')
    console.error('POS mount error:', e)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  // intentionally empty — keep order alive until payment
})
</script>