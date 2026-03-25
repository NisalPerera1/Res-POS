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
          <!-- Load Pending Orders Button -->
          <button 
            v-if="!currentOrder"
            @click="loadPendingDirectOrders"
            style="margin-top:8px; padding:4px 12px; background:#10B981; color:#fff; 
                   border:none; border-radius:6px; font-size:11px; cursor:pointer;"
          >
            🔄 Load Pending Orders
          </button>
        </div>

        <!-- Order type buttons -->
        <div style="display:flex; gap:4px;">
          <button
            v-for="t in orderTypes" :key="t.value"
            @click="selectedType = t.value"
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
      <div style="flex:1; overflow-y:auto; padding:12px;">
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
              ${{ item.price }}
            </div>

            <!-- ✅ Modifier indicator — INSIDE the button, after price -->
            <div v-if="item.modifier_groups && item.modifier_groups.length > 0"
              style="font-size:9px; color:#8B5CF6; margin-top:3px;
                     display:flex; align-items:center; gap:2px;">
              <span>{{ item.modifier_groups.length }} option{{ item.modifier_groups.length > 1 ? 's' : '' }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- ── RIGHT: Cart ── -->
    <div style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;">

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

          <!-- ── INSTANT ITEMS ── -->
          <div v-if="instantItems.length > 0" style="margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#10B981; white-space:nowrap;">
                ⚡ Instant
              </div>
              <div style="flex:1; height:1px; background:rgba(16,185,129,0.2);"></div>
              <div style="font-size:10px; color:#10B981;">No kitchen needed</div>
            </div>

            <div v-for="item in instantItems" :key="item.id"
              style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                     background:#1A1E28; border:1px solid rgba(16,185,129,0.2);
                     border-left:3px solid #10B981;">
              <div style="display:flex; align-items:center; gap:8px;">
                <div style="font-size:8px; font-weight:700; color:#10B981; background:rgba(16,185,129,0.1);
                             padding:2px 5px; border-radius:3px; flex-shrink:0;">SERVED</div>
                <div style="flex:1; font-size:12px; font-weight:500; color:#F1F5F9; min-width:0;
                             white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ item.item_name }}
                </div>
                <div style="font-size:11px; font-weight:700; color:#10B981; flex-shrink:0;">
                  ×{{ item.quantity }}
                </div>
                <div style="font-size:11px; color:#64748B; min-width:44px; text-align:right; flex-shrink:0;">
                  ${{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>

              <!-- Modifier pills -->
              <div v-if="item.modifiers && item.modifiers.length > 0"
                style="display:flex; flex-wrap:wrap; gap:3px; margin-top:4px; padding-left:2px;">
                <span v-for="mod in item.modifiers" :key="mod.id"
                  style="font-size:9px; padding:1px 6px; border-radius:3px;
                         background:rgba(16,185,129,0.08); color:#10B981;">
                  {{ mod.name }}
                  <span v-if="mod.price > 0">+${{ parseFloat(mod.price).toFixed(2) }}</span>
                </span>
              </div>

              <!-- Qty controls -->
              <div style="display:flex; align-items:center; gap:5px; margin-top:6px; padding-left:2px;">
                <button @click="decreaseQty(item)"
                  style="width:22px; height:22px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#EF4444'; e.currentTarget.style.color='#fff' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >−</button>
                <span style="font-size:12px; font-weight:700; min-width:18px; text-align:center; color:#F1F5F9;">
                  {{ item.quantity }}
                </span>
                <button @click="increaseQty(item)"
                  style="width:22px; height:22px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:14px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#10B981'; e.currentTarget.style.color='#fff' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >+</button>
                <button @click="voidOrderItem(item)"
                  style="margin-left:auto; background:none; border:none;
                         color:#475569; font-size:11px; cursor:pointer; padding:2px 4px;"
                  @mouseenter="e => e.currentTarget.style.color='#EF4444'"
                  @mouseleave="e => e.currentTarget.style.color='#475569'"
                >✕ remove</button>
              </div>
            </div>
          </div>

          <!-- ── SENT ROUNDS ── -->
          <div v-for="round in sentRounds" :key="'r' + round.number" style="margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#64748B; white-space:nowrap;">
                Round {{ round.number }}
              </div>
              <div style="flex:1; height:1px; background:#252B38;"></div>
              <div style="font-size:10px; color:#64748B; font-family:monospace;">{{ round.sentTime }}</div>
            </div>

            <div v-for="item in round.items" :key="item.id"
              style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                     background:#1A1E28; border:1px solid #252B38; border-left:3px solid;"
              :style="{ borderLeftColor: statusColor(item.status) }"
            >
              <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
                  :style="{ background: statusColor(item.status) }"></div>
                <div style="flex:1; font-size:12px; font-weight:500; color:#CBD5E1; min-width:0;
                             white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ item.item_name }}
                </div>
                <div style="font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px; flex-shrink:0;"
                  :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                  ×{{ item.quantity }}
                </div>
                <div style="font-size:11px; color:#64748B; min-width:44px; text-align:right; flex-shrink:0;">
                  ${{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>

              <!-- Modifier pills -->
              <div v-if="item.modifiers && item.modifiers.length > 0"
                style="display:flex; flex-wrap:wrap; gap:3px; margin-top:4px; padding-left:16px;">
                <span v-for="mod in item.modifiers" :key="mod.id"
                  style="font-size:9px; padding:1px 6px; border-radius:3px;
                         background:rgba(100,116,139,0.1); color:#64748B;">
                  {{ mod.name }}
                  <span v-if="mod.price > 0" style="color:#F59E0B;">+${{ parseFloat(mod.price).toFixed(2) }}</span>
                </span>
              </div>

              <div style="display:flex; align-items:center; justify-content:space-between; margin-top:5px; padding-left:16px;">
                <span style="font-size:9px; font-weight:700; text-transform:uppercase;
                             letter-spacing:0.05em; padding:2px 7px; border-radius:4px;"
                  :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                  {{ statusLabel(item.status) }}
                </span>
                <button v-if="item.status === 'preparing' || item.status === 'ready'"
                  @click="advanceStatus(item)"
                  style="font-size:10px; padding:2px 7px; border-radius:4px; border:none;
                         cursor:pointer; font-weight:600;"
                  :style="{
                    background: item.status === 'preparing' ? 'rgba(16,185,129,0.12)' : 'rgba(100,116,139,0.12)',
                    color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                  }"
                >{{ item.status === 'preparing' ? '✓ Ready' : '✓ Served' }}</button>
              </div>
            </div>
          </div>

          <!-- ── UNSENT ITEMS ── -->
          <div v-if="unsentItems.length > 0">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
              <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                          letter-spacing:0.07em; color:#F59E0B; white-space:nowrap;">
                ✦ New — Not Sent
              </div>
              <div style="flex:1; height:1px; background:rgba(245,158,11,0.25);"></div>
            </div>

            <div v-for="item in unsentItems" :key="item.id"
              style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                     background:#1A1E28; border:1.5px solid rgba(245,158,11,0.2);
                     border-left:3px solid #F59E0B;">
              <div style="display:flex; align-items:start; justify-content:space-between; gap:8px;">
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; font-weight:600; color:#F1F5F9;">{{ item.item_name }}</div>
                  <div v-if="item.notes" style="font-size:10px; color:#64748B; margin-top:1px;">📝 {{ item.notes }}</div>
                </div>
                <div style="font-size:13px; font-weight:700; color:#F59E0B; flex-shrink:0;">
                  ${{ parseFloat(item.total_price).toFixed(2) }}
                </div>
              </div>

              <!-- Modifier pills -->
              <div v-if="item.modifiers && item.modifiers.length > 0"
                style="display:flex; flex-wrap:wrap; gap:3px; margin-top:4px;">
                <span v-for="mod in item.modifiers" :key="mod.id"
                  style="font-size:9px; padding:1px 6px; border-radius:3px;
                         background:rgba(245,158,11,0.08); color:#F59E0B;">
                  {{ mod.name }}
                  <span v-if="mod.price > 0">+${{ parseFloat(mod.price).toFixed(2) }}</span>
                </span>
              </div>

              <div style="display:flex; align-items:center; gap:5px; margin-top:7px;">
                <button @click="decreaseQty(item)"
                  style="width:24px; height:24px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:15px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >−</button>
                <span style="font-size:12px; font-weight:700; min-width:20px; text-align:center; color:#F1F5F9;">
                  {{ item.quantity }}
                </span>
                <button @click="increaseQty(item)"
                  style="width:24px; height:24px; border-radius:5px; border:1px solid #334155;
                         background:#252B38; color:#F1F5F9; font-size:15px; cursor:pointer;
                         display:flex; align-items:center; justify-content:center;"
                  @mouseenter="e => { e.currentTarget.style.background='#F59E0B'; e.currentTarget.style.color='#000' }"
                  @mouseleave="e => { e.currentTarget.style.background='#252B38'; e.currentTarget.style.color='#F1F5F9' }"
                >+</button>
                <button @click="openNotes(item)"
                  style="padding:3px 7px; border-radius:4px; border:1px solid #252B38;
                         background:transparent; color:#64748B; font-size:11px; cursor:pointer;"
                >📝</button>
                <button @click="voidOrderItem(item)"
                  style="margin-left:auto; background:none; border:none; color:#475569;
                         font-size:11px; cursor:pointer;"
                  @mouseenter="e => e.currentTarget.style.color='#EF4444'"
                  @mouseleave="e => e.currentTarget.style.color='#475569'"
                >✕ remove</button>
              </div>
            </div>

            <!-- Send to Kitchen -->
            <button @click="sendKOT"
              style="width:100%; margin-top:6px; padding:11px; border-radius:8px;
                     background:rgba(59,130,246,0.12); color:#3B82F6;
                     border:1px solid rgba(59,130,246,0.3); font-size:13px;
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

      <!-- Totals -->
      <div v-if="orderItems.length > 0"
        style="padding:10px 14px; border-top:1px solid #252B38; flex-shrink:0;">

        <!-- Summary chips -->
        <div style="display:flex; gap:5px; flex-wrap:wrap; margin-bottom:8px;">
          <div v-if="instantItems.length > 0"
            style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:4px;
                   background:rgba(16,185,129,0.1); color:#10B981;">
            ⚡ {{ instantItems.reduce((s,i) => s+i.quantity, 0) }} instant
          </div>
          <div v-if="sentItems.length > 0"
            style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:4px;
                   background:rgba(59,130,246,0.1); color:#3B82F6;">
            🍳 {{ sentItems.reduce((s,i) => s+i.quantity, 0) }} in kitchen
          </div>
          <div v-if="unsentItems.length > 0"
            style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:4px;
                   background:rgba(245,158,11,0.1); color:#F59E0B;">
            ✦ {{ unsentItems.reduce((s,i) => s+i.quantity, 0) }} pending
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Subtotal</span><span>${{ currentOrder?.subtotal ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748B; margin-bottom:3px;">
          <span>Tax (10%)</span><span>${{ currentOrder?.tax_amount ?? '0.00' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:15px;
                    border-top:1px solid #252B38; padding-top:8px; margin-top:4px;">
          <span style="color:#F1F5F9;">Total</span>
          <span style="color:#F59E0B;">${{ currentOrder?.total ?? '0.00' }}</span>
        </div>
      </div>

      <!-- Charge button -->
      <div style="padding:10px 12px 14px; flex-shrink:0;">
        <div v-if="unsentItems.length > 0"
          style="font-size:11px; color:#F59E0B; text-align:center; padding:5px 10px;
                 margin-bottom:8px; background:rgba(245,158,11,0.08); border-radius:6px;
                 border:1px solid rgba(245,158,11,0.2);">
          ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
        </div>
        <button @click="showPayment = true"
          :disabled="orderItems.length === 0"
          style="width:100%; padding:13px; border-radius:9px; font-size:14px;
                 font-weight:700; background:#F59E0B; color:#000; border:none;
                 cursor:pointer; transition:all 0.15s;"
          :style="{ opacity: orderItems.length > 0 ? '1' : '0.4' }"
          @mouseenter="e => { if(orderItems.length > 0) e.currentTarget.style.filter='brightness(1.1)' }"
          @mouseleave="e => e.currentTarget.style.filter='brightness(1)'"
        >
          💳 Charge ${{ currentOrder?.total ?? '0.00' }}
        </button>
      </div>
    </div>

    <!-- ✅ Payment Modal — INSIDE root div -->
    <Teleport to="body">
      <div v-if="showPayment"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50;">
        <PaymentModal :order="currentOrder" @paid="onPaid" @cancel="showPayment = false" />
      </div>
    </Teleport>

    <!-- ✅ Notes Modal — INSIDE root div -->
    <Teleport to="body">
      <div v-if="notesItem"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:60;"
        @click.self="notesItem = null">
        <div style="background:#1A1E28; border:1px solid #252B38; border-radius:14px; padding:22px; width:300px;">
          <div style="font-size:14px; font-weight:700; color:#F1F5F9; margin-bottom:12px;">
            📝 Note for {{ notesItem.item_name }}
          </div>
          <textarea v-model="notesText" rows="3" placeholder="No onions, well done..."
            style="width:100%; background:#12151C; border:1px solid #252B38; border-radius:8px;
                   padding:10px; color:#F1F5F9; font-size:13px; resize:none; font-family:inherit; outline:none;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          ></textarea>
          <div style="display:flex; gap:8px; margin-top:10px;">
            <button @click="notesItem = null"
              style="flex:1; padding:10px; background:#12151C; border:1px solid #252B38;
                     border-radius:8px; color:#64748B; font-size:13px; cursor:pointer;">Cancel</button>
            <button @click="saveNotes"
              style="flex:2; padding:10px; background:#F59E0B; border:none;
                     border-radius:8px; color:#000; font-size:13px; font-weight:700; cursor:pointer;">Save</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ✅ Toast — INSIDE root div -->
    <Teleport to="body">
      <div v-if="toast.show"
        style="position:fixed; bottom:24px; right:24px; z-index:100;
               padding:12px 20px; border-radius:9px; font-weight:600; font-size:13px;
               display:flex; align-items:center; gap:8px;"
        :style="{ background: toast.type === 'success' ? '#10B981' : '#EF4444', color: '#fff' }">
        {{ toast.type === 'success' ? '✅' : '⚠️' }} {{ toast.message }}
      </div>
    </Teleport>

    <!-- ✅ ModifierSelector — INSIDE root div, uses Teleport internally -->
    <ModifierSelector
      v-if="modifierItem"
      :item="modifierItem"
      @confirm="onModifierConfirm"
      @cancel="modifierItem = null"
    />

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

// ── State ──────────────────────────────────────────────
const loading      = ref(false)
const showPayment  = ref(false)
const customerName = ref('Walk-in')
const selectedType = ref('takeaway')
const notesItem    = ref(null)
const notesText    = ref('')
const toast        = ref({ show: false, message: '', type: 'success' })
const modifierItem = ref(null)   // ← holds item waiting for modifier selection

const orderTypes = [
  { value: 'takeaway', label: '🥡 Takeaway', color: '#F59E0B' },
  { value: 'bar',      label: '🍺 Bar',      color: '#3B82F6' },
  { value: 'counter',  label: '🏪 Counter',  color: '#10B981' },
]

// ── Computed ───────────────────────────────────────────
const currentOrder = computed(() => orderStore.currentOrder)
const currentItems = computed(() => menuStore.getItemsByCategory(menuStore.activeCategory))

const orderItems = computed(() => {
  const items = currentOrder.value?.items ?? []
  return items.filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })
})

// Instant items (kot_round = 0)
const instantItems = computed(() =>
  orderItems.value.filter(i => i.kot_round === 0)
)

// Kitchen items sent (kot_round > 0)
const sentItems = computed(() =>
  orderItems.value.filter(i => i.kot_round && i.kot_round > 0)
)

// Unsent kitchen items (kot_round is null/undefined)
const unsentItems = computed(() =>
  orderItems.value.filter(i => i.kot_round === null || i.kot_round === undefined)
)

// Sent rounds grouped
const sentRounds = computed(() => {
  const groups = {}
  sentItems.value.forEach(item => {
    const r = item.kot_round
    if (!groups[r]) {
      groups[r] = {
        number:   r,
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

// ── Status helpers ──────────────────────────────────────
function statusColor(status) {
  return {
    pending:   '#F59E0B',
    preparing: '#3B82F6',
    ready:     '#10B981',
    served:    '#64748B',
  }[status] ?? '#64748B'
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
  return {
    pending:   '🟡 Pending',
    preparing: '🔵 Preparing',
    ready:     '🟢 Ready',
    served:    '⚫ Served',
  }[status] ?? status
}

// ── Actions ────────────────────────────────────────────
async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return

  // ✅ Open modifier selector if item has modifier groups
  const hasModifiers = menuItem.modifier_groups && menuItem.modifier_groups.length > 0
  if (hasModifiers) {
    modifierItem.value = menuItem
    return
  }

  // No modifiers — add directly
  try {
    const existing = orderItems.value.find(i => {
      if (menuItem.is_instant) {
        return i.menu_item_id === menuItem.id && i.kot_round === 0
      }
      return i.menu_item_id === menuItem.id && !i.kot_round
    })

    if (existing) {
      await orderStore.updateItemQty(currentOrder.value.id, existing.id, existing.quantity + 1)
    } else {
      await orderStore.addItem(currentOrder.value.id, {
        menu_item_id: menuItem.id,
        quantity:     1,
        is_instant:   menuItem.is_instant,
      })
    }

    if (menuItem.is_instant) {
      showToast(`${menuItem.name} added ⚡`, 'success')
    }
  } catch (e) {
    showToast('Failed to add: ' + (e.response?.data?.message ?? e.message), 'error')
  }
}

// ✅ Called when modifier selector confirms
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
    showToast('Failed to update', 'error')
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
    showToast('Failed to update', 'error')
  }
}

async function voidOrderItem(item) {
  try {
    await orderStore.voidItem(currentOrder.value.id, item.id)
    showToast('Item removed', 'success')
  } catch (e) {
    showToast('Failed to remove', 'error')
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
  }
}

async function updateCustomerName() {
  if (!currentOrder.value) return
  try {
    await axios.patch(`/orders/${currentOrder.value.id}/customer`, {
      customer_name: customerName.value || 'Walk-in',
    })
  } catch (e) {
    console.warn('Failed to update customer name:', e)
  }
}

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

async function onPaid() {
  showPayment.value = false
  orderStore.clearOrder()
  router.push('/')
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// Load pending direct orders from localStorage
async function loadPendingDirectOrders() {
  try {
    const savedOrder = localStorage.getItem('pos_current_order')
    if (savedOrder) {
      const orderData = JSON.parse(savedOrder)
      if (!orderData.table_id) {
        console.log('Manually loading pending direct order:', orderData.order_number)
        
        // Load the order from server to get fresh data
        await orderStore.fetchOrder(orderData.id)
        showToast(`Loaded order ${orderData.order_number} ✓`, 'success')
      } else {
        showToast('No pending direct orders found', 'info')
      }
    } else {
      showToast('No pending orders in storage', 'info')
    }
  } catch (e) {
    console.error('Failed to load pending orders:', e)
    showToast('Failed to load pending orders', 'error')
  }
}

// ── Lifecycle ───────────────────────────────────────────
onMounted(async () => {
  loading.value = true
  
  // Check if there's an existing direct order in localStorage
  const savedOrder = localStorage.getItem('pos_current_order')
  let hasExistingOrder = false
  
  if (savedOrder) {
    try {
      const orderData = JSON.parse(savedOrder)
      // Only load if it's a direct order (no table_id)
      if (!orderData.table_id) {
        console.log('Loading existing direct order from storage:', orderData.order_number)
        orderStore.setOrder(orderData)
        hasExistingOrder = true
        
        // Fetch fresh data from server to ensure it's up to date
        try {
          await orderStore.fetchOrder(orderData.id)
        } catch (e) {
          console.warn('Failed to fetch fresh order data, using cached:', e)
        }
      } else {
        // Clear if it's a table order
        orderStore.clearOrder()
      }
    } catch (e) {
      console.warn('Failed to load saved order:', e)
      orderStore.clearOrder()
    }
  } else {
    orderStore.clearOrder()
  }

  try {
    await Promise.all([
      menuStore.fetchMenu(),
      // Only create new order if we don't have an existing one
      hasExistingOrder ? Promise.resolve() : orderStore.createOrder({
        table_id:      null,
        type:          selectedType.value,
        customer_name: customerName.value,
      }),
    ])
  } catch (e) {
    showToast('Failed to start order: ' + (e.response?.data?.message ?? e.message), 'error')
  } finally {
    loading.value = false
  }
})
</script>