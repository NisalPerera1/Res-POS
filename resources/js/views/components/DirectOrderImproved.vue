<template>
  <div style="display:flex; flex-direction:column; height:100%; background:var(--bg-primary);
              overflow:hidden; font-family:'DM Sans',system-ui,-apple-system,sans-serif;">

    <!-- ── TOP BAR ── -->
    <div style="display:flex; align-items:center; justify-content:space-between;
                padding:10px 12px; border-bottom:1px solid var(--border-color);
                background:var(--bg-secondary); flex-shrink:0; gap:8px;">

      <button @click="$router.push({ name: 'tables' })"
        style="font-size:13px; color:var(--text-secondary); background:var(--bg-tertiary);
               border:1px solid var(--border-color); cursor:pointer; padding:8px 12px;
               border-radius:8px; min-height:40px; white-space:nowrap;
               -webkit-tap-highlight-color:transparent; touch-action:manipulation; flex-shrink:0;">
        ← Back
      </button>

      <div style="text-align:center; min-width:0; flex:1;">
        <div style="font-weight:700; font-size:15px; color:var(--text-primary);
                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
          Direct Order
        </div>
        <div style="font-size:11px; color:var(--text-secondary); margin-top:1px;">
          {{ currentOrder?.order_number ?? 'No active order' }}
        </div>
      </div>

      <!-- Mobile panel tabs -->
      <div class="mobile-tabs" style="display:none; gap:4px; flex-shrink:0;">
        <button
          v-for="tab in mobileTabs" :key="tab.value"
          @click="mobileView = tab.value"
          style="position:relative; padding:6px 10px; border-radius:8px; font-size:11px;
                 font-weight:700; border:1px solid var(--border-color); cursor:pointer;
                 min-height:36px; -webkit-tap-highlight-color:transparent;
                 touch-action:manipulation; white-space:nowrap;"
          :style="{
            background:  mobileView === tab.value ? 'var(--accent-color)' : 'var(--bg-tertiary)',
            color:       mobileView === tab.value ? '#000' : 'var(--text-secondary)',
            borderColor: mobileView === tab.value ? 'var(--accent-color)' : 'var(--border-color)',
          }"
        >
          {{ tab.icon }}
          <span v-if="tab.value === 'kot' && pendingKotOrders.length > 0"
            style="position:absolute; top:-4px; right:-4px; background:var(--success-color);
                   color:#fff; border-radius:50%; width:16px; height:16px; font-size:9px;
                   font-weight:700; display:flex; align-items:center; justify-content:center;">
            {{ pendingKotOrders.length }}
          </span>
          <span v-if="tab.value === 'cart' && unsentItems.length > 0"
            style="position:absolute; top:-4px; right:-4px; background:var(--accent-color);
                   color:#000; border-radius:50%; width:16px; height:16px; font-size:9px;
                   font-weight:700; display:flex; align-items:center; justify-content:center;">
            {{ unsentItems.length }}
          </span>
        </button>
      </div>

      <!-- Pending Orders Button -->
      <button @click="showPendingOrdersModal = true"
        style="display:flex; align-items:center; gap:6px; padding:8px 12px; background:var(--bg-tertiary);
               border:1px solid var(--border-color); border-radius:8px; color:var(--text-secondary);
               cursor:pointer; font-size:12px; font-weight:600; min-height:40px; white-space:nowrap;
               -webkit-tap-highlight-color:transparent; touch-action:manipulation; flex-shrink:0;"
        :style="{
          borderColor: pendingKotOrders.length > 0 ? '#F59E0B' : 'var(--border-color)',
          background:  pendingKotOrders.length > 0 ? 'rgba(245,158,11,0.1)' : 'var(--bg-tertiary)',
          color:      pendingKotOrders.length > 0 ? '#F59E0B' : 'var(--text-secondary)',
        }">
        <span>Orders</span>
        <span v-if="pendingKotOrders.length > 0"
          style="background:#F59E0B; color:#000; border-radius:50%; width:18px; height:18px;
                 font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center;">
          {{ pendingKotOrders.length }}
        </span>
      </button>

      <!-- Desktop: order type buttons -->
      <div class="desktop-order-types" style="display:flex; gap:4px; flex-shrink:0;">
        <button
          v-for="t in orderTypes" :key="t.value"
          @click="updateOrderType(t.value)"
          style="padding:6px 10px; border-radius:6px; font-size:11px; font-weight:600;
                 border:1px solid var(--border-color); cursor:pointer; min-height:36px;
                 -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
          :style="{
            background:  selectedType === t.value ? t.color : 'transparent',
            color:       selectedType === t.value ? '#000'  : '#64748B',
            borderColor: selectedType === t.value ? t.color : '#252B38',
          }"
        >{{ t.label }}</button>
        
        <!-- Start New Order Button -->
        <button @click="createNewDirectOrder"
          style="padding:6px 12px; border-radius:6px; font-size:11px; font-weight:600;
                 border:1px solid var(--border-color); cursor:pointer; min-height:36px;
                 background:linear-gradient(135deg,#10B981,#059669); color:#fff; border:none;
                 -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
          + New Order
        </button>
      </div>
    </div>

    <!-- ── MOBILE: Order type row ── -->
    <div class="mobile-order-types"
      style="display:none; gap:4px; padding:8px 12px; border-bottom:1px solid #252B38;
             background:#12151C; flex-shrink:0; overflow-x:auto; -webkit-overflow-scrolling:touch;
             scrollbar-width:none;">
      <button
        v-for="t in orderTypes" :key="t.value"
        @click="updateOrderType(t.value)"
        style="padding:7px 12px; border-radius:6px; font-size:12px; font-weight:600;
               border:1px solid var(--border-color); cursor:pointer; min-height:36px;
               white-space:nowrap; flex-shrink:0; -webkit-tap-highlight-color:transparent;
               touch-action:manipulation;"
        :style="{
          background:  selectedType === t.value ? t.color : 'transparent',
          color:       selectedType === t.value ? '#000'  : '#64748B',
          borderColor: selectedType === t.value ? t.color : '#252B38',
        }"
      >{{ t.label }}</button>
    </div>

    <!-- ── BODY ── -->
    <div style="flex:1; display:flex; overflow:hidden; min-height:0;">

      <!-- ── LEFT: Menu Panel ── -->
      <div class="panel menu-panel"
        :class="{ 'panel-active': mobileView === 'menu' }"
        style="flex:1; display:flex; flex-direction:column; border-right:1px solid #252B38;
               overflow:hidden; min-width:0;">

        <!-- Category Bar -->
        <div
          style="display:flex; gap:6px; padding:8px 12px; border-bottom:1px solid #252B38;
                 overflow-x:auto; flex-shrink:0; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
          <button
            v-for="cat in menuStore.categories" :key="cat.id"
            @click="menuStore.setActiveCategory(cat.id)"
            style="padding:7px 13px; border-radius:20px; font-size:12px; font-weight:600;
                   border:1px solid var(--border-color); white-space:nowrap; cursor:pointer;
                   flex-shrink:0; min-height:34px; -webkit-tap-highlight-color:transparent;
                   touch-action:manipulation; transition:all 0.15s;"
            :style="{
              background:  menuStore.activeCategory === cat.id ? '#F59E0B' : 'transparent',
              color:       menuStore.activeCategory === cat.id ? '#000'    : '#64748B',
              borderColor: menuStore.activeCategory === cat.id ? '#F59E0B' : '#252B38',
            }"
          >{{ cat.name }}</button>
        </div>

        <!-- Search Bar -->
        <div v-if="currentOrder"
          style="padding:8px 12px; border-bottom:1px solid #252B38; flex-shrink:0;">
          <div style="display:flex; gap:8px; align-items:center;">
            <div style="flex:1; position:relative;">
              <input
                v-model="searchQuery"
                @input="onSearchInput"
                @keydown.enter="onSearchEnter"
                placeholder="Search menu items..."
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
        </div>

        <!-- Menu Items Grid -->
        <div v-if="currentOrder"
          style="flex:1; overflow-y:auto; padding:10px; -webkit-overflow-scrolling:touch;">

          <div v-if="loading || menuStore.loading"
            style="display:flex; align-items:center; justify-content:center;
                   height:100%; color:var(--text-secondary);">
            <span style="font-size:13px;">Loading menu...</span>
          </div>

          <div v-else class="menu-grid">
            <!-- Search Results Message -->
            <div v-if="searchQuery && currentItems.length === 0"
              style="text-align:center; padding:40px 20px; color:var(--text-secondary);">
              <div style="font-size:24px; opacity:0.2; margin-bottom:12px;">🔍</div>
              <div style="font-size:14px; margin-bottom:4px;">No items found</div>
              <div style="font-size:12px; color:var(--text-muted);">
                Try different keywords or clear search to see all items
              </div>
            </div>

            <button
              v-for="item in currentItems" :key="item.id"
              @click="addItem(item)"
              :disabled="!item.is_available"
              class="menu-item-card"
              :style="{ opacity: item.is_available ? '1' : '0.4' }"
            >
              <!-- Badges -->
              <div style="position:absolute; top:4px; right:4px; display:flex; flex-direction:column; gap:2px; z-index:1;">
                <span v-if="item.is_instant"
                  style="font-size:8px; font-weight:700; background:rgba(16,185,129,0.9);
                         color:#fff; padding:1px 4px; border-radius:3px; line-height:1.4;">
                  ⚡
                </span>
              </div>
              <div v-if="item.modifier_groups?.length > 0"
                style="position:absolute; top:4px; left:4px; z-index:1;">
                <span style="font-size:8px; font-weight:700; background:rgba(139,92,246,0.9);
                             color:#fff; padding:1px 4px; border-radius:3px; line-height:1.4;">⚙</span>
              </div>

              <!-- Image -->
              <div class="item-img-wrap">
                <img
                  v-if="item.image"
                  :src="getImageUrl(item.image)"
                  :alt="item.name"
                  style="width:100%; height:100%; object-fit:cover; border-radius:6px;"
                  @error="e => e.target.style.display='none'"
                />
                <div v-else
                  style="width:100%; height:100%; display:flex; align-items:center;
                         justify-content:center; font-size:22px; color:#475569;
                         background:#1A1E28; border-radius:6px;">
                  🍽️
                </div>
              </div>

              <!-- Info -->
              <div style="padding:5px 2px 0;">
                <div style="font-size:12px; font-weight:600; color:var(--text-primary);
                            line-height:1.3; text-align:left; white-space:nowrap;
                            overflow:hidden; text-overflow:ellipsis;"
                  v-html="highlightText(item.name, searchQuery)">
                </div>
                <div style="font-size:12px; font-weight:700; margin-top:2px; text-align:left;"
                  :style="{ color: item.is_instant ? '#10B981' : '#F59E0B' }">
                  Rs.{{ Number(item.price).toFixed(0) }}
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- No Active Order -->
        <div v-else
          style="flex:1; display:flex; flex-direction:column; align-items:center;
                 justify-content:center; gap:16px; padding:32px;">
          <div style="font-size:48px; opacity:0.1;">⚡</div>
          <div style="text-align:center;">
            <div style="font-size:16px; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
              No Active Order
            </div>
            <div style="font-size:12px; color:var(--text-secondary);">
              Create a new order or switch to a pending one
            </div>
          </div>
          <button @click="createNewDirectOrder"
            style="padding:14px 28px; background:linear-gradient(135deg,#10B981,#059669);
                   color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700;
                   cursor:pointer; min-height:50px; -webkit-tap-highlight-color:transparent;
                   touch-action:manipulation;">
            + Create New Order
          </button>
        </div>
      </div>

      <!-- ── MIDDLE: KOT Orders Panel ── -->
      <div class="panel kot-panel"
        :class="{ 'panel-active': mobileView === 'kot' }"
        style="width:210px; display:flex; flex-direction:column; background:var(--bg-primary);
               border-right:1px solid var(--border-color); flex-shrink:0;">

        <div style="padding:12px 12px 10px; border-bottom:1px solid #252B38;
                    background:var(--bg-secondary); flex-shrink:0;">
          <div style="font-size:10px; font-weight:700; color:var(--text-secondary);
                      text-transform:uppercase; letter-spacing:0.06em;">
            KOT Orders
          </div>
        </div>

        <div style="flex:1; overflow-y:auto; padding:8px; -webkit-overflow-scrolling:touch;">
          <div v-if="loadingPending"
            style="display:flex; align-items:center; justify-content:center;
                   height:80px; color:#475569; font-size:11px; gap:6px;">
            <span style="opacity:0.5;">⏳</span> Loading...
          </div>

          <div v-else-if="pendingKotOrders.length === 0"
            style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                   height:120px; color:#334155; gap:8px; text-align:center; padding:0 8px;">
            <span style="font-size:28px; opacity:0.2;">🍳</span>
            <span style="font-size:11px; color:#475569; line-height:1.5;">No KOT orders<br>in progress</span>
          </div>

          <div v-else style="display:flex; flex-direction:column; gap:7px;">
            <div
              v-for="order in pendingKotOrders" :key="order.id"
              @click="switchToOrder(order)"
              style="background:var(--bg-tertiary); border:1px solid var(--border-color);
                     border-radius:10px; padding:10px 11px; cursor:pointer; position:relative;
                     border-left:3px solid transparent; transition:background 0.15s;
                     -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
              :style="{
                borderLeftColor: order.id === currentOrder?.id ? '#F59E0B' : 'transparent',
                background:      order.id === currentOrder?.id ? '#1E2535' : '#1A1E28',
              }"
            >
              <div v-if="order.id === currentOrder?.id"
                style="position:absolute; top:8px; right:8px; font-size:8px; font-weight:700;
                       background:rgba(245,158,11,0.15); color:#F59E0B; padding:1px 5px; border-radius:4px;">
                ACTIVE
              </div>
              <div v-else
                style="position:absolute; top:10px; right:10px; width:6px; height:6px;
                       border-radius:50%; background:#10B981;
                       box-shadow:0 0 0 2px rgba(16,185,129,0.2);">
              </div>

              <div style="font-size:12px; font-weight:700; color:var(--text-primary);
                          margin-bottom:3px; padding-right:14px;">
                {{ order.order_number }}
              </div>
              <div style="margin-bottom:5px;">
                <span style="font-size:9px; font-weight:700; padding:1px 6px; border-radius:4px;
                             text-transform:uppercase; letter-spacing:0.04em;"
                  :style="getTypeBadgeStyle(order.type)">
                  {{ order.type?.replace('_', ' ') }}
                </span>
              </div>
              <div style="font-size:11px; color:#94A3B8; margin-bottom:5px; white-space:nowrap;
                          overflow:hidden; text-overflow:ellipsis;">
                👤 {{ order.customer_name || 'Walk-in' }}
              </div>
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                <span style="font-size:10px; color:var(--text-secondary);">
                  🛒 {{ order.items?.length || 0 }} item(s)
                </span>
                <span style="font-size:12px; font-weight:700; color:#F59E0B;">
                  Rs.{{ parseFloat(order.total || 0).toFixed(0) }}
                </span>
              </div>
              <div style="display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:9px; color:#F59E0B; font-weight:600;">🍳 KOT Sent</span>
                <span style="font-size:10px; color:#475569;">{{ formatTime(order.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="pendingKotOrders.length > 0"
          style="padding:7px 12px; border-top:1px solid #252B38; background:#12151C; flex-shrink:0;">
          <div style="font-size:10px; color:#475569; text-align:center;">
            {{ pendingKotOrders.length }} order(s) in progress
          </div>
        </div>
      </div>

      <!-- ── RIGHT: Cart Panel ── -->
      <div v-if="currentOrder"
        class="panel cart-panel"
        :class="{ 'panel-active': mobileView === 'cart' }"
        style="width:300px; display:flex; flex-direction:column; background:#12151C; flex-shrink:0;">

        <!-- Customer Name -->
        <div style="padding:5px 12px; border-bottom:1px solid #252B38; flex-shrink:0;">
          <div style="font-size:10px; color:var(--text-secondary); margin-bottom:5px; font-weight:600;
                      text-transform:uppercase; letter-spacing:0.06em;">Customer</div>
          <input
            v-model="customerName"
            placeholder="Walk-in / Name (optional)"
            style="width:100%; background:var(--bg-tertiary); border:1px solid var(--border-color);
                   border-radius:7px; padding:10px 11px; color:var(--text-primary); font-size:16px;
                   outline:none; font-family:inherit; box-sizing:border-box;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => { e.target.style.borderColor='#252B38'; updateCustomerName() }"
          />
        </div>

        <!-- Cart Header -->
        <div style="padding:10px 12px; border-bottom:1px solid #252B38; flex-shrink:0;">
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:14px; color:var(--text-primary);">Order Cart</div>
              <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">
                {{ totalItemCount }} item(s) ·
                <span style="color:#F59E0B;">Round {{ currentRound }}</span>
              </div>
            </div>
            <button @click="refreshOrder"
              style="background:var(--bg-tertiary); border:1px solid var(--border-color);
                     border-radius:6px; padding:6px 8px; color:var(--text-secondary);
                     font-size:11px; cursor:pointer; min-height:36px;
                     -webkit-tap-highlight-color:transparent;">
              🔄
            </button>
          </div>
        </div>

        <!-- Cart Items -->
        <div style="flex:1; overflow-y:auto; padding:10px; padding-bottom:16px;
                    display:flex; flex-direction:column; gap:0; -webkit-overflow-scrolling:touch;">

          <div v-if="orderItems.length === 0"
            style="display:flex; flex-direction:column; align-items:center;
                   justify-content:center; height:100%; color:var(--text-secondary); gap:8px;">
            <span style="font-size:40px; opacity:0.2;">⚡</span>
            <span style="font-size:13px;">Add items to start</span>
            <span style="font-size:11px; color:#334155;">Tap menu items to add</span>
          </div>

          <template v-else>
            <!-- Unsent Items (moved to top for easier access) -->
            <div v-if="unsentItems.length > 0">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                            letter-spacing:0.07em; color:#F59E0B; white-space:nowrap;">
                  ✦ New Items
                </div>
                <div style="flex:1; height:1px; background:rgba(245,158,11,0.3);"></div>
                <div style="font-size:10px; color:#F59E0B; white-space:nowrap;">Not sent yet</div>
              </div>

              <div v-for="item in unsentItems" :key="item.id"
                style="border-radius:8px; padding:10px 11px; margin-bottom:4px;
                       background:var(--bg-tertiary); border:1.5px solid rgba(245,158,11,0.25);
                       border-left:3px solid #F59E0B;">
                <div style="display:flex; align-items:start; justify-content:space-between; gap:8px;">
                  <div style="flex:1; min-width:0;">
                    <div style="font-size:12px; font-weight:600; color:var(--text-primary);">
                      {{ item.item_name }}
                    </div>
                    <div v-if="item.notes"
                      style="font-size:10px; color:var(--text-secondary); margin-top:2px;">
                      📝 {{ item.notes }}
                    </div>
                  </div>
                  <div style="font-size:13px; font-weight:700; color:#F59E0B; flex-shrink:0;">
                    Rs.{{ parseFloat(item.total_price).toFixed(2) }}
                  </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px; margin-top:9px;">
                  <button @click="decreaseQty(item)"
                    style="width:34px; height:34px; border-radius:6px; border:1px solid var(--border-color);
                           background:var(--bg-tertiary); color:var(--text-primary); font-size:18px;
                           cursor:pointer; display:flex; align-items:center; justify-content:center;
                           flex-shrink:0; -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
                    −
                  </button>
                  <span style="font-size:13px; font-weight:700; min-width:24px;
                               text-align:center; color:var(--text-primary);">
                    {{ item.quantity }}
                  </span>
                  <button @click="increaseQty(item)"
                    style="width:34px; height:34px; border-radius:6px; border:1px solid var(--border-color);
                           background:var(--bg-tertiary); color:var(--text-primary); font-size:18px;
                           cursor:pointer; display:flex; align-items:center; justify-content:center;
                           flex-shrink:0; -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
                    +
                  </button>
                  <button @click="openNotes(item)"
                    style="padding:6px 10px; border-radius:5px; border:1px solid var(--border-color);
                           background:transparent; color:var(--text-secondary); font-size:11px;
                           cursor:pointer; min-height:34px; -webkit-tap-highlight-color:transparent;
                           touch-action:manipulation;">
                    📝 Note
                  </button>
                  <button @click="openAddons(item)"
                    style="padding:6px 10px; border-radius:5px; border:1px solid var(--accent-color);
                           background:rgba(245,158,11,0.1); color:var(--accent-color); font-size:11px;
                           font-weight:600; cursor:pointer; min-height:34px; -webkit-tap-highlight-color:transparent;
                           touch-action:manipulation;">
                    +Extra
                  </button>
                  <button @click="voidOrderItem(item)"
                    style="margin-left:auto; background:none; border:none; color:#EF4444;
                           font-size:13px; cursor:pointer; padding:6px 8px; min-height:34px;
                           min-width:34px; -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
                    ✕
                  </button>
                </div>
              </div>

              <button @click="sendKOT"
                style="width:100%; margin-top:6px; padding:13px; border-radius:9px;
                       background:rgba(59,130,246,0.12); color:#3B82F6;
                       border:1px solid rgba(59,130,246,0.35); font-size:13px; font-weight:700;
                       cursor:pointer; min-height:48px; display:flex; align-items:center;
                       justify-content:center; gap:6px; -webkit-tap-highlight-color:transparent;
                       touch-action:manipulation;">
                📋 Send {{ unsentItems.length }} item(s) to Kitchen
              </button>
            </div>

            <!-- Sent Rounds -->
            <div v-for="round in sentRounds" :key="'r' + round.number" style="margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px;">
                <div style="font-size:10px; font-weight:700; text-transform:uppercase;
                            letter-spacing:0.07em; color:var(--text-secondary); white-space:nowrap;">
                  Round {{ round.number }}
                </div>
                <div style="flex:1; height:1px; background:var(--border-color);"></div>
                <div style="font-size:10px; color:var(--text-secondary); white-space:nowrap; font-family:monospace;">
                  {{ round.sentTime }}
                </div>
              </div>

              <div v-for="item in round.items" :key="item.id"
                style="border-radius:8px; padding:9px 11px; margin-bottom:4px;
                       background:var(--bg-tertiary); border:1px solid var(--border-color);
                       border-left-width:3px;"
                :style="{ borderLeftColor: statusColor(item.status) }">
                <div style="display:flex; align-items:center; gap:8px;">
                  <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
                    :style="{ background: statusColor(item.status) }"></div>
                  <div style="flex:1; font-size:12px; font-weight:500; color:var(--text-primary);
                              white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ item.item_name }}
                  </div>
                  <div style="font-size:11px; font-weight:700; padding:1px 6px; border-radius:4px;"
                    :style="{ background: statusBg(item.status), color: statusColor(item.status) }">
                    ×{{ item.quantity }}
                  </div>
                  <div style="font-size:11px; color:var(--text-secondary); min-width:44px; text-align:right;">
                    Rs.{{ parseFloat(item.total_price).toFixed(2) }}
                  </div>
                </div>
                <div v-if="item.selected_modifiers?.length > 0"
                  style="display:flex; flex-wrap:wrap; gap:3px; margin-top:5px; padding-left:16px;">
                  <span v-for="mod in item.selected_modifiers" :key="mod.id"
                    style="font-size:9px; padding:1px 6px; border-radius:4px;
                           background:rgba(100,116,139,0.12); color:var(--text-secondary);">
                    {{ mod.name }}
                    <span v-if="mod.pivot?.price_adjustment > 0" style="color:#F59E0B;">
                      +Rs.{{ parseFloat(mod.pivot.price_adjustment).toFixed(2) }}
                    </span>
                  </span>
                </div>
                <div v-if="item.notes"
                  style="font-size:10px; color:var(--text-secondary); margin-top:3px; padding-left:16px;">
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
                    style="font-size:10px; padding:4px 10px; border-radius:4px; border:none;
                           cursor:pointer; font-weight:600; min-height:30px;
                           -webkit-tap-highlight-color:transparent; touch-action:manipulation;"
                    :style="{
                      background: item.status === 'preparing' ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)',
                      color:      item.status === 'preparing' ? '#10B981' : '#64748B',
                    }">
                    {{ item.status === 'preparing' ? '✓ Mark Ready' : '✓ Served' }}
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Totals -->
        <div style="padding:10px 14px 12px; border-top:1px solid #252B38; flex-shrink:0;">
          <div style="display:flex; justify-content:space-between; font-size:12px;
                      color:var(--text-secondary); margin-bottom:3px;">
            <span>Subtotal</span>
            <span>Rs.{{ parseFloat(currentOrder?.subtotal ?? 0).toFixed(2) }}</span>
          </div>
          <div v-if="currentOrder?.tax_rate > 0"
            style="display:flex; justify-content:space-between; font-size:12px;
                   color:var(--text-secondary); margin-bottom:3px;">
            <span>Tax ({{ currentOrder?.tax_rate ?? 0 }}%)</span>
            <span>Rs.{{ parseFloat(currentOrder?.tax_amount ?? 0).toFixed(2) }}</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-weight:700; font-size:15px;
                      border-top:1px solid #252B38; padding-top:8px; margin-top:6px;">
            <span style="color:var(--text-primary);">Total</span>
            <span style="color:#F59E0B;">Rs.{{ parseFloat(currentOrder?.total ?? 0).toFixed(2) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="padding:10px 12px; border-top:1px solid #252B38; flex-shrink:0;
                    display:flex; flex-direction:column; gap:8px;
                    padding-bottom:max(20px, env(safe-area-inset-bottom, 20px));">

          <div v-if="unsentItems.length > 0 && sentItems.length > 0"
            style="font-size:11px; color:#F59E0B; text-align:center; padding:5px 10px;
                   background:rgba(245,158,11,0.08); border-radius:6px;
                   border:1px solid rgba(245,158,11,0.2);">
            ⚠️ {{ unsentItems.length }} item(s) not sent to kitchen
          </div>

          <button v-if="unsentItems.length > 0"
            @click="sendKOT"
            style="width:100%; padding:13px; background:linear-gradient(135deg,#F59E0B,#D97706);
                   color:#000; border:none; border-radius:9px; font-size:14px; font-weight:700;
                   cursor:pointer; min-height:50px; display:flex; align-items:center;
                   justify-content:center; gap:6px; -webkit-tap-highlight-color:transparent;
                   touch-action:manipulation;">
            🍳 Send KOT ({{ unsentItems.length }} items)
          </button>

          <button
            @click="orderItems.length > 0 ? openDirectPayment() : null"
            style="width:100%; padding:14px; background:linear-gradient(135deg,#10B981,#059669);
                   color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:700;
                   cursor:pointer; min-height:52px; display:flex; align-items:center;
                   justify-content:center; gap:6px; -webkit-tap-highlight-color:transparent;
                   touch-action:manipulation; transition:opacity 0.2s;"
            :style="{ opacity: orderItems.length > 0 ? '1' : '0.45',
                      cursor:  orderItems.length > 0 ? 'pointer' : 'not-allowed' }">
            💳 Charge Rs.{{ parseFloat(currentOrder?.total ?? 0).toFixed(2) }}
          </button>

          <button v-if="!hasKotItems()"
            @click="deleteCurrentOrder"
            style="width:100%; padding:10px; background:transparent; color:#EF4444;
                   border:1px solid rgba(239,68,68,0.25); border-radius:8px; font-size:12px;
                   font-weight:600; cursor:pointer; min-height:42px;
                   -webkit-tap-highlight-color:transparent; touch-action:manipulation;">
            🗑️ Delete Order
          </button>
        </div>
      </div>

      <!-- No-order placeholder (cart area) -->
      <div v-else class="panel cart-panel"
        :class="{ 'panel-active': mobileView === 'cart' }"
        style="width:300px; background:#12151C; flex-shrink:0; display:flex;
               align-items:center; justify-content:center;">
        <div style="text-align:center; color:#334155; padding:24px;">
          <div style="font-size:32px; opacity:0.2; margin-bottom:8px;">🧾</div>
          <div style="font-size:12px;">Create an order first</div>
        </div>
      </div>

    </div><!-- end body -->

    <!-- ══ DIRECT ORDER PAYMENT MODAL ══ -->
    <Teleport to="body">
      <div v-if="directPayment.show"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50; padding:16px;">

        <div style="background:var(--bg-tertiary); border:1px solid var(--border-color);
                    border-radius:16px; width:420px; max-width:94vw; max-height:92vh;
                    overflow:hidden; display:flex; flex-direction:column;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">

          <!-- Payment Form -->
          <template v-if="!directPayment.paid">

            <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                        display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
              <div style="font-size:17px; font-weight:700; color:var(--text-primary);">💳 Payment</div>
              <button @click="directPayment.show = false"
                style="width:30px; height:30px; background:transparent; border:none;
                       color:var(--text-secondary); cursor:pointer; border-radius:6px;
                       font-size:18px; display:flex; align-items:center; justify-content:center;">
                ×
              </button>
            </div>

            <div style="flex:1; overflow-y:auto; padding:16px 20px;">

              <!-- Order info -->
              <div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
                <div style="background:#12151C; border:1px solid var(--border-color); border-radius:8px;
                            padding:8px 12px; flex:1; min-width:80px;">
                  <div style="font-size:10px; color:var(--text-secondary); margin-bottom:2px;">Order</div>
                  <div style="font-size:12px; font-weight:700; color:var(--text-primary); font-family:monospace;">
                    {{ directPayment.orderNumber }}
                  </div>
                </div>
                <div v-if="directPayment.customerName"
                  style="background:#12151C; border:1px solid var(--border-color); border-radius:8px;
                         padding:8px 12px; flex:1; min-width:80px; overflow:hidden;">
                  <div style="font-size:10px; color:var(--text-secondary); margin-bottom:2px;">Customer</div>
                  <div style="font-size:12px; font-weight:700; color:var(--text-primary); white-space:nowrap;
                               overflow:hidden; text-overflow:ellipsis;">
                    {{ directPayment.customerName }}
                  </div>
                </div>
              </div>

              <!-- Items list (collapsible) -->
              <div style="background:#12151C; border:1px solid var(--border-color); border-radius:10px;
                          margin-bottom:14px; overflow:hidden;">
                <div @click="directPayment.showItems = !directPayment.showItems"
                  style="padding:10px 14px; display:flex; align-items:center;
                         justify-content:space-between; cursor:pointer; user-select:none;">
                  <div style="font-size:12px; font-weight:600; color:var(--text-primary);">
                    Order Items ({{ directPayment.snapshotItems.length }})
                  </div>
                  <div style="font-size:12px; color:var(--text-secondary); transition:transform 0.2s;"
                    :style="{ transform: directPayment.showItems ? 'rotate(180deg)' : 'rotate(0deg)' }">▾</div>
                </div>
                <div v-if="directPayment.showItems" style="border-top:1px solid #252B38; padding:10px 14px;">
                  <div v-for="item in directPayment.snapshotItems" :key="item.id" style="margin-bottom:7px;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                      <div style="flex:1; min-width:0;">
                        <div style="font-size:12px; color:#CBD5E1;">×{{ item.quantity }} {{ item.item_name }}</div>
                        <div v-if="item.notes"
                          style="font-size:10px; color:var(--text-secondary); margin-top:1px; font-style:italic;">
                          📝 {{ item.notes }}
                        </div>
                      </div>
                      <div style="font-size:12px; color:#94A3B8; margin-left:8px; flex-shrink:0;">
                        Rs.{{
                          (parseFloat(item.total_price || 0) + 
                          (item.addons ? item.addons.reduce((sum, addon) => sum + parseFloat(addon.total_price || 0), 0) : 0)
                          ).toFixed(2)
                        }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Totals summary -->
              <div style="background:#12151C; border:1px solid var(--border-color); border-radius:10px;
                          padding:14px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                  <span style="font-size:12px; color:var(--text-secondary);">Subtotal</span>
                  <span style="font-size:12px; color:#94A3B8;">
                    Rs.{{ directPayment.snapshotSubtotal.toFixed(2) }}
                  </span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                  <span style="font-size:12px; color:var(--text-secondary);">Service Charge</span>
                  <span style="font-size:12px; color:#10B981;">Waived (Direct Order)</span>
                </div>
                <div style="display:flex; justify-content:space-between; padding-top:8px;
                            border-top:1px solid #252B38; margin-top:4px;">
                  <span style="font-size:15px; font-weight:700; color:var(--text-primary);">Total</span>
                  <span style="font-size:20px; font-weight:700; color:#F59E0B;">
                    Rs.{{ directPayment.snapshotTotal.toFixed(2) }}
                  </span>
                </div>
              </div>

              <!-- Payment Method -->
              <div style="margin-bottom:14px;">
                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase;
                            letter-spacing:0.06em; margin-bottom:8px; font-weight:600;">
                  Payment Method
                </div>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                  <button
                    v-for="m in directPaymentMethods" :key="m.value"
                    @click="directPayment.selectedMethod = m.value"
                    style="border-radius:10px; padding:12px 6px; text-align:center;
                           cursor:pointer; transition:all 0.15s; border:2px solid;"
                    :style="{
                      borderColor: directPayment.selectedMethod === m.value ? '#F59E0B' : '#252B38',
                      background:  directPayment.selectedMethod === m.value
                                   ? 'rgba(245,158,11,0.1)' : '#12151C',
                    }">
                    <div style="font-size:22px; margin-bottom:4px;">{{ m.icon }}</div>
                    <div style="font-size:11px; font-weight:600;"
                      :style="{ color: directPayment.selectedMethod === m.value ? '#F59E0B' : '#64748B' }">
                      {{ m.label }}
                    </div>
                  </button>
                </div>
              </div>

              <!-- Cash tendered -->
              <div v-if="directPayment.selectedMethod === 'cash'" style="margin-bottom:14px;">
                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase;
                            letter-spacing:0.06em; margin-bottom:8px; font-weight:600;">
                  Cash Tendered
                </div>
                <div style="display:flex; gap:5px; margin-bottom:8px; flex-wrap:wrap;">
                  <button
                    v-for="amt in directPayment.quickAmounts" :key="amt"
                    @click="directPayment.tendered = amt.toString()"
                    style="padding:5px 10px; border-radius:6px; border:1px solid;
                           background:#12151C; font-size:12px; cursor:pointer; font-weight:500;"
                    :style="{
                      borderColor: parseFloat(directPayment.tendered) === amt ? '#F59E0B' : '#252B38',
                      color:       parseFloat(directPayment.tendered) === amt ? '#F59E0B' : '#94A3B8',
                    }">
                    Rs.{{ amt }}
                  </button>
                </div>
                <input
                  v-model="directPayment.tendered"
                  type="number"
                  step="0.01"
                  :placeholder="directPayment.snapshotTotal.toFixed(2)"
                  style="width:100%; background:#12151C; border:1px solid var(--border-color);
                         border-radius:8px; padding:10px 12px; color:var(--text-primary);
                         font-size:16px; font-weight:700; outline:none; font-family:monospace;
                         box-sizing:border-box;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'"
                />
                <div v-if="parseFloat(directPayment.tendered) > directPayment.snapshotTotal"
                  style="margin-top:10px; padding:10px 14px; background:rgba(16,185,129,0.08);
                         border:1px solid rgba(16,185,129,0.2); border-radius:8px;
                         display:flex; justify-content:space-between; align-items:center;">
                  <span style="font-size:13px; color:#10B981; font-weight:600;">Change Due</span>
                  <span style="font-size:18px; font-weight:700; color:#10B981; font-family:monospace;">
                    Rs.{{ (Math.round((parseFloat(directPayment.tendered) -
                           directPayment.snapshotTotal) * 100) / 100).toFixed(2) }}
                  </span>
                </div>
              </div>

              <!-- Card reference -->
              <div v-if="directPayment.selectedMethod === 'card'" style="margin-bottom:14px;">
                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase;
                            letter-spacing:0.06em; margin-bottom:6px; font-weight:600;">
                  Card Reference (optional)
                </div>
                <input
                  v-model="directPayment.cardReference"
                  placeholder="Last 4 digits or transaction ID"
                  style="width:100%; background:#12151C; border:1px solid var(--border-color);
                         border-radius:8px; padding:10px 12px; color:var(--text-primary);
                         font-size:13px; outline:none; font-family:inherit; box-sizing:border-box;"
                  @focus="e => e.target.style.borderColor='#F59E0B'"
                  @blur="e => e.target.style.borderColor='#252B38'"
                />
              </div>

              <!-- Error -->
              <div v-if="directPayment.errorMsg"
                style="padding:10px 14px; background:rgba(239,68,68,0.08);
                       border:1px solid rgba(239,68,68,0.2); border-radius:8px;
                       color:#EF4444; font-size:13px; margin-bottom:4px;">
                ⚠️ {{ directPayment.errorMsg }}
              </div>
            </div>

            <!-- Footer -->
            <div style="padding:14px 20px; border-top:1px solid #252B38;
                        display:flex; gap:8px; flex-shrink:0;">
              <button @click="directPayment.show = false"
                style="flex:1; padding:12px; border-radius:8px; font-size:13px; font-weight:600;
                       background:transparent; color:var(--text-secondary);
                       border:1px solid var(--border-color); cursor:pointer;">
                Cancel
              </button>
              <button @click="processDirectPayment"
                :disabled="directPayment.processing"
                style="flex:2; padding:12px; border-radius:8px; font-size:14px; font-weight:700;
                       background:#10B981; color:#fff; border:none; cursor:pointer; transition:opacity 0.15s;"
                :style="{ opacity: directPayment.processing ? '0.6' : '1' }">
                {{ directPayment.processing
                   ? 'Processing...'
                   : `✓ Charge Rs.${directPayment.snapshotTotal.toFixed(2)}` }}
              </button>
            </div>
          </template>

          <!-- Receipt View -->
          <template v-else>
            <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                        display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
              <div style="font-size:15px; font-weight:700; color:#10B981;">✅ Payment Complete</div>
              <div style="display:flex; gap:8px;">
                <button @click="printDirectReceipt"
                  style="padding:5px 12px; background:rgba(59,130,246,0.1); color:#3B82F6;
                         border:1px solid rgba(59,130,246,0.3); border-radius:6px;
                         font-size:11px; font-weight:600; cursor:pointer;">
                  🖨️ Print
                </button>
                <button @click="closeDirectReceipt"
                  style="padding:5px 12px; background:rgba(239,68,68,0.1); color:#EF4444;
                         border:1px solid rgba(239,68,68,0.3); border-radius:6px;
                         font-size:11px; font-weight:600; cursor:pointer;">
                  Close
                </button>
              </div>
            </div>

            <div style="flex:1; overflow-y:auto; padding:0;">
              <div style="padding:20px; font-family:monospace;">

                <div style="text-align:center; margin-bottom:16px;">
                  <div style="font-size:16px; font-weight:700; color:var(--text-primary);">
                    {{ directPayment.receiptData?.receipt?.restaurant_name ?? 'Restaurant POS' }}
                  </div>
                  <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">
                    {{ directPayment.receiptData?.receipt?.printed_at }}
                  </div>
                </div>

                <div style="border-top:1px dashed #252B38; border-bottom:1px dashed #252B38;
                            padding:10px 0; margin-bottom:12px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:11px; color:var(--text-secondary);">Order</span>
                    <span style="font-size:11px; color:var(--text-primary);">
                      {{ directPayment.receiptData?.order?.order_number }}
                    </span>
                  </div>
                  <div v-if="directPayment.receiptData?.order?.customer_name"
                    style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:11px; color:var(--text-secondary);">Customer</span>
                    <span style="font-size:11px; color:var(--text-primary);">
                      {{ directPayment.receiptData.order.customer_name }}
                    </span>
                  </div>
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:11px; color:var(--text-secondary);">Cashier</span>
                    <span style="font-size:11px; color:var(--text-primary);">
                      {{ directPayment.receiptData?.receipt?.cashier }}
                    </span>
                  </div>
                </div>

                <div style="margin-bottom:12px;">
                  <div v-for="item in directPayment.receiptData?.items" :key="item.id" style="margin-bottom:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                      <div style="flex:1;">
                        <span style="font-size:12px; color:var(--text-primary);">
                          {{ item.quantity }}x {{ item.name }}
                        </span>
                        <div v-if="item.modifiers?.length > 0"
                          style="font-size:10px; color:var(--text-secondary); margin-top:1px;">
                          {{ item.modifiers.map(m => m.name).join(', ') }}
                        </div>
                        <div v-if="item.notes"
                          style="font-size:10px; color:var(--text-secondary); margin-top:1px; font-style:italic;">
                          * {{ item.notes }}
                        </div>
                      </div>
                      <span style="font-size:12px; color:#94A3B8; margin-left:8px;">
                        Rs.{{ item.total_price }}
                      </span>
                    </div>
                  </div>
                </div>

                <div style="border-top:1px dashed #252B38; padding-top:10px; margin-bottom:12px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span style="font-size:12px; color:var(--text-secondary);">Subtotal</span>
                    <span style="font-size:12px; color:#94A3B8;">
                      Rs.{{ directPayment.receiptData?.totals?.subtotal }}
                    </span>
                  </div>
                  <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span style="font-size:12px; color:var(--text-secondary);">
                      Service ({{ directPayment.receiptData?.totals?.tax_rate }}%)
                    </span>
                    <span style="font-size:12px; color:#94A3B8;">
                      {{ parseFloat(directPayment.receiptData?.totals?.tax_rate) === 0
                         ? 'Waived'
                         : 'Rs.' + directPayment.receiptData?.totals?.tax_amount }}
                    </span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding-top:8px;
                              border-top:1px dashed #252B38; margin-top:4px;">
                    <span style="font-size:15px; font-weight:700; color:var(--text-primary);">TOTAL</span>
                    <span style="font-size:15px; font-weight:700; color:#F59E0B;">
                      Rs.{{ directPayment.receiptData?.totals?.total }}
                    </span>
                  </div>
                </div>

                <div style="background:#12151C; border:1px solid var(--border-color); border-radius:8px;
                            padding:12px; margin-bottom:12px;">
                  <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase;
                              letter-spacing:0.05em; margin-bottom:8px; font-weight:600;">
                    Payment Details
                  </div>
                  <div v-for="p in directPayment.receiptData?.payments" :key="p.id" style="margin-bottom:6px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                      <span style="font-size:12px; color:var(--text-primary); font-weight:600;">
                        {{ p.method_label }}
                      </span>
                      <span style="font-size:12px; font-weight:700; color:#10B981;">Rs.{{ p.amount }}</span>
                    </div>
                    <div v-if="p.method === 'cash' && parseFloat(p.tendered) > parseFloat(p.amount)"
                      style="display:flex; justify-content:space-between;">
                      <span style="font-size:11px; color:var(--text-secondary);">Tendered</span>
                      <span style="font-size:11px; color:var(--text-secondary);">Rs.{{ p.tendered }}</span>
                    </div>
                    <div v-if="parseFloat(p.change_amount) > 0"
                      style="display:flex; justify-content:space-between;">
                      <span style="font-size:11px; color:#10B981;">Change</span>
                      <span style="font-size:11px; font-weight:700; color:#10B981;">Rs.{{ p.change_amount }}</span>
                    </div>
                    <div v-if="p.reference" style="display:flex; justify-content:space-between;">
                      <span style="font-size:11px; color:var(--text-secondary);">Ref</span>
                      <span style="font-size:11px; color:var(--text-secondary);">{{ p.reference }}</span>
                    </div>
                    <div style="font-size:10px; color:#334155; margin-top:1px;">
                      {{ p.paid_at }} · {{ p.cashier }}
                    </div>
                  </div>
                </div>

                <div style="text-align:center; padding:10px 0;">
                  <div style="font-size:10px; color:#334155;">Receipt No.</div>
                  <div style="font-size:13px; font-weight:700; color:var(--text-secondary); letter-spacing:0.1em;">
                    {{ directPayment.receiptData?.payments?.[0]?.receipt_number }}
                  </div>
                </div>

                <div style="text-align:center; padding-top:10px; border-top:1px dashed #252B38;">
                  <div style="font-size:13px; color:var(--text-secondary);">Thank you for your visit!</div>
                </div>
              </div>
            </div>

            <div style="padding:14px 20px; border-top:1px solid #252B38; flex-shrink:0;">
              <button @click="finishDirectOrder"
                style="width:100%; padding:13px; border-radius:9px; font-size:14px; font-weight:700;
                       background:#F59E0B; color:#000; border:none; cursor:pointer;">
                Done — New Order
              </button>
            </div>
          </template>
        </div>
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
               display:flex; align-items:flex-end; justify-content:center; z-index:60;"
        @click.self="notesItem = null">
        <div style="background:var(--bg-tertiary); border:1px solid var(--border-color);
                    border-radius:20px 20px 0 0; padding:20px 16px; width:100%; max-width:480px;
                    padding-bottom:calc(20px + env(safe-area-inset-bottom, 0px));">
          <div style="width:40px; height:4px; background:var(--border-color);
                      border-radius:2px; margin:0 auto 16px;"></div>
          <div style="font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:12px;">
            📝 Note for {{ notesItem.item_name }}
          </div>
          <textarea
            v-model="notesText" rows="3"
            placeholder="e.g. No onions, well done, extra sauce..."
            style="width:100%; background:#12151C; border:1px solid var(--border-color);
                   border-radius:8px; padding:12px; color:var(--text-primary); font-size:16px;
                   resize:none; font-family:inherit; outline:none; box-sizing:border-box;"
            @focus="e => e.target.style.borderColor='#F59E0B'"
            @blur="e => e.target.style.borderColor='#252B38'"
          ></textarea>
          <div style="display:flex; gap:8px; margin-top:12px;">
            <button @click="notesItem = null"
              style="flex:1; padding:14px; background:#12151C; border:1px solid var(--border-color);
                     border-radius:10px; color:var(--text-secondary); font-size:14px; cursor:pointer;
                     min-height:50px; -webkit-tap-highlight-color:transparent;">
              Cancel
            </button>
            <button @click="saveNotes"
              style="flex:2; padding:14px; background:#F59E0B; border:none;
                     border-radius:10px; color:#000; font-size:14px; font-weight:700;
                     cursor:pointer; min-height:50px; -webkit-tap-highlight-color:transparent;">
              Save Note
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show"
          style="position:fixed; bottom:24px; left:50%; transform:translateX(-50%); z-index:100;
                 padding:12px 20px; border-radius:9px; font-weight:600; font-size:13px;
                 display:flex; align-items:center; gap:8px; white-space:nowrap;
                 box-shadow:0 4px 16px rgba(0,0,0,0.4);"
          :style="{
            background: toast.type === 'success' ? '#10B981'
                      : toast.type === 'error'   ? '#EF4444' : '#3B82F6',
            color: '#fff'
          }">
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>

    <!-- Pending Orders Modal -->
    <Teleport to="body">
      <div v-if="showPendingOrdersModal"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.65);
               display:flex; align-items:center; justify-content:center; z-index:50; padding:16px;"
        @click.self="showPendingOrdersModal = false">

        <div style="background:var(--bg-tertiary); border:1px solid var(--border-color);
                    border-radius:16px; width:500px; max-width:94vw; max-height:85vh;
                    overflow:hidden; display:flex; flex-direction:column;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">

          <div style="padding:16px 20px; border-bottom:1px solid #252B38;
                      display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="font-size:17px; font-weight:700; color:var(--text-primary);">
              Pending Orders
              <span style="font-size:12px; color:var(--text-secondary); margin-left:8px;">
                ({{ pendingKotOrders.length }} active)
              </span>
            </div>
            <button @click="showPendingOrdersModal = false"
              style="width:30px; height:30px; background:transparent; border:none;
                     color:var(--text-secondary); cursor:pointer; border-radius:6px;
                     font-size:18px; display:flex; align-items:center; justify-content:center;">
              ×
            </button>
          </div>

          <div style="flex:1; overflow-y:auto; padding:16px;">
            <div v-if="loadingPending"
              style="display:flex; align-items:center; justify-content:center;
                     height:120px; color:#475569; font-size:13px; gap:8px;">
              <span style="opacity:0.5;">Loading pending orders...</span>
            </div>

            <div v-else-if="pendingKotOrders.length === 0"
              style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                     height:120px; color:#334155; gap:12px; text-align:center;">
              <span style="font-size:40px; opacity:0.2;"></span>
              <span style="font-size:14px; color:var(--text-secondary);">No pending orders</span>
              <span style="font-size:12px; color:#475569;">All orders are completed or paid</span>
            </div>

            <div v-else style="display:flex; flex-direction:column; gap:12px;">
              <div
                v-for="order in pendingKotOrders"
                :key="order.id"
                @click="switchToOrderAndClose(order)"
                style="background:var(--bg-secondary); border:1px solid var(--border-color);
                       border-radius:12px; padding:14px; cursor:pointer; position:relative;
                       border-left:4px solid transparent; transition:all 0.15s;"
                :style="{
                  borderLeftColor: order.id === currentOrder?.id ? '#F59E0B' : '#10B981',
                  background:      order.id === currentOrder?.id ? '#1E2535' : '#12151C',
                }">
                <div v-if="order.id === currentOrder?.id"
                  style="position:absolute; top:8px; right:8px; font-size:9px; font-weight:700;
                         background:rgba(245,158,11,0.15); color:#F59E0B; padding:2px 6px; border-radius:4px;">
                  ACTIVE
                </div>

                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
                  <div>
                    <div style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
                      {{ order.order_number }}
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
                      <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;"
                        :style="getTypeBadgeStyle(order.type)">
                        {{ order.type?.replace('_', ' ') }}
                      </span>
                      <span style="font-size:11px; color:#94A3B8;">
                        {{ formatTime(order.created_at) }}
                      </span>
                    </div>
                  </div>
                  <div style="text-align:right;">
                    <div style="font-size:16px; font-weight:700; color:#F59E0B;">
                      Rs.{{ parseFloat(order.total || 0).toFixed(0) }}
                    </div>
                    <div style="font-size:11px; color:#475569;">
                      {{ order.items?.length || 0 }} items
                    </div>
                  </div>
                </div>

                <div style="font-size:12px; color:#94A3B8; margin-bottom:8px;">
                  {{ order.customer_name || 'Walk-in Customer' }}
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; padding-top:8px;
                            border-top:1px solid rgba(37,43,56,0.5);">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:10px; color:#10B981; font-weight:600;">KOT Sent</span>
                    <span style="font-size:10px; color:#475569;">
                      {{ order.items?.filter(i => i.kot_round).length || 0 }} items
                    </span>
                  </div>
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:10px; color:#F59E0B; font-weight:600;">New</span>
                    <span style="font-size:10px; color:#475569;">
                      {{ order.items?.filter(i => !i.kot_round && !i.is_void).length || 0 }} items
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div style="padding:16px; border-top:1px solid #252B38; flex-shrink:0;">
            <button @click="createNewDirectOrder"
              style="width:100%; padding:12px; background:linear-gradient(135deg,#10B981,#059669);
                     color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700;
                     cursor:pointer; min-height:48px; display:flex; align-items:center;
                     justify-content:center; gap:8px;">
              + Create New Order
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Addons Modal -->
    <Teleport to="body">
      <div v-if="addonsItem" class="modal-overlay" @click.self="addonsItem = null">
        <div class="modal-sheet">
          <div class="sheet-handle" />
          <div class="modal-scroll">
            <AddonsModal
              :show="!!addonsItem"
              :item="addonsItem"
              :orderType="'direct'"
              @close="addonsItem = null"
              @added="onAddonAdded"
              @deleted="onAddonDeleted"
            />
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter }                          from 'vue-router'
import { useOrderStore }                      from '@/stores/orders'
import { useMenuStore }                       from '@/stores/menu'
import axios                                  from 'axios'
import ModifierSelector                       from '@/components/ModifierSelector.vue'
import AddonsModal                             from '@/components/AddonsModal.vue'

const router     = useRouter()
const orderStore = useOrderStore()
const menuStore  = useMenuStore()

// ── State ──────────────────────────────────────────────────
const loading          = ref(false)
const loadingPending   = ref(false)
const modifierItem     = ref(null)
const notesItem        = ref(null)
const addonsItem       = ref(null)
const notesText        = ref('')
const toast            = ref({ show: false, message: '', type: 'success' })
const selectedType     = ref('takeaway')
const customerName     = ref('')
const pendingKotOrders = ref([])
const mobileView       = ref('menu')
const showPendingOrdersModal = ref(false)
const searchQuery = ref('')

// ── Direct Payment State ───────────────────────────────────
const directPayment = reactive({
  show:             false,
  paid:             false,
  processing:       false,
  showItems:        false,
  selectedMethod:   'cash',
  tendered:         '',
  cardReference:    '',
  errorMsg:         '',
  receiptData:      null,
  orderId:          null,
  orderNumber:      '',
  customerName:     '',
  snapshotItems:    [],
  snapshotTotal:    0,
  snapshotSubtotal: 0,
  quickAmounts:     [],
})

const directPaymentMethods = [
  { value: 'cash',   label: 'Cash',   icon: '💵' },
  { value: 'card',   label: 'Card',   icon: '💳' },
  { value: 'mobile', label: 'Mobile', icon: '📱' },
]

const mobileTabs = [
  { value: 'menu', icon: '🍽️' },
  { value: 'kot',  icon: '🍳' },
  { value: 'cart', icon: '🧾' },
]

const orderTypes = [
  { value: 'takeaway', label: 'Takeaway', color: '#10B981' },
  { value: 'dine_in',  label: 'Dine In',  color: '#F59E0B' },
  { value: 'bar',      label: 'Bar',      color: '#8B5CF6' },
  { value: 'counter',  label: 'Counter',  color: '#3B82F6' },
  { value: 'delivery', label: 'Delivery', color: '#EF4444' },
]

const typeBadgeColors = {
  takeaway: { background: 'rgba(16,185,129,0.15)',  color: '#10B981' },
  dine_in:  { background: 'rgba(245,158,11,0.15)',  color: '#F59E0B' },
  bar:      { background: 'rgba(139,92,246,0.15)',  color: '#8B5CF6' },
  counter:  { background: 'rgba(59,130,246,0.15)',  color: '#3B82F6' },
  delivery: { background: 'rgba(239,68,68,0.15)',   color: '#EF4444' },
}

function getTypeBadgeStyle(type) {
  return typeBadgeColors[type] ?? typeBadgeColors.takeaway
}

// ── Image URL helper ───────────────────────────────────────
// Handles both full URLs and bare filenames
function getImageUrl(image) {
  if (!image) return ''
  if (image.startsWith('http://') || image.startsWith('https://') || image.startsWith('/')) {
    return image
  }
  return `/storage/menu_items/${image}`
}

function formatTime(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// ── Computed ───────────────────────────────────────────────
const currentOrder = computed(() => orderStore.currentOrder)

const currentItems = computed(() => {
  // When not searching, show items from active category only
  if (!searchQuery.value || !searchQuery.value.trim()) {
    return menuStore.getItemsByCategory(menuStore.activeCategory)
  }
  
  // During search, show items from all categories with relevance sorting
  const query = searchQuery.value.toLowerCase().trim()
  console.log('🔍 Searching for:', query)
  
  const allItems = []
  menuStore.categories.forEach(cat => {
    const catItems = menuStore.getItemsByCategory(cat.id)
    allItems.push(...catItems)
  })
  
  const filteredItems = allItems.filter(item => 
    item.name.toLowerCase().includes(query) ||
    (item.description && item.description.toLowerCase().includes(query))
  )
  
  console.log('📊 Found items:', filteredItems.length, 'from', allItems.length, 'categories')
  
  return filteredItems.sort((a, b) => {
    // Prioritize exact name matches
    const aNameMatch = a.name.toLowerCase().indexOf(query) === 0
    const bNameMatch = b.name.toLowerCase().indexOf(query) === 0
    
    if (aNameMatch && !bNameMatch) return -1
    if (bNameMatch && !aNameMatch) return 1
    
    // Both match equally
    return 0
  })
})

const orderItems = computed(() => {
  const items = (currentOrder.value?.items ?? []).filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })
  
  // Sort: unsent items first (newest first), then sent items by KOT round
  return items.sort((a, b) => {
    // Unsent items come first
    const aUnsent = !a.kot_round
    const bUnsent = !b.kot_round
    
    if (aUnsent && !bUnsent) return -1  // a comes first
    if (!aUnsent && bUnsent) return 1   // b comes first
    
    // Both sent or both unsent - sort by created_at (newest first)
    const aTime = new Date(a.created_at || 0).getTime()
    const bTime = new Date(b.created_at || 0).getTime()
    return bTime - aTime
  })
})

const unsentItems = computed(() => orderItems.value.filter(i => !i.kot_round))
const sentItems   = computed(() => orderItems.value.filter(i =>  i.kot_round))

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

// ── Status helpers ─────────────────────────────────────────
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

// ── Open direct payment — snapshot ALL order data NOW ─────
function openDirectPayment() {
  const order = currentOrder.value
  if (!order) return

  // Calculate subtotal including add-ons
  const subtotal = (order.items ?? []).reduce((total, item) => {
    if (item.is_void || item.is_void === 1 || item.is_void === '1') {
      return total
    }
    
    // Calculate item total including add-ons
    const itemTotal = parseFloat(item.total_price || 0)
    const addonsTotal = item.addons ? item.addons.reduce((addonSum, addon) => {
      return addonSum + parseFloat(addon.total_price || 0)
    }, 0) : 0
    
    return total + itemTotal + addonsTotal
  }, 0)
  
  const total = Math.max(0, Math.round(subtotal * 100) / 100)

  const activeItems = (order.items ?? []).filter(i => {
    const v = i.is_void
    return v !== true && v !== 1 && v !== '1'
  })

  // Quick cash amounts
  const amounts = []
  for (const r of [1, 5, 10, 20, 50, 100]) {
    const rounded = Math.ceil(total / r) * r
    if (rounded >= total && !amounts.includes(rounded) && amounts.length < 5) amounts.push(rounded)
  }

  Object.assign(directPayment, {
    show:             true,
    paid:             false,
    processing:       false,
    showItems:        false,
    selectedMethod:   'cash',
    tendered:         '',
    cardReference:    '',
    errorMsg:         '',
    receiptData:      null,
    orderId:          order.id,
    orderNumber:      order.order_number,
    customerName:     order.customer_name,
    snapshotItems:    activeItems,
    snapshotTotal:    total,
    snapshotSubtotal: subtotal,
    quickAmounts:     amounts,
  })
}

// ── Process payment ────────────────────────────────────────
async function processDirectPayment() {
  directPayment.errorMsg = ''

  const total   = directPayment.snapshotTotal
  const orderId = directPayment.orderId

  if (directPayment.selectedMethod === 'cash') {
    const t = parseFloat(directPayment.tendered || total)
    if (isNaN(t) || t < total) {
      directPayment.errorMsg = `Cash tendered must be at least Rs.${total.toFixed(2)}`
      return
    }
  }

  directPayment.processing = true

  try {
    await orderStore.processPayment(orderId, [{
      method:    directPayment.selectedMethod,
      amount:    total,
      tendered:  directPayment.selectedMethod === 'cash'
        ? parseFloat(directPayment.tendered || total)
        : total,
      reference: directPayment.cardReference || null,
    }])

    const { data } = await axios.get(`/orders/${orderId}/receipt`)
    directPayment.receiptData = data
    directPayment.paid        = true

    showToast('Payment successful!', 'success')
  } catch (e) {
    console.error('Direct payment error:', e)
    directPayment.errorMsg = e.response?.data?.message ?? e.message ?? 'Payment failed. Please try again.'
  } finally {
    directPayment.processing = false
  }
}

// ── Print receipt ──────────────────────────────────────────
function printDirectReceipt() {
  const r = directPayment.receiptData
  const w = window.open('', '_blank', 'width=400,height=700')
  w.document.write(`<html><head>
    <title>Receipt ${r?.order?.order_number ?? ''}</title>
    <style>
      *{box-sizing:border-box;margin:0;padding:0}
      body{font-family:monospace;font-size:12px;color:#000;background:#fff;padding:10px;width:80mm}
      .c{text-align:center}.b{font-weight:bold}.m{color:#666}.l{font-size:16px}
      .row{display:flex;justify-content:space-between;margin-bottom:3px}
      .div{border-top:1px dashed #999;margin:8px 0}
      .tot{font-size:15px;font-weight:bold}
    </style>
  </head><body>
    <div class="c b l">${r?.receipt?.restaurant_name ?? 'Restaurant POS'}</div>
    <div class="c m" style="font-size:10px;margin-bottom:8px">${r?.receipt?.printed_at}</div>
    <div class="div"></div>
    <div class="row"><span class="m">Order</span><span class="b">${r?.order?.order_number}</span></div>
    ${r?.order?.customer_name ? `<div class="row"><span class="m">Customer</span><span>${r.order.customer_name}</span></div>` : ''}
    <div class="row"><span class="m">Cashier</span><span>${r?.receipt?.cashier}</span></div>
    <div class="div"></div>
    ${(r?.items ?? []).map(i => `
      <div class="row">
        <span>${i.quantity}x ${i.name}${i.modifiers?.length ? ' (' + i.modifiers.map(m => m.name).join(', ') + ')' : ''}</span>
        <span>Rs.${i.total_price}</span>
      </div>
      ${i.notes ? `<div class="m" style="font-size:10px;padding-left:8px">* ${i.notes}</div>` : ''}
    `).join('')}
    <div class="div"></div>
    <div class="row"><span class="m">Subtotal</span><span>Rs.${r?.totals?.subtotal}</span></div>
    <div class="row">
      <span class="m">Service (${r?.totals?.tax_rate}%)</span>
      <span>${parseFloat(r?.totals?.tax_rate) === 0 ? 'Waived' : 'Rs.' + r?.totals?.tax_amount}</span>
    </div>
    ${parseFloat(r?.totals?.discount_amount) > 0 ? `<div class="row"><span>Discount</span><span>-Rs.${r.totals.discount_amount}</span></div>` : ''}
    <div class="div"></div>
    <div class="row tot"><span>TOTAL</span><span>Rs.${r?.totals?.total}</span></div>
    <div class="div"></div>
    ${(r?.payments ?? []).map(p => `
      <div class="row"><span class="b">${p.method_label}</span><span class="b">Rs.${p.amount}</span></div>
      ${p.method === 'cash' && parseFloat(p.tendered) > parseFloat(p.amount) ? `
        <div class="row"><span class="m">Tendered</span><span>Rs.${p.tendered}</span></div>
        <div class="row"><span class="m">Change</span><span class="b">Rs.${p.change_amount}</span></div>
      ` : ''}
      ${p.reference ? `<div class="row"><span class="m">Ref</span><span>${p.reference}</span></div>` : ''}
    `).join('')}
    <div class="div"></div>
    <div class="c m" style="font-size:10px">${r?.payments?.[0]?.receipt_number}</div>
    <div class="c" style="margin-top:12px">Thank you for your visit!</div>
  </body></html>`)
  w.document.close()
  w.focus()
  setTimeout(() => { w.print(); w.close() }, 250)
}

function closeDirectReceipt() {
  directPayment.show = false
  directFinishCleanup()
}

function finishDirectOrder() {
  directPayment.show = false
  directFinishCleanup()
}

// ── Search Functions ───────────────────────────────────
function onSearchInput(event) {
  const query = event.target.value
  if (query && query.trim()) {
    searchQuery.value = query
  } else {
    clearSearch()
  }
}

function onSearchEnter() {
  // Focus on first search result when Enter is pressed
  if (currentItems.value.length > 0) {
    const firstItem = currentItems.value[0]
    if (firstItem && firstItem.is_available) {
      addItem(firstItem)
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

async function directFinishCleanup() {
  orderStore.clearOrder()
  customerName.value = ''
  selectedType.value = 'takeaway'
  await loadPendingKotOrders()
  showToast('Order completed ✓ Ready for new order', 'success')
}

// ── Load pending KOT orders ────────────────────────────────
async function loadPendingKotOrders() {
  loadingPending.value = true
  try {
    const { data } = await axios.get('/direct-orders/pending')
    const all = Array.isArray(data) ? data : (data.pending_orders ?? [])
    pendingKotOrders.value = all.filter(o =>
      o.items && o.items.some(i => i.kot_round && i.kot_round > 0)
    )
  } catch (e) {
    console.error('Failed to load pending KOT orders:', e)
  } finally {
    loadingPending.value = false
  }
}

// ── API actions ────────────────────────────────────────────
async function createNewDirectOrder() {
  try {
    const { data } = await axios.post('/direct-orders', {
      type:          selectedType.value,
      customer_name: 'Walk-in',
    })
    orderStore.setOrder(data)
    selectedType.value = data.type || 'takeaway'
    customerName.value = ''
    await loadPendingKotOrders()
    showToast(`Created ${data.order_number} ✓`, 'success')
    mobileView.value = 'menu'
  } catch (e) {
    console.error('Create order failed:', e)
    showToast(e.response?.data?.message || 'Failed to create order', 'error')
  }
}

async function switchToOrder(order) {
  if (!order || order.id === currentOrder.value?.id) return
  try {
    const { data } = await axios.post(`/direct-orders/${order.id}/switch`)
    orderStore.setOrder(data)
    selectedType.value = data.type || 'takeaway'
    customerName.value = data.customer_name || ''
    await loadPendingKotOrders()
    showToast(`Switched to ${data.order_number} ¥`, 'success')
    mobileView.value = 'menu'
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to switch order', 'error')
  }
}

async function switchToOrderAndClose(order) {
  await switchToOrder(order)
  showPendingOrdersModal.value = false
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

// ── FIX: addItem — extract numeric ID from order object ───
async function addItem(menuItem) {
  if (!currentOrder.value || !menuItem.is_available) return

  if (menuItem.modifier_groups?.length > 0) {
    modifierItem.value = menuItem
    return
  }

  // ✅ Always extract the numeric ID — never pass the whole object
  const orderId = currentOrder.value.id

  try {
    await orderStore.addItem(orderId, {
      menu_item_id: menuItem.id,       // ✅ correct field name the API expects
      quantity:     1,
      is_instant:   menuItem.is_instant ?? false,
    })
    if (menuItem.is_instant) showToast(`${menuItem.name} added ⚡`, 'success')
    mobileView.value = 'cart'
  } catch (e) {
    console.error('addItem failed:', e.response?.data ?? e.message)
    showToast(e.response?.data?.message || 'Failed to add item', 'error')
  }
}

async function onModifierConfirm(payload) {
  modifierItem.value = null
  if (!currentOrder.value) return
  const orderId = currentOrder.value.id
  try {
    await orderStore.addItem(orderId, {
      menu_item_id:       payload.menu_item_id,
      quantity:           payload.quantity,
      selected_modifiers: payload.selected_modifiers,
      notes:              payload.notes,
      is_instant:         payload.is_instant ?? false,
    })
    showToast('Added to cart ✓', 'success')
    mobileView.value = 'cart'
  } catch (e) {
    console.error('onModifierConfirm failed:', e.response?.data ?? e.message)
    showToast(e.response?.data?.message || 'Failed to add item', 'error')
  }
}

async function increaseQty(item) {
  if (!currentOrder.value) return
  try {
    await orderStore.updateItemQty(currentOrder.value.id, item.id, item.quantity + 1)
  } catch (e) {
    showToast('Failed to update quantity', 'error')
  }
}

async function decreaseQty(item) {
  if (!currentOrder.value) return
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
  if (!currentOrder.value) return
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
  if (!currentOrder.value) return
  try {
    const res = await orderStore.sendKOT(currentOrder.value.id)
    showToast(`Round ${res.round}: ${res.items_sent} item(s) sent! 🍳`, 'success')
    await loadPendingKotOrders()
  } catch (e) {
    showToast(e.response?.data?.message ?? 'Failed to send KOT', 'error')
  }
}

async function advanceStatus(item) {
  if (!currentOrder.value) return
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

function openNotes(item) {
  notesItem.value = item
  notesText.value = item.notes ?? ''
}

async function saveNotes() {
  if (!notesItem.value || !currentOrder.value) return
  try {
    await axios.patch(`/orders/${currentOrder.value.id}/items/${notesItem.value.id}`, {
      notes: notesText.value,
    })
    await orderStore.fetchOrder(currentOrder.value.id)
    notesItem.value = null
    showToast('Note saved', 'success')
  } catch (e) {
    showToast('Failed to save note', 'error')
  }
}

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

function openAddons(item) {
  addonsItem.value = item
}

async function onAddonAdded(addon) {
  // Refresh the order to show updated pricing
  await orderStore.fetchOrder(currentOrder.value.id)
  showToast('Extra added successfully!', 'success')
}

async function onAddonDeleted(addonId) {
  // Refresh the order to show updated pricing
  await orderStore.fetchOrder(currentOrder.value.id)
  showToast('Extra removed', 'success')
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ── Lifecycle ──────────────────────────────────────────────
onMounted(async () => {
  loading.value = true
  await menuStore.fetchMenu()

  const saved = localStorage.getItem('pos_current_order')
  if (saved) {
    try {
      const orderData = JSON.parse(saved)
      // Only restore direct orders (no table_id)
      if (!orderData.table_id && orderData.id) {
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
button {
  touch-action: manipulation;
}

input, textarea {
  /* Force 16px on mobile to prevent iOS zoom */
  font-size: 16px !important;
}

/* Hide scrollbars globally */
div::-webkit-scrollbar {
  display: none;
}

/* ── Menu card grid ── */
.menu-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 7px;
}

/* ── Individual menu card ── */
.menu-item-card {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 7px 6px 7px;
  text-align: left;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  position: relative;
  width: 100%;
  transition: transform 0.12s, box-shadow 0.12s;
  -webkit-tap-highlight-color: transparent;
}

.menu-item-card:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  border-color: rgba(245,158,11,0.35);
}

.menu-item-card:active:not(:disabled) {
  transform: scale(0.97);
}

.menu-item-card:disabled {
  cursor: not-allowed;
}

/* ── Image wrapper ── */
.item-img-wrap {
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 6px;
  overflow: hidden;
  background: #1A1E28;
  margin-bottom: 0;
}

/* ── Mobile breakpoints ── */
@media (max-width: 768px) {
  .mobile-tabs         { display: flex !important; }
  .desktop-order-types { display: none !important; }
  .mobile-order-types  { display: flex !important; }

  .kot-panel {
    width: 100% !important;
    border-right: none !important;
  }

  .cart-panel {
    width: 100% !important;
  }

  .panel {
    flex: none !important;
    overflow: hidden !important;
  }

  .panel.panel-active {
    flex: 1 !important;
    overflow: hidden !important;
  }

  .panel:not(.panel-active) {
    width: 0 !important;
    min-width: 0 !important;
    padding: 0 !important;
    border: none !important;
    visibility: hidden !important;
  }

  .menu-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
  }
}

@media (max-width: 480px) {
  .menu-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }
}

/* ── Toast transition ── */
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.25s, transform 0.25s;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}
</style>