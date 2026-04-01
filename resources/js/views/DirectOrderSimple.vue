<template>
  <div style="display:flex; height:100%; background:#0A0C10; overflow:hidden; font-family:system-ui,-apple-system,sans-serif;">

    <!-- ── LEFT: Menu Panel ── -->
    <div style="flex:1; display:flex; flex-direction:column; border-right:1px solid #252B38; overflow:hidden; min-width:0;">

      <!-- Top Bar -->
      <div style="display:flex; align-items:center; justify-content:space-between;
                  padding:10px 16px; border-bottom:1px solid #252B38; background:#12151C; flex-shrink:0;">

        <button @click="$router.push('/')"
          style="font-size:12px; color:#64748B; background:#1A1E28; border:1px solid #252B38;
                 cursor:pointer; padding:6px 12px; border-radius:6px; transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.color='#F1F5F9'"
          @mouseleave="e => e.currentTarget.style.color='#64748B'"
        >← Back</button>

        <div style="text-align:center;">
          <div style="font-weight:700; font-size:15px; color:#F1F5F9;">⚡ Direct Order</div>
          <div style="font-size:11px; color:#64748B; margin-top:2px;">
            {{ currentOrder?.order_number ?? 'No active order' }}
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
              background:  selectedType === t.value ? t.color : 'transparent',
              color:       selectedType === t.value ? '#000'  : '#64748B',
              borderColor: selectedType === t.value ? t.color : '#252B38',
            }"
          >{{ t.label }}</button>
        </div>
      </div>

      <!-- Category Bar (only when active order exists) -->
      <div v-if="currentOrder"
           style="display:flex; gap:8px; padding:10px 14px; border-bottom:1px solid #252B38;
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

      <!-- Menu Grid (only when active order exists) -->
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
            <div v-if="item.is_instant"
              style="position:absolute; top:6px; right:6px; font-size:9px; font-weight:700;
                     background:rgba(16,185,129,0.15); color:#10B981; padding:1px 5px;
                     border-radius:4px; text-transform:uppercase;">⚡ Instant</div>
            <div v-if="item.modifier_groups?.length > 0"
              style="position:absolute; top:6px; left:6px; font-size:9px; font-weight:700;
                     background:rgba(139,92,246,0.15); color:#8B5CF6; padding:1px 5px; border-radius:4px;">⚙️</div>
            <div v-if="item.image" style="width:110px; height:90px; border-radius:12px; margin-bottom:8px; background-size:cover; background-position:center; background-repeat:no-repeat; background-color:#1A1E28;" :style="{ backgroundImage: 'url(/storage/menu_items/' + item.image + ')' }"></div>
            <div v-else style="width:110px; height:90px; border-radius:12px; margin-bottom:8px; background-color:#2D3748; display:flex; align-items:center; justify-content:center; font-size:36px; color:#64748B;">🍽️</div>
            <div style="font-size:12px; font-weight:500; color:#F1F5F9; line-height:1.3;">{{ item.name }}</div>
            <div style="font-size:14px; font-weight:700; margin-top:5px;"
              :style="{ color: item.is_instant ? '#10B981' : '#F59E0B' }">
              Rs. {{ item.price }}
            </div>
            <div v-if="item.modifier_groups?.length > 0"
              style="font-size:9px; color:#8B5CF6; margin-top:3px;">
              {{ item.modifier_groups.length }} option{{ item.modifier_groups.length > 1 ? 's' : '' }}
            </div>
          </button>
        </div>
      </div>

      <!-- No Active Order: Create prompt -->
      <div v-else
        style="flex:1; display:flex; flex-direction:column; align-items:center;
               justify-content:center; gap:16px; padding:32px;">
        <div style="font-size:48px; opacity:0.1;">⚡</div>
        <div style="text-align:center;">
          <div style="font-size:16px; font-weight:700; color:#F1F5F9; margin-bottom:6px;">No Active Order</div>
          <div style="font-size:12px; color:#64748B;">Create a new order or switch to a pending one</div>
        </div>
        <button
          @click="createNewDirectOrder"
          style="padding:12px 28px; background:linear-gradient(135deg,#10B981,#059669);
                 color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600;
                 cursor:pointer; transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >+ Create New Order</button>
      </div>
    </div>

    <!-- ── MIDDLE: Pending KOT Orders Column ── -->
    <div style="width:210px; display:flex; flex-direction:column; background:#0D1017;
                border-right:1px solid #252B38; flex-shrink:0;">

      <!-- Column Header -->
      <div style="padding:12px 12px 10px; border-bottom:1px solid #252B38; background:#12151C; flex-shrink:0;">
        <div style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;
                    letter-spacing:0.06em; margin-bottom:8px;">🍳 KOT Orders</div>
        <button
          @click="createNewDirectOrder"
          style="width:100%; padding:7px 8px; background:linear-gradient(135deg,#10B981,#059669);
                 color:#fff; border:none; border-radius:7px; font-size:11px; font-weight:600;
                 cursor:pointer; transition:all 0.15s;"
          @mouseenter="e => { e.currentTarget.style.filter='brightness(1.15)'; e.currentTarget.style.transform='translateY(-1px)'; }"
          @mouseleave="e => { e.currentTarget.style.filter='brightness(1)'; e.currentTarget.style.transform='translateY(0)'; }"
        >+ New Order</button>
      </div>

      <!-- Orders List -->
      <div style="flex:1; overflow-y:auto; padding:8px;">

        <!-- Loading -->
        <div v-if="loadingPending"
          style="display:flex; align-items:center; justify-content:center;
                 height:80px; color:#475569; font-size:11px; gap:6px;">
          <span style="opacity:0.5;">⏳</span> Loading...
        </div>

        <!-- Empty -->
        <div v-else-if="pendingKotOrders.length === 0"
          style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                 height:120px; color:#334155; gap:8px; text-align:center; padding:0 8px;">
          <span style="font-size:28px; opacity:0.2;">🍳</span>
          <span style="font-size:11px; color:#475569; line-height:1.5;">No KOT orders<br>in progress</span>
        </div>

        <!-- Order Cards -->
        <div v-else style="display:flex; flex-direction:column; gap:7px;">
          <div
            v-for="order in pendingKotOrders" :key="order.id"
            @click="switchToOrder(order)"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:10px;
                   padding:10px 11px; cursor:pointer; transition:all 0.15s; position:relative;
                   border-left:3px solid transparent;"
            :style="{
              borderLeftColor: order.id === currentOrder?.id ? '#F59E0B' : 'transparent',
              background: order.id === currentOrder?.id ? '#1E2535' : '#1A1E28',
            }"
            @mouseenter="e => {
              if (order.id !== currentOrder?.id) {
                e.currentTarget.style.borderLeftColor='#10B981'
                e.currentTarget.style.background='#1E2535'
              }
            }"
            @mouseleave="e => {
              if (order.id !== currentOrder?.id) {
                e.currentTarget.style.borderLeftColor='transparent'
                e.currentTarget.style.background='#1A1E28'
              }
            }"
          >
            <!-- Active indicator -->
            <div v-if="order.id === currentOrder?.id"
              style="position:absolute; top:8px; right:8px; font-size:8px; font-weight:700;
                     background:rgba(245,158,11,0.15); color:#F59E0B; padding:1px 5px; border-radius:4px;">
              ACTIVE
            </div>

            <!-- Live dot (other orders) -->
            <div v-else
              style="position:absolute; top:10px; right:10px; width:6px; height:6px;
                     border-radius:50%; background:#10B981;
                     box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></div>

            <!-- Order number -->
            <div style="font-size:12px; font-weight:700; color:#F1F5F9; margin-bottom:3px; padding-right:14px;">
              {{ order.order_number }}
            </div>

            <!-- Type badge -->
            <div style="margin-bottom:5px;">
              <span
                style="font-size:9px; font-weight:700; padding:1px 6px; border-radius:4px;
                       text-transform:uppercase; letter-spacing:0.04em;"
                :style="getTypeBadgeStyle(order.type)"
              >{{ order.type?.replace('_', ' ') }}</span>
            </div>

            <!-- Customer -->
            <div style="font-size:11px; color:#94A3B8; margin-bottom:5px; white-space:nowrap;
                        overflow:hidden; text-overflow:ellipsis;">
              👤 {{ order.customer_name || 'Walk-in' }}
            </div>

            <!-- Items + total -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
              <span style="font-size:10px; color:#64748B;">
                🛒 {{ order.items?.length || 0 }} item{{ (order.items?.length || 0) !== 1 ? 's' : '' }}
              </span>
              <span style="font-size:12px; font-weight:700; color:#F59E0B;">
                Rs. {{ parseFloat(order.total || 0).toFixed(0) }}
              </span>
            </div>

            <!-- KOT badge -->
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <span style="font-size:9px; color:#F59E0B; font-weight:600;">🍳 KOT Sent</span>
              <span style="font-size:10px; color:#475569;">{{ formatTime(order.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer count -->
      <div v-if="pendingKotOrders.length > 0"
        style="padding:7px 12px; border-top:1px solid #252B38; background:#12151C; flex-shrink:0;">
        <div style="font-size:10px; color:#475569; text-align:center;">
          {{ pendingKotOrders.length }} order{{ pendingKotOrders.length !== 1 ? 's' : '' }} in progress
        </div>
      </div>
    </div>

    <!-- ── RIGHT: Cart ── -->
    <div v-if="currentOrder"
         style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;">

      <!-- Customer Name -->
      <div style="padding:12px 14px; border-bottom:1px solid #252B38; flex-shrink:0;">
        <div style="font-size:10px; color:#64748B; margin-bottom:5px; font-weight:600;
                    text-transform:uppercase; letter-spacing:0.06em;">Customer</div>
        <input
          v-model="customerName"
          placeholder="Walk-in / Name (optional)"
          style="width:100%; background:#1A1E28; border:1px solid #252B38; border-radius:7px;
                 padding:8px 11px; color:#F1F5F9; font-size:13px; outline:none;
                 font-family:inherit; transition:border 0.15s; box-sizing:border-box;"
          @focus="e => e.target.style.borderColor='#F59E0B'"
          @blur="e => { e.target.style.borderColor='#252B38'; updateCustomerName() }"
        />
      </div>

      <!-- Cart Header -->
      <div style="padding:12px 14px; border-bottom:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <div>
            <div style="font-weight:700; font-size:14px; color:#F1F5F9;">Order Cart</div>
            <div style="font-size:11px; color:#64748B; margin-top:2px;">
              {{ totalItemCount }} item(s) · <span style="color:#F59E0B;">Round {{ currentRound }}</span>
            </div>
          </div>
          <button
            @click="refreshOrder"
            style="background:#1A1E28; border:1px solid #252B38; border-radius:6px;
                   padding:4px 8px; color:#64748B; font-size:11px; cursor:pointer;"
          >🔄</button>
        </div>
      </div>

      <!-- Cart Items -->
      <div style="flex:1; overflow-y:auto; padding:10px; display:flex; flex-direction:column; gap:0;">

        <!-- Empty -->
        <div v-if="orderItems.length === 0"
          style="display:flex; flex-direction:column; align-items:center;
                 justify-content:center; height:100%; color:#64748B; gap:8px;">
          <span style="font-size:40px; opacity:0.2;">⚡</span>
          <span style="font-size:13px;">Add items to start</span>
          <span style="font-size:11px; color:#334155;">Instant items charge immediately</span>
        </div>

        <template v-else>

          <!-- Sent rounds -->
          <div v-for="round in sentRounds" :key="'r' + round.number" style="margin-bottom:12px;">
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

            <div
              v-for="item in round.items" :key="item.id"
              style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                     background:#1A1E28; border:1px solid #252B38; border-left-width:3px;"
              :style="{ borderLeftColor: statusColor(item.status) }"
            >
              <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
                  :style="{ background: statusColor(item.status) }"></div>
                <div style="flex:1; font-size:12px; font-weight:500; color:#F1F5F9;
                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ item.item_name }}
                </div>
                <div style="font-size:11px; font-weight:700; padding:1px 6px; border-radius:4px;"
                  :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                  ×{{ item.quantity }}
                </div>
                <div style="font-size:11px; color:#64748B; min-width:44px; text-align:right;">
                  Rs. {{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>
              <div v-if="item.selected_modifiers?.length > 0"
                style="display:flex; flex-wrap:wrap; gap:3px; margin-top:5px; padding-left:16px;">
                <span v-for="mod in item.selected_modifiers" :key="mod.id"
                  style="font-size:9px; padding:1px 6px; border-radius:4px;
                         background:rgba(100,116,139,0.12); color:#64748B;">
                  {{ mod.name }}
                  <span v-if="mod.pivot?.price_adjustment > 0" style="color:#F59E0B;">
                    +Rs. {{ parseFloat(mod.pivot.price_adjustment).toFixed(2) }}
                  </span>
                </span>
              </div>
              <div v-if="item.notes" style="font-size:10px; color:#64748B; margin-top:3px; padding-left:16px;">
                📝 {{ item.notes }}
              </div>
              <div style="display:flex; align-items:center; justify-content:space-between; margin-top:6px;">
                <span style="font-size:9px; font-weight:700; text-transform:uppercase;
                             padding:1px 6px; border-radius:4px;"
                  :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                  {{ statusLabel(item.status) }}
                </span>
                <button v-if="item.status === 'preparing' || item.status === 'ready'"
                  @click="advanceStatus(item)"
                  style="font-size:10px; padding:2px 8px; border-radius:4px; border:none;
                         cursor:pointer; font-weight:600;"
                  :style="{
                    background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                    color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                  }"
                >{{ item.status === 'preparing' ? '✓ Mark Ready' : '✓ Served' }}</button>
              </div>
            </div>
          </div>

          <!-- Unsent items -->
          <div v-if="unsentItems.length > 0">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#F59E0B; white-space:nowrap;">✦ New Items</div>
              <div style="flex:1; height:1px; background:rgba(245,158,11,0.3);"></div>
              <div style="font-size:10px; color:#F59E0B; white-space:nowrap;">Not sent yet</div>
            </div>

            <div
              v-for="item in unsentItems" :key="item.id"
              style="border-radius:8px; padding:10px 11px; margin-bottom:4px;
                     background:#1A1E28; border:1.5px solid rgba(245,158,11,0.25);
                     border-left:3px solid #F59E0B;"
            >
              <div style="display:flex; align-items:start; justify-content:space-between; gap:8px;">
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; font-weight:600; color:#F1F5F9;">{{ item.item_name }}</div>
                  <div v-if="item.notes" style="font-size:10px; color:#64748B; margin-top:2px;">📝 {{ item.notes }}</div>
                </div>
                <div style="font-size:13px; font-weight:700; color:#F59E0B; flex-shrink:0;">
                  Rs. {{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>
              <div style="display:flex; align-items:center; gap:6px; margin-top:8px;">
                <button @click="decreaseQty(item)"
                  style="width:24px; height:24px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >−</button>
                <span style="font-size:13px; font-weight:700; min-width:20px; text-align:center; color:#F1F5F9;">
                  {{ item.quantity }}
                </span>
                <button @click="increaseQty(item)"
                  style="width:24px; height:24px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >+</button>
                <button @click="openNotes(item)"
                  style="padding:3px 7px; border-radius:5px; border:1px solid #252B38;
                         background:transparent; color:#64748B; font-size:11px; cursor:pointer;"
                  @mouseenter="e => e.currentTarget.style.color='#F1F5F9'"
                  @mouseleave="e => e.currentTarget.style.color='#64748B'"
                >📝 Note</button>
                <button @click="voidOrderItem(item)"
                  style="margin-left:auto; background:none; border:none; color:#EF4444;
                         font-size:11px; cursor:pointer; padding:3px 5px;"
                >✕</button>
              </div>
            </div>

            <button
              @click="sendKOT"
              style="width:100%; margin-top:6px; padding:11px; border-radius:8px;
                     background:rgba(59,130,246,0.12); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.35); font-size:13px;
                     font-weight:700; cursor:pointer; transition:all 0.15s;
                     display:flex; align-items:center; justify-content:center; gap:6px;"
              @mouseenter="e => e.currentTarget.style.background='rgba(59,130,246,0.22)'"
              @mouseleave="e => e.currentTarget.style.background='rgba(59,130,246,0.12)'"
            >📋 Send {{ unsentItems.length }} item(s) to Kitchen</button>
          </div>

        </template>
      </div>

      <!-- Totals -->
      <div style="padding:12px 14px; border-top:1px solid #252B38; flex-shrink:0;">
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Subtotal</span><span>Rs. {{ currentOrder?.subtotal ?? '0.00' }}</span>
        </div>
        <div v-if="currentOrder?.tax_rate > 0" style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Tax ({{ currentOrder?.tax_rate ?? 0 }}%)</span><span>Rs. {{ currentOrder?.tax_amount ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:15px;
                    border-top:1px solid #252B38; padding-top:8px; margin-top:6px;">
          <span style="color:#F1F5F9;">Total</span>
          <span style="color:#F59E0B;">Rs. {{ currentOrder?.total ?? '0.00' }}</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div style="padding:12px 14px; border-top:1px solid #252B38; flex-shrink:0; display:flex; flex-direction:column; gap:8px;">

        <div v-if="unsentItems.length > 0 && sentItems.length > 0"
          style="font-size:11px; color:#F59E0B; text-align:center; padding:5px 10px;
                 background:rgba(245,158,11,0.08); border-radius:6px;
                 border:1px solid rgba(245,158,11,0.2);">
          ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
        </div>

        <button
          v-if="unsentItems.length > 0"
          @click="sendKOT"
          style="width:100%; padding:10px; background:linear-gradient(135deg,#F59E0B,#D97706);
                 color:#000; border:none; border-radius:8px; font-size:13px; font-weight:700;
                 cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;"
          @mouseenter="e => e.currentTarget.style.filter='brightness(1.1)'"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >🍳 Send KOT ({{ unsentItems.length }} items)</button>

        <button
          @click="orderItems.length > 0 ? showPayment = true : null"
          style="width:100%; padding:12px; background:linear-gradient(135deg,#10B981,#059669);
                 color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700;
                 cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;"
          :style="{ opacity: orderItems.length > 0 ? '1' : '0.45',
                    cursor:  orderItems.length > 0 ? 'pointer' : 'not-allowed' }"
          @mouseenter="e => { if(orderItems.length > 0) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >💳 Charge Rs. {{ currentOrder?.total ?? '0.00' }}</button>

        <!-- Delete order (no KOT items only) -->
        <button
          v-if="!hasKotItems()"
          @click="deleteCurrentOrder"
          style="width:100%; padding:8px; background:transparent; color:#EF4444;
                 border:1px solid rgba(239,68,68,0.25); border-radius:7px; font-size:11px;
                 font-weight:600; cursor:pointer; transition:all 0.15s;"
          @mouseenter="e => e.currentTarget.style.background='rgba(239,68,68,0.08)'"
          @mouseleave="e => e.currentTarget.style.background='transparent'"
        >🗑️ Delete Order</button>
      </div>
    </div>

    <!-- Payment Modal -->
    <Teleport to="body">
      <div v-if="showPayment && currentOrder"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;">
        <PaymentModal :order="currentOrder" @paid="onPaid" @cancel="showPayment = false" />
      </div>
    </Teleport>

    <!-- Modifier Selector -->
    <Teleport to="body">
      <ModifierSelector
        v-if="modifierItem"
        :item="modifierItem"
        @confirm="onModifierConfirm"
        @cancel="modifierItem = null"
      />
    </Teleport>

    <!-- Notes Modal -->
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
            v-model="notesText" rows="3"
            placeholder="e.g. No onions, well done, extra sauce..."
            style="width:100%; background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:10px 12px; color:#F1F5F9; font-size:13px; resize:none;
                   font-family:inherit; outline:none; box-sizing:border-box;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          ></textarea>
          <div style="display:flex; gap:8px; margin-top:12px;">
            <button @click="notesItem = null"
              style="flex:1; padding:10px; background:#12151C; border:1px solid #252B38;
                     border-radius:8px; color:#64748B; font-size:13px; cursor:pointer;">Cancel</button>
            <button @click="saveNotes"
              style="flex:2; padding:10px; background:#F59E0B; border:none;
                     border-radius:8px; color:#000; font-size:13px; font-weight:700; cursor:pointer;">Save Note</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; top:20px; right:20px; padding:12px 18px; border-radius:8px;
               font-size:13px; font-weight:500; z-index:9999;"
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
import axios                        from 'axios'
import PaymentModal                 from '@/components/PaymentModal.vue'
import ModifierSelector             from '@/components/ModifierSelector.vue'

const router     = useRouter()
const orderStore = useOrderStore()
const menuStore  = useMenuStore()

// ── State ─────────────────────────────────────────────────
const loading        = ref(false)
const loadingPending = ref(false)
const showPayment    = ref(false)
const modifierItem   = ref(null)
const notesItem      = ref(null)
const notesText      = ref('')
const toast          = ref({ show: false, message: '', type: 'success' })
const selectedType   = ref('takeaway')
const customerName   = ref('')
const pendingKotOrders = ref([])   // only orders that have KOT items (from backend filter)

// ── Order types ───────────────────────────────────────────
const orderTypes = [
  { value: 'takeaway', label: 'Takeaway', color: '#10B981' },
  { value: 'dine_in',  label: 'Dine In',  color: '#F59E0B' },
  { value: 'bar',      label: 'Bar',      color: '#8B5CF6' },
  { value: 'counter',  label: 'Counter',  color: '#3B82F6' },
  { value: 'delivery', label: 'Delivery', color: '#EF4444' },
]

const typeBadgeColors = {
  takeaway: { background: 'rgba(16,185,129,0.15)', color: '#10B981' },
  dine_in:  { background: 'rgba(245,158,11,0.15)', color: '#F59E0B' },
  bar:      { background: 'rgba(139,92,246,0.15)', color: '#8B5CF6' },
  counter:  { background: 'rgba(59,130,246,0.15)', color: '#3B82F6' },
  delivery: { background: 'rgba(239,68,68,0.15)',  color: '#EF4444' },
}

function getTypeBadgeStyle(type) {
  return typeBadgeColors[type] ?? typeBadgeColors.takeaway
}

function formatTime(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// ── Computed ──────────────────────────────────────────────
const currentOrder = computed(() => orderStore.currentOrder)
const currentItems = computed(() => menuStore.getItemsByCategory(menuStore.activeCategory))

const orderItems = computed(() =>
  (currentOrder.value?.items ?? []).filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })
)

const unsentItems = computed(() => orderItems.value.filter(i => !i.kot_round))
const sentItems   = computed(() => orderItems.value.filter(i =>  i.kot_round))

const sentRounds = computed(() => {
  const groups = {}
  sentItems.value.forEach(item => {
    const r = item.kot_round
    if (!groups[r]) {
      groups[r] = {
        number: r,
        sentTime: item.kot_sent_at
          ? new Date(item.kot_sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
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

// ── Status helpers ────────────────────────────────────────
function statusColor(s) {
  return { pending:'#F59E0B', preparing:'#3B82F6', ready:'#10B981', served:'#64748B' }[s] ?? '#64748B'
}
function statusBg(s) {
  return {
    pending:   'rgba(245,158,11,0.12)',
    preparing: 'rgba(59,130,246,0.12)',
    ready:     'rgba(16,185,129,0.12)',
    served:    'rgba(100,116,139,0.12)',
  }[s] ?? 'rgba(100,116,139,0.12)'
}
function statusLabel(s) {
  return { pending:'🟡 Pending', preparing:'🔵 Preparing', ready:'🟢 Ready', served:'⚫ Served' }[s] ?? s
}

function hasKotItems() {
  return orderItems.value.some(i => i.kot_round && i.kot_round > 0)
}

// ── Load pending KOT orders ───────────────────────────────
// Backend already filters: whereHas items with kot_round not null
async function loadPendingKotOrders() {
  loadingPending.value = true
  try {
    const { data } = await axios.get('/direct-orders/pending')
    // Backend returns orders that have KOT items; ensure flat array
    const all = Array.isArray(data) ? data : (data.pending_orders ?? [])
    // Extra client-side guard: only show orders that actually have items with kot_round
    pendingKotOrders.value = all.filter(o =>
      o.items && o.items.some(i => i.kot_round && i.kot_round > 0)
    )
  } catch (e) {
    console.error('Failed to load pending KOT orders:', e)
  } finally {
    loadingPending.value = false
  }
}

// ── API actions ───────────────────────────────────────────
async function createNewDirectOrder() {
  try {
    const { data } = await axios.post('/direct-orders', {
      type:          selectedType.value,
      customer_name: 'Walk-in',
    })
    orderStore.setOrder(data)
    selectedType.value = data.type || 'takeaway'
    customerName.value = ''
    showPayment.value  = false
    await loadPendingKotOrders()
    showToast(`Created ${data.order_number} ✓`, 'success')
  } catch (e) {
    showToast('Failed to create order', 'error')
  }
}

async function switchToOrder(order) {
  if (!order || order.id === currentOrder.value?.id) return
  try {
    const { data } = await axios.post(`/direct-orders/${order.id}/switch`)
    orderStore.setOrder(data)
    selectedType.value = data.type || 'takeaway'
    customerName.value = data.customer_name || ''
    showPayment.value  = false
    await loadPendingKotOrders()
    showToast(`Switched to ${data.order_number} ✓`, 'success')
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to switch order', 'error')
  }
}

async function updateOrderType(type) {
  selectedType.value = type
  if (!currentOrder.value) return
  try {
    await axios.patch(`/direct-orders/${currentOrder.value.id}/type`, { type })
    orderStore.currentOrder.type = type
  } catch (e) {
    showToast('Failed to update order type', 'error')
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

async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return
  if (menuItem.modifier_groups?.length > 0) {
    modifierItem.value = menuItem
    return
  }
  try {
    await orderStore.addItem(currentOrder.value.id, {
      menu_item_id: menuItem.id,
      quantity:     1,
      is_instant:   menuItem.is_instant,
    })
    if (menuItem.is_instant) showToast(`${menuItem.name} added ⚡`, 'success')
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
  if (unsentItems.value.length === 0) { showToast('No new items to send', 'error'); return }
  try {
    const res = await orderStore.sendKOT(currentOrder.value.id)
    showToast(`Round ${res.round}: ${res.items_sent} item(s) sent to kitchen! 🍳`, 'success')
    await loadPendingKotOrders()   // refresh column after KOT sent
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Failed to send KOT', 'error')
  }
}

async function advanceStatus(item) {
  const next = { preparing: 'ready', ready: 'served' }[item.status]
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
    await loadPendingKotOrders()
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
    await axios.patch(`/orders/${currentOrder.value.id}/items/${notesItem.value.id}`, { notes: notesText.value })
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
  showToast('Payment completed! 🎉', 'success')
  localStorage.removeItem('pos_current_order')
  orderStore.clearOrder()
  customerName.value = ''
  selectedType.value = 'takeaway'
  
  // Reload the page to refresh pending orders list
  setTimeout(() => {
    window.location.reload()
  }, 1500)
}

// ── Delete ────────────────────────────────────────────────
async function deleteCurrentOrder() {
  if (!currentOrder.value) return
  if (!confirm('Delete this order? This cannot be undone.')) return
  try {
    await axios.delete(`/direct-orders/${currentOrder.value.id}`)
    localStorage.removeItem('pos_current_order')
    orderStore.clearOrder()
    customerName.value = ''
    selectedType.value = 'takeaway'
    showToast('Order deleted', 'success')
    await loadPendingKotOrders()
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to delete order', 'error')
  }
}

// ── Toast ─────────────────────────────────────────────────
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  loading.value = true
  await menuStore.fetchMenu()

  // Restore saved direct order if valid
  const saved = localStorage.getItem('pos_current_order')
  if (saved) {
    try {
      const orderData = JSON.parse(saved)
      if (!orderData.table_id) {
        await orderStore.fetchOrder(orderData.id)
        const order = currentOrder.value
        if (order &&
            order.payment_status !== 'paid' &&
            order.status !== 'completed' &&
            order.status !== 'cancelled') {
          selectedType.value = order.type || 'takeaway'
          customerName.value = order.customer_name || ''
        } else {
          orderStore.clearOrder()
          localStorage.removeItem('pos_current_order')
        }
      } else {
        orderStore.clearOrder()
        localStorage.removeItem('pos_current_order')
      }
    } catch {
      orderStore.clearOrder()
      localStorage.removeItem('pos_current_order')
    }
  }

  await loadPendingKotOrders()
  loading.value = false
})
</script>

<style scoped>
@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #252B38; border-radius: 2px; }

/* ── Mobile Responsive ── */
@media (max-width: 768px) {
  .main-layout {
    flex-direction: column;
  }
  
  .menu-panel {
    width: 100%;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid #252B38;
    max-height: 40vh;
  }
  
  .order-panel {
    width: 100%;
    max-width: 100%;
  }
  
  .category-tabs {
    flex-wrap: wrap;
    gap: 4px;
  }
  
  .category-tabs button {
    font-size: 11px;
    padding: 6px 10px;
    min-width: auto;
  }
  
  .menu-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 8px;
    padding: 8px;
  }
  
  .menu-item {
    padding: 8px;
    border-radius: 8px;
  }
  
  .item-image {
    width: 80px;
    height: 66px;
    font-size: 24px;
  }
  
  .item-name {
    font-size: 11px;
  }
  
  .item-price {
    font-size: 10px;
  }
  
  .order-header {
    padding: 12px 14px;
  }
  
  .order-header h2 {
    font-size: 16px;
  }
  
  .cart-items {
    max-height: 25vh;
  }
  
  .cart-item {
    padding: 8px 10px;
  }
  
  .cart-item-name {
    font-size: 12px;
  }
  
  .cart-item-price {
    font-size: 11px;
  }
  
  .order-totals {
    padding: 10px 14px;
  }
  
  .order-totals div {
    font-size: 11px;
  }
  
  .order-totals div:last-child {
    font-size: 13px;
  }
  
  .action-buttons {
    padding: 10px 14px;
    gap: 6px;
  }
  
  .action-buttons button {
    font-size: 12px;
    padding: 10px;
  }
}

@media (max-width: 480px) {
  .menu-panel {
    max-height: 35vh;
  }
  
  .category-tabs {
    gap: 2px;
  }
  
  .category-tabs button {
    font-size: 10px;
    padding: 4px 8px;
  }
  
  .menu-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 6px;
    padding: 6px;
  }
  
  .menu-item {
    padding: 6px;
    border-radius: 6px;
  }
  
  .item-image {
    width: 70px;
    height: 58px;
    font-size: 20px;
  }
  
  .item-name {
    font-size: 10px;
  }
  
  .item-price {
    font-size: 9px;
  }
  
  .order-header {
    padding: 10px 12px;
  }
  
  .order-header h2 {
    font-size: 14px;
  }
  
  .cart-items {
    max-height: 30vh;
  }
  
  .cart-item {
    padding: 6px 8px;
  }
  
  .cart-item-name {
    font-size: 11px;
  }
  
  .cart-item-price {
    font-size: 10px;
  }
  
  .order-totals {
    padding: 8px 12px;
  }
  
  .order-totals div {
    font-size: 10px;
  }
  
  .order-totals div:last-child {
    font-size: 12px;
  }
  
  .action-buttons {
    padding: 8px 12px;
    gap: 4px;
  }
  
  .action-buttons button {
    font-size: 11px;
    padding: 8px;
  }
}
</style>