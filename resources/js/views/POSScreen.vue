<template>
  <div style="display:flex; flex-direction:column; height:100%; background:#0A0C10; overflow:hidden;">

    <!-- ── TOP BAR ── -->
    <div style="display:flex; align-items:center; justify-content:space-between;
                padding:10px 12px; border-bottom:1px solid #252B38; background:#12151C;
                flex-shrink:0; gap:8px;">

      <button
        @click="$router.push('/')"
        style="font-size:13px; color:#64748B; background:#1A1E28; border:1px solid #252B38;
               cursor:pointer; padding:8px 12px; border-radius:8px; min-height:40px;
               white-space:nowrap; -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
      >← Back</button>

      <div style="text-align:center; min-width:0; flex:1;">
        <div style="font-weight:700; font-size:15px; color:#F1F5F9; white-space:nowrap;
                    overflow:hidden; text-overflow:ellipsis;">
          {{ table ? table.name : 'Loading...' }}
        </div>
        <div style="font-size:11px; color:#64748B; margin-top:1px;">
          {{ currentOrder?.order_number ?? '...' }}
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
        <div
          style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:6px; white-space:nowrap;"
          :style="tableStatusStyle"
        >{{ table?.status ?? '...' }}</div>

        <!-- Mobile cart toggle — only visible on small screens -->
        <button
          @click="mobileView = mobileView === 'cart' ? 'menu' : 'cart'"
          class="mobile-only"
          style="position:relative; padding:8px 10px; background:#1A1E28;
                 border:1px solid #252B38; border-radius:8px; color:#F1F5F9;
                 font-size:16px; cursor:pointer; min-height:40px; min-width:40px;
                 display:flex; align-items:center; justify-content:center;
                 -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
        >
          {{ mobileView === 'cart' ? '🍽️' : '🧾' }}
          <span v-if="unsentItems.length > 0 && mobileView !== 'cart'"
            style="position:absolute; top:-5px; right:-5px; background:#F59E0B; color:#000;
                   border-radius:50%; width:18px; height:18px; font-size:10px;
                   font-weight:700; display:flex; align-items:center; justify-content:center;
                   pointer-events:none;">
            {{ unsentItems.length }}
          </span>
        </button>
      </div>
    </div>

    <!-- ── BODY ── -->
    <div style="flex:1; display:flex; overflow:hidden; min-height:0; position:relative;">

      <!-- ══ LEFT: Menu Panel ══ -->
      <div
        class="menu-panel"
        :class="{ 'panel-hidden-mobile': mobileView === 'cart' }"
        style="flex:1; display:flex; flex-direction:column; border-right:1px solid #252B38;
               overflow:hidden; min-width:0;"
      >
        <!-- Category Bar -->
        <div style="display:flex; gap:6px; padding:8px 12px; border-bottom:1px solid #252B38;
                    overflow-x:auto; flex-shrink:0; -webkit-overflow-scrolling:touch;
                    scrollbar-width:none;">
          <button
            v-for="cat in menuStore.categories"
            :key="cat.id"
            @click="menuStore.setActiveCategory(cat.id)"
            style="padding:7px 14px; border-radius:20px; font-size:12px; font-weight:600;
                   border:1px solid #252B38; white-space:nowrap; flex-shrink:0; cursor:pointer;
                   min-height:34px; -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
            :style="{
              background:  menuStore.activeCategory === cat.id ? '#F59E0B' : 'transparent',
              color:       menuStore.activeCategory === cat.id ? '#000'    : '#64748B',
              borderColor: menuStore.activeCategory === cat.id ? '#F59E0B' : '#252B38',
            }"
          >{{ cat.name }}</button>
        </div>

        <!-- Menu Items Grid -->
        <div style="flex:1; overflow-y:auto; padding:8px; -webkit-overflow-scrolling:touch;">
          <div v-if="loading || menuStore.loading"
            style="display:flex; flex-direction:column; align-items:center;
                   justify-content:center; height:100%; color:#64748B; gap:8px;">
            <span style="font-size:36px; opacity:0.3;">⏳</span>
            <span style="font-size:13px;">Loading menu...</span>
          </div>

          <div v-else class="menu-grid">
            <button
              v-for="item in currentItems"
              :key="item.id"
              @click="addItem(item)"
              :disabled="!item.is_available"
              style="background:#1A1E28; border:1px solid #252B38; border-radius:10px;
                     padding:8px 6px; text-align:center; cursor:pointer;
                     display:flex; flex-direction:column; align-items:center;
                     -webkit-tap-highlight-color:transparent; touch-action:manipulation;
                     transition:opacity 0.15s; width:100%; position:relative;"
              :style="{ opacity: item.is_available ? '1' : '0.4',
                        cursor:  item.is_available ? 'pointer' : 'not-allowed' }"
              @touchstart="e => { if(item.is_available) e.currentTarget.style.opacity='0.7' }"
              @touchend="e => { e.currentTarget.style.opacity='1' }"
              @touchcancel="e => { e.currentTarget.style.opacity='1' }"
            >
              <!-- Item image or placeholder -->
              <div v-if="item.image"
                class="menu-item-img"
                :style="{ backgroundImage: 'url(/storage/menu_items/' + item.image + ')' }"
                style="width:100%; border-radius:7px; margin-bottom:5px;
                       background-size:cover; background-position:center;
                       background-repeat:no-repeat; background-color:#252B38;">
              </div>
              <div v-else
                class="menu-item-img"
                style="width:100%; border-radius:7px; margin-bottom:5px;
                       background-color:#252B38; display:flex; align-items:center;
                       justify-content:center; font-size:22px; color:#64748B;">🍽️
              </div>

              <!-- Cart count badge -->
              <span v-if="itemCartCount(item) > 0"
                style="position:absolute; top:5px; right:5px; background:#F59E0B; color:#000;
                       border-radius:50%; width:18px; height:18px; font-size:9px; font-weight:700;
                       display:flex; align-items:center; justify-content:center; pointer-events:none;">
                {{ itemCartCount(item) }}
              </span>

              <div style="font-size:11px; font-weight:600; color:#F1F5F9; line-height:1.3;
                          text-align:center; width:100%;">{{ item.name }}</div>
              <div style="font-size:12px; font-weight:700; color:#F59E0B; margin-top:3px;">
                Rs. {{ item.price }}
              </div>
              <div v-if="item.is_popular"
                style="font-size:8px; background:rgba(16,185,129,0.12); color:#10B981;
                       border-radius:4px; padding:1px 6px; margin-top:3px;">Popular</div>
            </button>
          </div>
        </div>
      </div>

      <!-- ══ RIGHT: Cart Panel ══ -->
      <div
        class="cart-panel"
        :class="{ 'cart-open': mobileView === 'cart' }"
        style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;"
      >
        <!-- Cart Header -->
        <div style="padding:10px 12px; border-bottom:1px solid #252B38; flex-shrink:0;">
          <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
            <div style="min-width:0;">
              <div style="font-weight:700; font-size:13px; color:#F1F5F9;">
                🧾 {{ table?.name ?? 'Order' }}
              </div>
              <div style="font-size:11px; color:#64748B; margin-top:1px;">
                {{ totalItemCount }} item(s) ·
                <span style="color:#F59E0B;">Round {{ currentRound }}</span>
              </div>
            </div>
            <button
              @click="refreshOrder"
              style="background:#1A1E28; border:1px solid #252B38; border-radius:6px;
                     padding:6px 8px; color:#64748B; font-size:11px; cursor:pointer;
                     min-height:32px; -webkit-tap-highlight-color:transparent; flex-shrink:0;"
            >🔄</button>
          </div>
        </div>

        <!-- Cart Items -->
        <div style="flex:1; overflow-y:auto; padding:6px; display:flex; flex-direction:column;
                    gap:0; -webkit-overflow-scrolling:touch;">

          <div v-if="loading"
            style="display:flex; align-items:center; justify-content:center;
                   height:100%; color:#64748B;">
            <span style="font-size:13px;">Loading order...</span>
          </div>

          <div v-else-if="orderItems.length === 0"
            style="display:flex; flex-direction:column; align-items:center;
                   justify-content:center; height:100%; color:#64748B; gap:10px;">
            <span style="font-size:40px; opacity:0.2;">🛒</span>
            <span style="font-size:13px;">Tap menu items to add</span>
          </div>

          <template v-else>

            <!-- SENT ROUNDS -->
            <div v-for="round in sentRounds" :key="'r' + round.number" style="margin-bottom:8px;">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase;
                            letter-spacing:0.07em; color:#64748B; white-space:nowrap;">
                  Round {{ round.number }}
                </div>
                <div style="flex:1; height:1px; background:#252B38;"></div>
                <div style="font-size:9px; color:#64748B; white-space:nowrap; font-family:monospace;">
                  {{ round.sentTime }}
                </div>
              </div>

              <div
                v-for="item in round.items"
                :key="item.id"
                style="border-radius:6px; padding:6px 8px; margin-bottom:3px;
                       background:#1A1E28; border:1px solid #252B38; border-left-width:2px;"
                :style="{ borderLeftColor: statusColor(item.status) }"
              >
                <div style="display:flex; align-items:center; justify-content:space-between; gap:5px;">
                  <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:0;">
                    <div style="width:6px; height:6px; border-radius:50%; flex-shrink:0;"
                         :style="{ background: statusColor(item.status) }"></div>
                    <div style="min-width:0;">
                      <div style="font-size:11px; font-weight:500; color:#F1F5F9;
                                  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ item.item_name }}
                      </div>
                      <div v-if="item.modifiers && item.modifiers.length > 0"
                        style="display:flex; flex-wrap:wrap; gap:2px; margin-top:1px;">
                        <span v-for="mod in item.modifiers" :key="mod.id"
                          style="font-size:8px; padding:1px 3px; border-radius:3px;
                                 background:rgba(100,116,139,0.12); color:#64748B;">
                          {{ mod.name }}<span v-if="mod.price > 0" style="color:#F59E0B;"> +Rs.{{ parseFloat(mod.price).toFixed(0) }}</span>
                        </span>
                      </div>
                      <div v-if="item.notes" style="font-size:9px; color:#64748B; margin-top:1px;">
                        📝 {{ item.notes }}
                      </div>
                    </div>
                  </div>
                  <div style="display:flex; align-items:center; gap:5px; flex-shrink:0;">
                    <div style="font-size:10px; font-weight:700; padding:1px 4px;
                                 border-radius:3px; min-width:18px; text-align:center;"
                      :style="{ background: statusBg(item.status), color: statusColor(item.status) }"
                    >×{{ item.quantity }}</div>
                    <div style="font-size:10px; font-weight:700; color:#94A3B8; min-width:38px; text-align:right;">
                      Rs.{{ parseFloat(item.total_price).toFixed(0) }}
                    </div>
                  </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:4px;">
                  <span style="font-size:8px; font-weight:700; text-transform:uppercase;
                               letter-spacing:0.06em; padding:1px 4px; border-radius:3px;"
                    :style="{ background: statusBg(item.status), color: statusColor(item.status) }"
                  >{{ statusLabel(item.status) }}</span>

                  <button
                    v-if="item.status === 'preparing' || item.status === 'ready'"
                    @click="advanceStatus(item)"
                    style="font-size:9px; padding:2px 6px; border-radius:3px; border:none;
                           cursor:pointer; font-weight:600; min-height:22px;
                           -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
                    :style="{
                      background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                      color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                    }"
                  >{{ item.status === 'preparing' ? '✓ Ready' : '✓ Served' }}</button>
                </div>
              </div>
            </div>

            <!-- UNSENT ITEMS -->
            <div v-if="unsentItems.length > 0">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                <div style="font-size:9px; font-weight:700; text-transform:uppercase;
                            letter-spacing:0.07em; color:#F59E0B; white-space:nowrap;">
                  ✦ New Items
                </div>
                <div style="flex:1; height:1px; background:rgba(245,158,11,0.3);"></div>
                <div style="font-size:9px; color:#F59E0B; white-space:nowrap;">Not sent</div>
              </div>

              <div
                v-for="item in unsentItems"
                :key="item.id"
                style="border-radius:6px; padding:6px 8px; margin-bottom:3px;
                       background:#1A1E28; border:1.5px solid rgba(245,158,11,0.25);
                       border-left:2px solid #F59E0B;"
              >
                <div style="display:flex; align-items:start; justify-content:space-between; gap:5px;">
                  <div style="flex:1; min-width:0;">
                    <div style="font-size:11px; font-weight:600; color:#F1F5F9; line-height:1.2;">
                      {{ item.item_name }}
                    </div>
                    <div v-if="item.notes" style="font-size:9px; color:#64748B; margin-top:1px;">
                      📝 {{ item.notes }}
                    </div>
                  </div>
                  <div style="font-size:12px; font-weight:700; color:#F59E0B; flex-shrink:0;">
                    Rs.{{ parseFloat(item.total_price).toFixed(0) }}
                  </div>
                </div>

                <div style="display:flex; align-items:center; gap:4px; margin-top:5px;">
                  <button @click="decreaseQty(item)"
                    style="width:28px; height:28px; border-radius:5px; border:1px solid #334155;
                           background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                           display:flex; align-items:center; justify-content:center;
                           -webkit-tap-highlight-color:transparent; touch-action:manipulation; flex-shrink:0;"
                  >−</button>
                  <span style="font-size:12px; font-weight:700; min-width:20px;
                               text-align:center; color:#F1F5F9;">{{ item.quantity }}</span>
                  <button @click="increaseQty(item)"
                    style="width:28px; height:28px; border-radius:5px; border:1px solid #334155;
                           background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                           display:flex; align-items:center; justify-content:center;
                           -webkit-tap-highlight-color:transparent; touch-action:manipulation; flex-shrink:0;"
                  >+</button>
                  <button @click="openNotes(item)"
                    style="padding:3px 6px; border-radius:4px; border:1px solid #252B38;
                           background:transparent; color:#64748B; font-size:10px; cursor:pointer;
                           min-height:28px; -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
                  >📝</button>
                  <button @click="voidOrderItem(item)"
                    style="margin-left:auto; background:none; border:none; color:#EF4444;
                           font-size:13px; cursor:pointer; padding:3px 5px;
                           min-height:28px; min-width:28px;
                           -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
                  >✕</button>
                </div>
              </div>

              <!-- Send to Kitchen -->
              <button
                @click="sendKOT"
                style="width:100%; margin-top:4px; padding:8px; border-radius:6px;
                       background:rgba(59,130,246,0.12); color:#3B82F6;
                       border:1px solid rgba(59,130,246,0.35); font-size:11px;
                       font-weight:700; cursor:pointer; min-height:36px;
                       display:flex; align-items:center; justify-content:center; gap:4px;
                       -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
              >
                📋 Send {{ unsentItems.length }} item(s) to Kitchen
              </button>
            </div>

          </template>
        </div>

        <!-- Totals -->
        <div v-if="orderItems.length > 0"
          style="padding:6px 10px; border-top:1px solid #252B38; flex-shrink:0;">
          <div style="display:flex; justify-content:space-between; font-size:10px; color:#64748B; margin-bottom:2px;">
            <span>Subtotal</span>
            <span>Rs. {{ currentOrder?.subtotal ?? '0.00' }}</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:10px; color:#64748B; margin-bottom:2px;">
            <span>Service (10%)</span>
            <span>Rs. {{ currentOrder?.tax_amount ?? '0.00' }}</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-weight:700;
                      font-size:13px; border-top:1px solid #252B38; padding-top:4px; margin-top:2px;">
            <span style="color:#F1F5F9;">Total</span>
            <span style="color:#F59E0B;">Rs. {{ currentOrder?.total ?? '0.00' }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="padding:6px 8px; flex-shrink:0; display:flex; flex-direction:column; gap:4px;
                    padding-bottom:calc(6px + env(safe-area-inset-bottom, 0px)); background:#12151C;">
          <div v-if="unsentItems.length > 0 && sentItems.length > 0"
            style="font-size:9px; color:#F59E0B; text-align:center; padding:4px 6px;
                   background:rgba(245,158,11,0.08); border-radius:4px;
                   border:1px solid rgba(245,158,11,0.2);">
            ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
          </div>

          <button
            @click="showPayment = true"
            :disabled="orderItems.length === 0"
            style="width:100%; padding:10px; border-radius:6px; font-size:13px;
                   font-weight:700; background:#F59E0B; color:#000; border:none;
                   cursor:pointer; min-height:44px;
                   -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
            :style="{ opacity: orderItems.length > 0 ? '1' : '0.4',
                      cursor:  orderItems.length > 0 ? 'pointer' : 'not-allowed' }"
          >
            💳 Charge Rs. {{ currentOrder?.total ?? '0.00' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODALS ══ -->

    <!-- Payment Modal -->
    <Teleport to="body">
      <div v-if="showPayment"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;
               padding:16px;">
        <PaymentModal
          :order="currentOrder"
          @paid="onPaid"
          @cancel="showPayment = false"
        />
      </div>
    </Teleport>

    <!-- Notes Modal — bottom sheet on mobile, centered on desktop -->
    <Teleport to="body">
      <div v-if="notesItem"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:60;
               display:flex; align-items:flex-end; justify-content:center;"
        @click.self="notesItem = null"
      >
        <div style="background:#1A1E28; border:1px solid #252B38;
                    border-radius:20px 20px 0 0; padding:20px 16px; width:100%; max-width:480px;
                    padding-bottom:calc(20px + env(safe-area-inset-bottom, 0px));">
          <div style="width:36px; height:4px; background:#252B38; border-radius:2px; margin:0 auto 14px;"></div>
          <div style="font-size:14px; font-weight:700; color:#F1F5F9; margin-bottom:10px;">
            📝 Note for {{ notesItem.item_name }}
          </div>
          <textarea
            v-model="notesText"
            rows="3"
            placeholder="e.g. No onions, well done, extra sauce..."
            style="width:100%; background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:12px; color:#F1F5F9; font-size:16px; resize:none;
                   font-family:inherit; outline:none; box-sizing:border-box;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          ></textarea>
          <div style="display:flex; gap:8px; margin-top:10px;">
            <button @click="notesItem = null"
              style="flex:1; padding:13px; background:#12151C; border:1px solid #252B38;
                     border-radius:10px; color:#64748B; font-size:14px; cursor:pointer;
                     min-height:48px; -webkit-tap-highlight-color:transparent;">Cancel</button>
            <button @click="saveNotes"
              style="flex:2; padding:13px; background:#F59E0B; border:none;
                     border-radius:10px; color:#000; font-size:14px;
                     font-weight:700; cursor:pointer; min-height:48px;
                     -webkit-tap-highlight-color:transparent;">Save Note</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; bottom:24px; left:50%; transform:translateX(-50%); z-index:100;
               padding:10px 18px; border-radius:9px; font-weight:600; font-size:13px;
               display:flex; align-items:center; gap:8px; white-space:nowrap;
               box-shadow:0 4px 16px rgba(0,0,0,0.4);"
        :style="{ background: toast.type === 'success' ? '#10B981' : '#EF4444', color:'#fff' }"
      >
        {{ toast.type === 'success' ? '✅' : '⚠️' }} {{ toast.message }}
      </div>
    </Teleport>

    <!-- Modifier Selector — fixed bottom sheet, scrollable -->
    <Teleport to="body">
      <div v-if="modifierItem"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:70;
               display:flex; align-items:flex-end; justify-content:center;"
        @click.self="modifierItem = null"
      >
        <!-- Wrapper that caps height and makes content scrollable -->
        <div style="width:100%; max-width:520px; max-height:90vh; display:flex; flex-direction:column;
                    background:#1A1E28; border:1px solid #252B38; border-radius:20px 20px 0 0;
                    overflow:hidden;">
          <!-- Drag handle -->
          <div style="flex-shrink:0; padding:12px 16px 0; text-align:center;">
            <div style="width:36px; height:4px; background:#334155; border-radius:2px; margin:0 auto 12px;"></div>
          </div>
          <!-- Scrollable content area -->
          <div style="flex:1; overflow-y:auto; -webkit-overflow-scrolling:touch; padding:0 16px;
                      padding-bottom:calc(16px + env(safe-area-inset-bottom, 0px));">
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

// ── State ──────────────────────────────────────────────────────────────────
const table        = ref(null)
const loading      = ref(false)
const loadingTable = ref(true)
const showPayment  = ref(false)
const notesItem    = ref(null)
const notesText    = ref('')
const toast        = ref({ show: false, message: '', type: 'success' })
const modifierItem = ref(null)
const mobileView   = ref('menu') // 'menu' | 'cart'

// ── Computed ───────────────────────────────────────────────────────────────
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

const totalItemCount = computed(() =>
  orderItems.value.reduce((s, i) => s + i.quantity, 0)
)

const tableStatusStyle = computed(() => {
  const map = {
    free:     { background:'rgba(16,185,129,0.12)', color:'#10B981' },
    occupied: { background:'rgba(59,130,246,0.12)', color:'#3B82F6' },
    reserved: { background:'rgba(139,92,246,0.12)', color:'#8B5CF6' },
    cleaning: { background:'rgba(245,158,11,0.12)', color:'#F59E0B' },
  }
  return map[table.value?.status] ?? map.occupied
})

// How many of this menu item are in the (unsent) cart
function itemCartCount(menuItem) {
  return orderItems.value
    .filter(i => i.menu_item_id === menuItem.id && !i.kot_round)
    .reduce((s, i) => s + i.quantity, 0)
}

// ── Status helpers ─────────────────────────────────────────────────────────
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

// ── Cart actions ───────────────────────────────────────────────────────────
async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return

  if (menuItem.modifier_groups?.length > 0) {
    modifierItem.value = menuItem
    return
  }

  try {
    const existing = orderItems.value.find(
      i => i.menu_item_id === menuItem.id && !i.kot_round
    )
    if (existing) {
      await orderStore.updateItemQty(currentOrder.value.id, existing.id, existing.quantity + 1)
    } else {
      await orderStore.addItem(currentOrder.value.id, {
        menu_item_id: menuItem.id,
        quantity:     1,
        is_instant:   menuItem.is_instant,
      })
    }
    // On mobile: stay on menu but show badge (don't auto-switch)
    // Comment the line below to prevent auto-switch — badge on toggle button is enough feedback
    // mobileView.value = 'cart'
  } catch (e) {
    showToast('Failed to add: ' + (e.response?.data?.message ?? e.message), 'error')
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
  const next = { preparing:'ready', ready:'served' }[item.status]
  if (!next) return
  try {
    await axios.patch(`/orders/${currentOrder.value.id}/items/${item.id}`, { status: next })
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

// ── Notes ──────────────────────────────────────────────────────────────────
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

// ── Payment ────────────────────────────────────────────────────────────────
async function onPaid() {
  showPayment.value = false
  orderStore.clearOrder()
  await loadTables()
  router.push('/')
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Lifecycle ──────────────────────────────────────────────────────────────
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

    if (orderId) {
      await orderStore.fetchOrder(orderId)
    } else {
      orderStore.clearOrder()
      await orderStore.createOrder({ table_id: tableId, type: 'dine_in' })
    }

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

<style scoped>
/* ── Prevent iOS input zoom ── */
input, textarea, select { font-size: 16px !important; }

/* ── Hide scrollbars ── */
div::-webkit-scrollbar { display: none; }

/* ── Menu image: aspect ratio ── */
.menu-item-img {
  aspect-ratio: 4 / 3;
}

/* ── Menu grid: 3 cols on desktop, 2 on mobile ── */
.menu-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 7px;
}

/* ── Desktop: show cart as sidebar, hide toggle ── */
.mobile-only {
  display: none;
}

/* ════════════════════════════════════════
   MOBILE  ≤ 700 px
   ════════════════════════════════════════ */
@media (max-width: 700px) {

  /* Show the cart toggle button */
  .mobile-only {
    display: flex !important;
  }

  /* 2-column menu grid */
  .menu-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }

  /* Menu panel fills the whole body width */
  .menu-panel {
    flex: 1 !important;
    border-right: none !important;
    width: 100% !important;
  }

  /* Cart panel: slide in from the right, sits over menu panel */
  .cart-panel {
    position: fixed !important;
    top: 53px !important;          /* align with top bar */
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    z-index: 40;
    transform: translateX(100%);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .cart-panel.cart-open {
    transform: translateX(0) !important;
  }
}
</style>