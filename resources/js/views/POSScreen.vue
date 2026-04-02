<template>
  <div class="pos-root">

    <!-- ── TOP BAR ── -->
    <div class="top-bar">

      <button class="back-btn" @click="$router.push('/')">← Back</button>

      <div class="top-center">
        <div class="top-table-name">{{ table ? table.name : 'Loading...' }}</div>
        <div class="top-order-num">{{ currentOrder?.order_number ?? '...' }}</div>
      </div>

      <div class="top-right">
        <div class="table-status-badge" :style="tableStatusStyle">
          {{ table?.status ?? '...' }}
        </div>

        <!-- Mobile cart toggle -->
        <button
          class="mobile-only cart-toggle-btn"
          @click="mobileView = mobileView === 'cart' ? 'menu' : 'cart'"
        >
          {{ mobileView === 'cart' ? '🍽️' : '🧾' }}
          <span v-if="unsentItems.length > 0 && mobileView !== 'cart'" class="cart-badge">
            {{ unsentItems.length }}
          </span>
        </button>
      </div>
    </div>

    <!-- ── BODY ── -->
    <div class="pos-body">

      <!-- ══ LEFT: Menu Panel ══ -->
      <div class="menu-panel" :class="{ 'panel-hidden': mobileView === 'cart' }">

        <!-- Category Bar -->
        <div class="category-bar">
          <button
            v-for="cat in menuStore.categories"
            :key="cat.id"
            class="cat-btn"
            :class="{ active: menuStore.activeCategory === cat.id }"
            @click="menuStore.setActiveCategory(cat.id)"
          >{{ cat.name }}</button>
        </div>

        <!-- Menu Items Grid -->
        <div class="menu-scroll">
          <div v-if="loading || menuStore.loading" class="loading-state">
            <span style="font-size:36px; opacity:0.3;">⏳</span>
            <span>Loading menu...</span>
          </div>

          <div v-else class="menu-grid">
            <button
              v-for="item in currentItems"
              :key="item.id"
              class="menu-item-card"
              :class="{ unavailable: !item.is_available }"
              :disabled="!item.is_available"
              @click="addItem(item)"
              @touchstart="e => { if(item.is_available) e.currentTarget.style.opacity='0.7' }"
              @touchend="e => { e.currentTarget.style.opacity='1' }"
              @touchcancel="e => { e.currentTarget.style.opacity='1' }"
            >
              <!-- Image -->
              <div
                v-if="item.image"
                class="menu-item-img"
                :style="{ backgroundImage: 'url(/storage/menu_items/' + item.image + ')' }"
              />
              <div v-else class="menu-item-img menu-item-placeholder">🍽️</div>

              <!-- Cart badge -->
              <span v-if="itemCartCount(item) > 0" class="item-badge">
                {{ itemCartCount(item) }}
              </span>

              <div class="item-name">{{ item.name }}</div>
              <div class="item-price">Rs. {{ item.price }}</div>
              <div v-if="item.is_popular" class="item-popular">Popular</div>
            </button>
          </div>
        </div>
      </div><!-- end menu-panel -->

      <!-- ══ RIGHT: Cart Panel ══ -->
      <!-- Desktop: normal sidebar, Mobile: teleported to body -->
      <div class="cart-panel desktop-cart">
        <!-- Cart Header (Desktop) -->
        <div class="cart-header">
          <div class="cart-header-info">
            <div class="cart-title">🧾 {{ table?.name ?? 'Order' }}</div>
            <div class="cart-subtitle">
              {{ totalItemCount }} item(s) ·
              <span class="round-label">Round {{ currentRound }}</span>
            </div>
          </div>
          <button class="refresh-btn" @click="refreshOrder">🔄</button>
        </div>

        <!-- Cart Items — scrollable -->
        <div class="cart-scroll">

          <div v-if="loading" class="cart-empty">
            <span>Loading order...</span>
          </div>

          <div v-else-if="orderItems.length === 0" class="cart-empty">
            <span style="font-size:40px; opacity:0.2;">🛒</span>
            <span>Tap menu items to add</span>
          </div>

          <template v-else>

            <!-- SENT ROUNDS -->
            <div v-for="round in sentRounds" :key="'r' + round.number" class="round-group">
              <div class="round-header">
                <span class="round-label-sm">Round {{ round.number }}</span>
                <div class="round-line" />
                <span class="round-time">{{ round.sentTime }}</span>
              </div>

              <div
                v-for="item in round.items"
                :key="item.id"
                class="cart-item"
                :style="{ borderLeftColor: statusColor(item.status) }"
              >
                <div class="cart-item-top">
                  <div class="cart-item-left">
                    <div class="status-dot" :style="{ background: statusColor(item.status) }" />
                    <div class="cart-item-info">
                      <div class="cart-item-name">{{ item.item_name }}</div>
                      <div v-if="item.modifiers?.length" class="mod-list">
                        <span v-for="mod in item.modifiers" :key="mod.id" class="mod-tag">
                          {{ mod.name }}<span v-if="mod.price > 0" class="mod-price"> +Rs.{{ parseFloat(mod.price).toFixed(0) }}</span>
                        </span>
                      </div>
                      <div v-if="item.notes" class="item-notes">📝 {{ item.notes }}</div>
                    </div>
                  </div>
                  <div class="cart-item-right">
                    <div class="qty-badge" :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                      ×{{ item.quantity }}
                    </div>
                    <div class="item-total">Rs.{{ parseFloat(item.total_price).toFixed(0) }}</div>
                  </div>
                </div>

                <div class="cart-item-footer">
                  <span class="status-pill" :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                    {{ statusLabel(item.status) }}
                  </span>
                  <button
                    v-if="item.status === 'preparing' || item.status === 'ready'"
                    class="advance-btn"
                    :style="{
                      background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                      color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                    }"
                    @click="advanceStatus(item)"
                  >{{ item.status === 'preparing' ? '✓ Ready' : '✓ Served' }}</button>
                </div>
              </div>
            </div>

            <!-- UNSENT ITEMS -->
            <div v-if="unsentItems.length > 0" class="unsent-section">
              <div class="unsent-header">
                <span class="unsent-label">✦ New Items</span>
                <div class="unsent-line" />
                <span class="unsent-tag">Not sent</span>
              </div>

              <div v-for="item in unsentItems" :key="item.id" class="unsent-item">
                <div class="unsent-top">
                  <div class="unsent-name">{{ item.item_name }}</div>
                  <div class="unsent-price">Rs.{{ parseFloat(item.total_price).toFixed(0) }}</div>
                </div>
                <div v-if="item.notes" class="item-notes">📝 {{ item.notes }}</div>

                <div class="unsent-controls">
                  <button class="qty-btn" @click="decreaseQty(item)">−</button>
                  <span class="qty-count">{{ item.quantity }}</span>
                  <button class="qty-btn" @click="increaseQty(item)">+</button>
                  <button class="note-btn" @click="openNotes(item)">📝</button>
                  <button class="void-btn" @click="voidOrderItem(item)">✕</button>
                </div>
              </div>

              <button class="send-kot-btn" @click="sendKOT">
                📋 Send {{ unsentItems.length }} item(s) to Kitchen
              </button>
            </div>
          </template>
        </div>

        <!-- ── Totals ── -->
        <div v-if="orderItems.length > 0" class="cart-totals">
          <div class="total-row">
            <span>Subtotal</span>
            <span>Rs. {{ currentOrder?.subtotal ?? '0.00' }}</span>
          </div>
          <div class="total-row">
            <span>Service (10%)</span>
            <span>Rs. {{ currentOrder?.tax_amount ?? '0.00' }}</span>
          </div>
          <div class="total-row total-row--grand">
            <span>Total</span>
            <span class="grand-total">Rs. {{ currentOrder?.total ?? '0.00' }}</span>
          </div>
        </div>

        <!-- ── Action buttons ── -->
        <div class="cart-actions">
          <div v-if="unsentItems.length > 0 && sentItems.length > 0" class="unsent-warning">
            ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
          </div>
          <button
            class="charge-btn"
            :class="{ disabled: orderItems.length === 0 }"
            :disabled="orderItems.length === 0"
            @click="showPayment = true"
          >
            💳 Charge Rs. {{ currentOrder?.total ?? '0.00' }}
          </button>
        </div>

      </div>

      <!-- Mobile: teleported to body to avoid containing block clipping -->
      <Teleport to="body">
        <div class="cart-panel mobile-cart" :class="{ 'cart-open': mobileView === 'cart' }">

        <!-- Cart Header -->
        <div class="cart-header">
          <div class="cart-header-left">
            <button class="cart-back-btn" @click="mobileView = 'menu'">←</button>
            <div class="cart-header-info">
              <div class="cart-title">🧾 {{ table?.name ?? 'Order' }}</div>
              <div class="cart-subtitle">
                {{ totalItemCount }} item(s) ·
                <span class="round-label">Round {{ currentRound }}</span>
              </div>
            </div>
          </div>
          <button class="refresh-btn" @click="refreshOrder">🔄</button>
        </div>

        <!-- Cart Items — scrollable -->
        <div class="cart-scroll">

          <div v-if="loading" class="cart-empty">
            <span>Loading order...</span>
          </div>

          <div v-else-if="orderItems.length === 0" class="cart-empty">
            <span style="font-size:40px; opacity:0.2;">🛒</span>
            <span>Tap menu items to add</span>
          </div>

          <template v-else>

            <!-- SENT ROUNDS -->
            <div v-for="round in sentRounds" :key="'r' + round.number" class="round-group">
              <div class="round-header">
                <span class="round-label-sm">Round {{ round.number }}</span>
                <div class="round-line" />
                <span class="round-time">{{ round.sentTime }}</span>
              </div>

              <div
                v-for="item in round.items"
                :key="item.id"
                class="cart-item"
                :style="{ borderLeftColor: statusColor(item.status) }"
              >
                <div class="cart-item-top">
                  <div class="cart-item-left">
                    <div class="status-dot" :style="{ background: statusColor(item.status) }" />
                    <div class="cart-item-info">
                      <div class="cart-item-name">{{ item.item_name }}</div>
                      <div v-if="item.modifiers?.length" class="mod-list">
                        <span v-for="mod in item.modifiers" :key="mod.id" class="mod-tag">
                          {{ mod.name }}<span v-if="mod.price > 0" class="mod-price"> +Rs.{{ parseFloat(mod.price).toFixed(0) }}</span>
                        </span>
                      </div>
                      <div v-if="item.notes" class="item-notes">📝 {{ item.notes }}</div>
                    </div>
                  </div>
                  <div class="cart-item-right">
                    <div class="qty-badge" :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                      ×{{ item.quantity }}
                    </div>
                    <div class="item-total">Rs.{{ parseFloat(item.total_price).toFixed(0) }}</div>
                  </div>
                </div>

                <div class="cart-item-footer">
                  <span class="status-pill" :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                    {{ statusLabel(item.status) }}
                  </span>
                  <button
                    v-if="item.status === 'preparing' || item.status === 'ready'"
                    class="advance-btn"
                    :style="{
                      background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                      color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                    }"
                    @click="advanceStatus(item)"
                  >{{ item.status === 'preparing' ? '✓ Ready' : '✓ Served' }}</button>
                </div>
              </div>
            </div>

            <!-- UNSENT ITEMS -->
            <div v-if="unsentItems.length > 0" class="unsent-section">
              <div class="unsent-header">
                <span class="unsent-label">✦ New Items</span>
                <div class="unsent-line" />
                <span class="unsent-tag">Not sent</span>
              </div>

              <div v-for="item in unsentItems" :key="item.id" class="unsent-item">
                <div class="unsent-top">
                  <div class="unsent-name">{{ item.item_name }}</div>
                  <div class="unsent-price">Rs.{{ parseFloat(item.total_price).toFixed(0) }}</div>
                </div>
                <div v-if="item.notes" class="item-notes">📝 {{ item.notes }}</div>

                <div class="unsent-controls">
                  <button class="qty-btn" @click="decreaseQty(item)">−</button>
                  <span class="qty-count">{{ item.quantity }}</span>
                  <button class="qty-btn" @click="increaseQty(item)">+</button>
                  <button class="note-btn" @click="openNotes(item)">📝</button>
                  <button class="void-btn" @click="voidOrderItem(item)">✕</button>
                </div>
              </div>

              <!-- Send to Kitchen -->
              <button class="send-kot-btn" @click="sendKOT">
                📋 Send {{ unsentItems.length }} item(s) to Kitchen
              </button>
            </div>

          </template>
        </div>

        <!-- ── Totals ── -->
        <div v-if="orderItems.length > 0" class="cart-totals">
          <div class="total-row">
            <span>Subtotal</span>
            <span>Rs. {{ currentOrder?.subtotal ?? '0.00' }}</span>
          </div>
          <div class="total-row">
            <span>Service (10%)</span>
            <span>Rs. {{ currentOrder?.tax_amount ?? '0.00' }}</span>
          </div>
          <div class="total-row total-row--grand">
            <span>Total</span>
            <span class="grand-total">Rs. {{ currentOrder?.total ?? '0.00' }}</span>
          </div>
        </div>

        <!-- ── Action Buttons ── -->
        <div class="cart-actions">
          <div v-if="unsentItems.length > 0 && sentItems.length > 0" class="unsent-warning">
            ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
          </div>
          <button
            class="charge-btn"
            :class="{ disabled: orderItems.length === 0 }"
            :disabled="orderItems.length === 0"
            @click="showPayment = true"
          >
            💳 Charge Rs. {{ currentOrder?.total ?? '0.00' }}
          </button>
        </div>

      </div><!-- end cart-panel -->
      </Teleport>
    </div><!-- end pos-body -->

    <!-- ══ MODALS ══ -->

    <Teleport to="body">
      <div v-if="showPayment" class="modal-overlay">
        <PaymentModal :order="currentOrder" @paid="onPaid" @cancel="showPayment = false" />
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="notesItem" class="modal-overlay modal-overlay--bottom" @click.self="notesItem = null">
        <div class="notes-sheet">
          <div class="sheet-handle" />
          <div class="notes-title">📝 Note for {{ notesItem.item_name }}</div>
          <textarea
            v-model="notesText"
            rows="3"
            class="notes-textarea"
            placeholder="e.g. No onions, well done, extra sauce..."
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          />
          <div class="notes-actions">
            <button class="notes-cancel" @click="notesItem = null">Cancel</button>
            <button class="notes-save" @click="saveNotes">Save Note</button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="toast.show" class="toast" :class="toast.type === 'success' ? 'toast--success' : 'toast--error'">
        {{ toast.type === 'success' ? '✅' : '⚠️' }} {{ toast.message }}
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="modifierItem" class="modal-overlay modal-overlay--bottom" @click.self="modifierItem = null">
        <div class="modifier-sheet">
          <div class="sheet-handle-wrap">
            <div class="sheet-handle" />
          </div>
          <div class="modifier-scroll">
            <ModifierSelector
              :item="modifierItem"
              @confirm="onModifierConfirm"
              @cancel="modifierItem = null"
              :inline="true"
            />
          </div>
        </div>
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
import ModifierSelector                          from '@/components/ModifierSelector.vue'

const route      = useRoute()
const router     = useRouter()
const orderStore = useOrderStore()
const menuStore  = useMenuStore()

const table        = ref(null)
const loading      = ref(false)
const loadingTable = ref(true)
const showPayment  = ref(false)
const notesItem    = ref(null)
const notesText    = ref('')
const toast        = ref({ show: false, message: '', type: 'success' })
const modifierItem = ref(null)
const mobileView   = ref('menu')

const currentOrder = computed(() => orderStore.currentOrder)
const currentItems = computed(() => menuStore.getItemsByCategory(menuStore.activeCategory))

const orderItems = computed(() => {
  const items = currentOrder.value?.items ?? []
  return items.filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })
})

const unsentItems = computed(() => orderItems.value.filter(i => !i.kot_round))
const sentItems   = computed(() => orderItems.value.filter(i => !!i.kot_round))

const sentRounds = computed(() => {
  const groups = {}
  sentItems.value.forEach(item => {
    const r = item.kot_round
    if (!groups[r]) {
      groups[r] = {
        number:   r,
        sentTime: item.kot_sent_at
          ? new Date(item.kot_sent_at).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })
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

const totalItemCount = computed(() => orderItems.value.reduce((s, i) => s + i.quantity, 0))

const tableStatusStyle = computed(() => {
  const map = {
    free:     { background:'rgba(16,185,129,0.12)', color:'#10B981' },
    occupied: { background:'rgba(59,130,246,0.12)', color:'#3B82F6' },
    reserved: { background:'rgba(139,92,246,0.12)', color:'#8B5CF6' },
    cleaning: { background:'rgba(245,158,11,0.12)', color:'#F59E0B' },
  }
  return map[table.value?.status] ?? map.occupied
})

function itemCartCount(menuItem) {
  return orderItems.value
    .filter(i => i.menu_item_id === menuItem.id && !i.kot_round)
    .reduce((s, i) => s + i.quantity, 0)
}

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

async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return
  if (menuItem.modifier_groups?.length > 0) { modifierItem.value = menuItem; return }
  try {
    const existing = orderItems.value.find(i => i.menu_item_id === menuItem.id && !i.kot_round)
    if (existing) {
      await orderStore.updateItemQty(currentOrder.value.id, existing.id, existing.quantity + 1)
    } else {
      await orderStore.addItem(currentOrder.value.id, { menu_item_id: menuItem.id, quantity: 1, is_instant: menuItem.is_instant })
    }
  } catch (e) {
    showToast('Failed to add: ' + (e.response?.data?.message ?? e.message), 'error')
  }
}

async function onModifierConfirm(payload) {
  modifierItem.value = null
  try {
    await orderStore.addItem(currentOrder.value.id, {
      menu_item_id: payload.menu_item_id, quantity: payload.quantity,
      selected_modifiers: payload.selected_modifiers, notes: payload.notes, is_instant: payload.is_instant,
    })
    showToast('Added to cart ✓', 'success')
  } catch (e) {
    showToast('Failed to add: ' + (e.response?.data?.message ?? e.message), 'error')
  }
}

async function increaseQty(item) {
  try { await orderStore.updateItemQty(currentOrder.value.id, item.id, item.quantity + 1) }
  catch { showToast('Failed to update quantity', 'error') }
}

async function decreaseQty(item) {
  try {
    if (item.quantity <= 1) await orderStore.voidItem(currentOrder.value.id, item.id)
    else await orderStore.updateItemQty(currentOrder.value.id, item.id, item.quantity - 1)
  } catch { showToast('Failed to update quantity', 'error') }
}

async function voidOrderItem(item) {
  if (item.kot_round && !confirm(`"${item.item_name}" was already sent to kitchen. Remove anyway?`)) return
  try { await orderStore.voidItem(currentOrder.value.id, item.id); showToast('Item removed', 'success') }
  catch { showToast('Failed to remove item', 'error') }
}

async function sendKOT() {
  if (unsentItems.value.length === 0) { showToast('No new items to send', 'error'); return }
  try {
    const res = await orderStore.sendKOT(currentOrder.value.id)
    showToast(`Round ${res.round}: ${res.items_sent} item(s) sent to kitchen! 🍳`, 'success')
  } catch (e) { showToast(e.response?.data?.message ?? 'Failed to send KOT', 'error') }
}

async function advanceStatus(item) {
  const next = { preparing:'ready', ready:'served' }[item.status]
  if (!next) return
  try {
    await axios.patch(`/orders/${currentOrder.value.id}/items/${item.id}`, { status: next })
    await orderStore.fetchOrder(currentOrder.value.id)
    showToast(`Marked as ${next}`, 'success')
  } catch { showToast('Failed to update status', 'error') }
}

async function refreshOrder() {
  if (currentOrder.value?.id) {
    await orderStore.fetchOrder(currentOrder.value.id)
    showToast('Refreshed ✓', 'success')
  }
}

function openNotes(item) { notesItem.value = item; notesText.value = item.notes ?? '' }

async function saveNotes() {
  if (!notesItem.value) return
  try {
    await axios.patch(`/orders/${currentOrder.value.id}/items/${notesItem.value.id}`, { notes: notesText.value })
    await orderStore.fetchOrder(currentOrder.value.id)
    notesItem.value = null
    showToast('Note saved', 'success')
  } catch { showToast('Failed to save note', 'error') }
}

async function onPaid() {
  showPayment.value = false
  orderStore.clearOrder()
  router.push('/')
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

onMounted(async () => {
  const tableId = parseInt(route.params.tableId)
  loading.value = true; loadingTable.value = true
  try {
    const [tableRes] = await Promise.all([axios.get(`/tables/${tableId}`), menuStore.fetchMenu()])
    table.value = tableRes.data; loadingTable.value = false
    const orderId = tableRes.data.current_order_id
    if (orderId) await orderStore.fetchOrder(orderId)
    else { orderStore.clearOrder(); await orderStore.createOrder({ table_id: tableId, type: 'dine_in' }) }
  } catch (e) {
    loadingTable.value = false
    showToast('Failed to load: ' + (e.response?.data?.message ?? e.message), 'error')
  } finally { loading.value = false }
})
</script>

<style scoped>
/* ── iOS input zoom prevention ── */
input, textarea, select { font-size: 16px !important; }

/* ── Hide scrollbars ── */
div::-webkit-scrollbar { display: none; }

/* ════════════════════════════════════════
   ROOT LAYOUT
   ════════════════════════════════════════ */
.pos-root {
  display: flex;
  flex-direction: column;
  height: 100%;
  height: 100dvh;
  background: #0A0C10;
  overflow: hidden;
}

/* ── Top Bar ── */
.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-bottom: 1px solid #252B38;
  background: #12151C;
  flex-shrink: 0;
  gap: 8px;
  /* capture its rendered height via a CSS variable for mobile cart offset */
  --topbar-h: 61px;
}

.back-btn {
  font-size: 13px; color: #64748B;
  background: #1A1E28; border: 1px solid #252B38;
  cursor: pointer; padding: 8px 12px; border-radius: 8px;
  min-height: 40px; white-space: nowrap;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
}

.top-center {
  text-align: center; min-width: 0; flex: 1;
}
.top-table-name {
  font-weight: 700; font-size: 15px; color: #F1F5F9;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.top-order-num { font-size: 11px; color: #64748B; margin-top: 1px; }

.top-right {
  display: flex; align-items: center; gap: 6px; flex-shrink: 0;
}
.table-status-badge {
  font-size: 11px; font-weight: 600; padding: 4px 10px;
  border-radius: 6px; white-space: nowrap;
}

.cart-toggle-btn {
  position: relative;
  padding: 8px 10px; background: #1A1E28; border: 1px solid #252B38;
  border-radius: 8px; color: #F1F5F9; font-size: 16px; cursor: pointer;
  min-height: 40px; min-width: 40px;
  display: flex; align-items: center; justify-content: center;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
}
.cart-badge {
  position: absolute; top: -5px; right: -5px;
  background: #F59E0B; color: #000; border-radius: 50%;
  width: 18px; height: 18px; font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  pointer-events: none;
}

/* ── Body (flex row) ── */
.pos-body {
  flex: 1;
  display: flex;
  overflow: hidden;
  min-height: 0;
  position: relative;
}

/* ════════════════════════════════════════
   MENU PANEL (left / full width on mobile)
   ════════════════════════════════════════ */
.menu-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #252B38;
  overflow: hidden;
  min-width: 0;
}

.category-bar {
  display: flex; gap: 6px; padding: 8px 12px;
  border-bottom: 1px solid #252B38; overflow-x: auto;
  flex-shrink: 0; -webkit-overflow-scrolling: touch; scrollbar-width: none;
}
.cat-btn {
  padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
  border: 1px solid #252B38; white-space: nowrap; flex-shrink: 0; cursor: pointer;
  min-height: 34px; background: transparent; color: #64748B;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
  transition: all 0.15s;
}
.cat-btn.active { background: #F59E0B; color: #000; border-color: #F59E0B; }

.menu-scroll {
  flex: 1; overflow-y: auto; padding: 8px;
  -webkit-overflow-scrolling: touch;
}

.loading-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; height: 100%; color: #64748B; gap: 8px;
  font-size: 13px;
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 7px;
}

.menu-item-card {
  background: #1A1E28; border: 1px solid #252B38; border-radius: 10px;
  padding: 8px 6px; text-align: center; cursor: pointer;
  display: flex; flex-direction: column; align-items: center;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
  transition: opacity 0.15s; width: 100%; position: relative;
}
.menu-item-card.unavailable { opacity: 0.4; cursor: not-allowed; }

.menu-item-img {
  width: 100%; aspect-ratio: 4/3; border-radius: 7px; margin-bottom: 5px;
  background-size: cover; background-position: center;
  background-repeat: no-repeat; background-color: #252B38;
}
.menu-item-placeholder {
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; color: #64748B;
}

.item-badge {
  position: absolute; top: 5px; right: 5px;
  background: #F59E0B; color: #000; border-radius: 50%;
  width: 18px; height: 18px; font-size: 9px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  pointer-events: none;
}
.item-name {
  font-size: 11px; font-weight: 600; color: #F1F5F9;
  line-height: 1.3; text-align: center; width: 100%;
}
.item-price { font-size: 12px; font-weight: 700; color: #F59E0B; margin-top: 3px; }
.item-popular {
  font-size: 8px; background: rgba(16,185,129,0.12); color: #10B981;
  border-radius: 4px; padding: 1px 6px; margin-top: 3px;
}

/* ════════════════════════════════════════
   CART PANEL (right sidebar / mobile overlay)
   ════════════════════════════════════════ */
.cart-panel {
  width: 300px;
  display: flex;
  flex-direction: column;
  background: #12151C;
  flex-shrink: 0;
  /* ⚠️ no position:absolute/fixed here — it's a normal flex child on desktop */
}

/* Cart header */
.cart-header {
  padding: 10px 12px; border-bottom: 1px solid #252B38; flex-shrink: 0;
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.cart-header-left {
  display: flex; align-items: center; gap: 8px;
  flex: 1; min-width: 0;
}
.cart-back-btn {
  background: #1A1E28; border: 1px solid #252B38; border-radius: 6px;
  padding: 6px 10px; color: #64748B; font-size: 13px; cursor: pointer;
  min-height: 32px; min-width: 36px;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
  display: flex; align-items: center; justify-content: center;
}
.cart-header-info { min-width: 0; }
.cart-title { font-weight: 700; font-size: 13px; color: #F1F5F9; }
.cart-subtitle { font-size: 11px; color: #64748B; margin-top: 1px; }
.round-label { color: #F59E0B; }
.refresh-btn {
  background: #1A1E28; border: 1px solid #252B38; border-radius: 6px;
  padding: 6px 8px; color: #64748B; font-size: 11px; cursor: pointer;
  min-height: 32px; -webkit-tap-highlight-color: transparent; flex-shrink: 0;
}

/* Cart scroll area — flex:1 so it fills between header and footer */
.cart-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 6px;
  -webkit-overflow-scrolling: touch;
}

.cart-empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; height: 100%; color: #64748B;
  gap: 10px; font-size: 13px;
}

/* Round groups */
.round-group { margin-bottom: 8px; }
.round-header {
  display: flex; align-items: center; gap: 8px; margin-bottom: 4px;
}
.round-label-sm {
  font-size: 9px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.07em; color: #64748B; white-space: nowrap;
}
.round-line { flex: 1; height: 1px; background: #252B38; }
.round-time { font-size: 9px; color: #64748B; white-space: nowrap; font-family: monospace; }

/* Cart item */
.cart-item {
  border-radius: 6px; padding: 6px 8px; margin-bottom: 3px;
  background: #1A1E28; border: 1px solid #252B38; border-left-width: 2px;
}
.cart-item-top {
  display: flex; align-items: center; justify-content: space-between; gap: 5px;
}
.cart-item-left {
  display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;
}
.status-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.cart-item-info { min-width: 0; }
.cart-item-name {
  font-size: 11px; font-weight: 500; color: #F1F5F9;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mod-list { display: flex; flex-wrap: wrap; gap: 2px; margin-top: 1px; }
.mod-tag {
  font-size: 8px; padding: 1px 3px; border-radius: 3px;
  background: rgba(100,116,139,0.12); color: #64748B;
}
.mod-price { color: #F59E0B; }
.item-notes { font-size: 9px; color: #64748B; margin-top: 1px; }

.cart-item-right {
  display: flex; align-items: center; gap: 5px; flex-shrink: 0;
}
.qty-badge {
  font-size: 10px; font-weight: 700; padding: 1px 4px;
  border-radius: 3px; min-width: 18px; text-align: center;
}
.item-total {
  font-size: 10px; font-weight: 700; color: #94A3B8;
  min-width: 38px; text-align: right;
}
.cart-item-footer {
  display: flex; align-items: center; justify-content: space-between; margin-top: 4px;
}
.status-pill {
  font-size: 8px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.06em; padding: 1px 4px; border-radius: 3px;
}
.advance-btn {
  font-size: 9px; padding: 2px 6px; border-radius: 3px; border: none;
  cursor: pointer; font-weight: 600; min-height: 22px;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
}

/* Unsent items */
.unsent-section {}
.unsent-header {
  display: flex; align-items: center; gap: 8px; margin-bottom: 4px;
}
.unsent-label {
  font-size: 9px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.07em; color: #F59E0B; white-space: nowrap;
}
.unsent-line { flex: 1; height: 1px; background: rgba(245,158,11,0.3); }
.unsent-tag { font-size: 9px; color: #F59E0B; white-space: nowrap; }

.unsent-item {
  border-radius: 6px; padding: 6px 8px; margin-bottom: 3px;
  background: #1A1E28; border: 1.5px solid rgba(245,158,11,0.25);
  border-left: 2px solid #F59E0B;
}
.unsent-top {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 5px;
}
.unsent-name { font-size: 11px; font-weight: 600; color: #F1F5F9; line-height: 1.2; flex: 1; }
.unsent-price { font-size: 12px; font-weight: 700; color: #F59E0B; flex-shrink: 0; }

.unsent-controls {
  display: flex; align-items: center; gap: 4px; margin-top: 5px;
}
.qty-btn {
  width: 28px; height: 28px; border-radius: 5px; border: 1px solid #334155;
  background: #252B38; color: #F1F5F9; font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation; flex-shrink: 0;
}
.qty-count {
  font-size: 12px; font-weight: 700; min-width: 20px;
  text-align: center; color: #F1F5F9;
}
.note-btn {
  padding: 3px 6px; border-radius: 4px; border: 1px solid #252B38;
  background: transparent; color: #64748B; font-size: 10px; cursor: pointer;
  min-height: 28px; -webkit-tap-highlight-color: transparent;
}
.void-btn {
  margin-left: auto; background: none; border: none; color: #EF4444;
  font-size: 13px; cursor: pointer; padding: 3px 5px;
  min-height: 28px; min-width: 28px;
  -webkit-tap-highlight-color: transparent;
}

.send-kot-btn {
  width: 100%; margin-top: 4px; padding: 8px; border-radius: 6px;
  background: rgba(59,130,246,0.12); color: #3B82F6;
  border: 1px solid rgba(59,130,246,0.35); font-size: 11px;
  font-weight: 700; cursor: pointer; min-height: 36px;
  display: flex; align-items: center; justify-content: center; gap: 4px;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
}

/* ── Totals — flex-shrink:0, always visible ── */
.cart-totals {
  padding: 6px 10px;
  border-top: 1px solid #252B38;
  flex-shrink: 0;
}
.total-row {
  display: flex; justify-content: space-between;
  font-size: 10px; color: #64748B; margin-bottom: 2px;
}
.total-row--grand {
  font-weight: 700; font-size: 13px;
  border-top: 1px solid #252B38; padding-top: 4px; margin-top: 2px;
  color: #F1F5F9;
}
.grand-total { color: #F59E0B; }

/* ── Action buttons ── */
.cart-actions {
  padding: 6px 8px;
  padding-bottom: calc(6px + env(safe-area-inset-bottom, 0px));
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: #12151C;
  border-top: 1px solid #252B38;
  margin-top: auto; /* Push to bottom of flex container */
  min-height: 120px; /* Ensure charge button always visible */
}
.unsent-warning {
  font-size: 9px; color: #F59E0B; text-align: center; padding: 4px 6px;
  background: rgba(245,158,11,0.08); border-radius: 4px;
  border: 1px solid rgba(245,158,11,0.2);
}
.charge-btn {
  width: 100%; padding: 10px; border-radius: 6px; font-size: 13px;
  font-weight: 700; background: #F59E0B; color: #000; border: none;
  cursor: pointer; min-height: 44px;
  -webkit-tap-highlight-color: transparent; touch-action: manipulation;
}
.charge-btn.disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Desktop: hide mobile toggle ── */
.mobile-only { display: none; }
.cart-back-btn { display: none; } /* Hide back button on desktop */

/* Hide mobile cart on desktop, show desktop cart */
.mobile-cart { display: none !important; }
.desktop-cart { display: flex; }

/* ── Modals ── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.65);
  display: flex; align-items: center; justify-content: center;
  z-index: 50; padding: 16px;
}
.modal-overlay--bottom {
  align-items: flex-end; justify-content: center; padding: 0;
}

.notes-sheet {
  background: #1A1E28; border: 1px solid #252B38;
  border-radius: 20px 20px 0 0; padding: 20px 16px; width: 100%; max-width: 480px;
  padding-bottom: calc(20px + env(safe-area-inset-bottom, 0px));
}
.sheet-handle {
  width: 36px; height: 4px; background: #252B38; border-radius: 2px; margin: 0 auto 14px;
}
.notes-title { font-size: 14px; font-weight: 700; color: #F1F5F9; margin-bottom: 10px; }
.notes-textarea {
  width: 100%; background: #12151C; border: 1px solid #252B38; border-radius: 8px;
  padding: 12px; color: #F1F5F9; resize: none; font-family: inherit; outline: none;
  box-sizing: border-box;
}
.notes-actions { display: flex; gap: 8px; margin-top: 10px; }
.notes-cancel {
  flex: 1; padding: 13px; background: #12151C; border: 1px solid #252B38;
  border-radius: 10px; color: #64748B; font-size: 14px; cursor: pointer;
  min-height: 48px; -webkit-tap-highlight-color: transparent;
}
.notes-save {
  flex: 2; padding: 13px; background: #F59E0B; border: none;
  border-radius: 10px; color: #000; font-size: 14px;
  font-weight: 700; cursor: pointer; min-height: 48px;
  -webkit-tap-highlight-color: transparent;
}

.modifier-sheet {
  width: 100%; max-width: 520px; max-height: 90vh;
  display: flex; flex-direction: column;
  background: #1A1E28; border: 1px solid #252B38;
  border-radius: 20px 20px 0 0; overflow: hidden;
}
.sheet-handle-wrap { flex-shrink: 0; padding: 12px 16px 0; text-align: center; }
.modifier-scroll {
  flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; padding: 0 16px;
  padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
}

.toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  z-index: 100; padding: 10px 18px; border-radius: 9px;
  font-weight: 600; font-size: 13px; color: #fff;
  display: flex; align-items: center; gap: 8px; white-space: nowrap;
  box-shadow: 0 4px 16px rgba(0,0,0,0.4);
}
.toast--success { background: #10B981; }
.toast--error   { background: #EF4444; }

/* ════════════════════════════════════════
   MOBILE ≤ 700px
   Cart becomes a full-screen slide-over.
   Menu panel stays in the background.
   ════════════════════════════════════════ */
@media (max-width: 700px) {

  .mobile-only { display: flex !important; }
  .cart-back-btn { display: flex !important; } /* Show back button on mobile */

  /* Show mobile cart, hide desktop cart */
  .mobile-cart { display: flex !important; }
  .desktop-cart { display: none !important; }

  .menu-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }

  /* Menu fills full width; no right border */
  .menu-panel {
    flex: 1 !important;
    border-right: none !important;
    width: 100% !important;
  }

  /* Hide menu when cart is open */
  .panel-hidden {
    display: none !important;
  }

  /*
   * Cart: teleported to body, slides in from the right.
   * Since it's teleported to body, we can use standard fixed positioning
   * without worrying about parent containing blocks.
   */
  .cart-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 300px;
    height: 100vh;
    z-index: 40;
    transform: translateX(100%);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background: #12151C;
    display: flex;
    flex-direction: column;
  }

  .cart-panel.cart-open {
    transform: translateX(0) !important;
  }

  /* Mobile: cart becomes full-screen overlay */
  .cart-panel {
    width: 100% !important;
    height: 100vh !important;
    height: 100dvh !important; /* Dynamic viewport height for mobile */
  }
}
</style>